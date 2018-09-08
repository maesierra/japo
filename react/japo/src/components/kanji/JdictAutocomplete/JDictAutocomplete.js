import React from 'react';
import './JDictAutocomplete.css'
require('es6-promise').polyfill();
require('isomorphic-fetch');
const wanakana = require('wanakana');
const Japo = require('../../../lib/japo/japo');


class JDictAutocomplete extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            options: [],
            state: 'closed',
            selected: -1,
            value: props.value ? props.value : ''
        };
        this.onValueSelected = this.onValueSelected.bind(this);
    }

    componentDidMount() {
        this.mainInput.focus();
        let len = this.mainInput.value.length * 2;
        this.mainInput.setSelectionRange(len, len);
    };

    valueChanged(event) {
        this.setState(Object.assign({}, this.state, {
            value: wanakana.toKana(event.target.value, { IMEMode: true })
        }));
        if (this.props.onChange) {
            this.props.onChange(event);
        }
    }
    close(state, value) {
        this.setState(Object.assign({}, this.state, {
            state: state ? state : 'closed',
            options: [],
            selected: -1,
            value: value ? value: this.state.value
        }));
    }

    keyPressed(event) {
        //Check for control + space
        if (event.ctrlKey && event.keyCode === 32) {
            this.close('loading'); //Close and set status to loading
            //jdict query
            Japo.jDict({
                reading: this.state.value,
                page: 1,
                pageSize: 25,
                exact: true
            }).then((results) => {
                this.setState(Object.assign({}, this.state, {
                    state: results.entries.length > 0  ? 'open' : 'closed',
                    options: results.entries
                }));
            });
            return;
        }
        if (this.state.state === 'open') {
            switch (event.keyCode) {
                case 38: //up arrow
                    this.moveSelected(-1);
                    break;
                case 40: //down arrow
                    this.moveSelected(1);
                    break;
                case 27: //escape
                    this.close();
                    break;
                case 13: //enter
                    if (this.state.selected >= 0) {
                        this.onValueSelected(this.state.options[this.state.selected].kanji, event);
                    }
                    break;
                default:
                    break;
            }
        }
    }

    moveSelected(delta) {
        let selected = this.state.selected;
        if (selected < 0 && delta < 0) {
            selected = 0;
        }
        let nOptions = this.state.options.length;
        selected += delta;
        if (selected < 0) {
            selected = nOptions + selected;
        } else if (selected >= nOptions) {
            selected = selected - nOptions;
        }
        this.setState(Object.assign({}, this.state, {
            selected: selected
        }));
    }

    spinner() {
        if (this.state.state === 'loading') {
            return <i className="fa fa-spinner fa-spin form-control-icon" />;
        } else {
            return '';
        }
    }

    onValueSelected(value, event) {
        this.close('closed', value);
        this.mainInput.focus();
        if (this.props.onChange) {
            //Propagate the event with a new value
            this.props.onChange(Object.assign({}, event, {target: Object.assign({}, event.target, {value: value})}));
        }
    }

    render() {
        const classNames = ['jdict-autocomplete', 'has-icon'];
        const {className, ...rest } = this.props;
        if (className) {
            classNames.push(className);
        }
        return <div className={classNames.join(' ')}>
            <input className="form-control" {...rest}  value={this.state.value}
                   lang="ja"
                   onChange={this.valueChanged.bind(this)}
                   onKeyUp={this.keyPressed.bind(this)}
                   ref={(input) => { this.mainInput = input; }}
            />
            <div className="list-group autocomplete">
                {this.state.options.map((option, i) => {
                    const itemClass = ['list-group-item'];
                    if (i === this.state.selected) {
                        itemClass.push('active');
                    }
                    return <a key={i} className={itemClass.join(' ')} onClick={(e) => this.onValueSelected(option.kanji, e)}>
                        <span lang="ja">{option.kanji}</span> <span className="small">({option.gloss.join(',')})</span>
                    </a>;
                })}
            </div>
            {this.spinner()}
        </div>

    }
}


export default JDictAutocomplete;

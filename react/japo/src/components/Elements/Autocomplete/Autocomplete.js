import React from 'react';
import './Autocomplete.css'
const _ = require('lodash');

class Autocomplete extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            options: [],
            state: 'closed',
            selected: -1,
            value: this.transformValue(props.value ? props.value : '')
        };
        this.onValueSelected = this.onValueSelected.bind(this);
        this.close = this.close.bind(this);
    }

    transformValue(option) {
        return this.props.transformValue ? this.props.transformValue(option) : option;
    }

    valueChanged(event) {
        let value = event.target.value;
        this.setState(Object.assign({}, this.state, {
            value: value,
            options: this.searchOptions(value)
        }));
    }

    close(value) {
        this.setState(Object.assign({}, this.state, {
            state: 'closed',
            options: [],
            selected: -1,
            value: value ? value: this.state.value
        }));
    }

    searchOptions(value) {
        let regExp = new RegExp(".*" + _.escapeRegExp(value) + ".*", "i");
        let filter = this.props.search ? this.props.search : (o, val) => {
            return regExp.test(this.transformValue(o));
        };
        return this.props.options.filter((o) => filter(o, value));

    }

    keyPressed(event) {
        if (this.state.state === 'closed') {
            this.setState(Object.assign({}, this.state, {
                state: this.props.options.length > 0  ? 'open' : 'closed',
                options: this.props.options,
                selected: -1
            }));
        } else {
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
                        this.onValueSelected(this.state.options[this.state.selected], event);
                    }
                    break;
                case 8: //backspace
                case 46: //delete
                    if(this.state.value.length === 0) {
                        this.close();
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


    onValueSelected(option, event) {
        let value = this.transformValue(option);
        this.close(value);
        this.mainInput.focus();
        this.onChange(event, value, option);
    }

    onChange(event, value, option) {
        if (this.props.onChange) {
            //Propagate the event with a new value
            this.props.onChange(Object.assign({}, event, {
                target: Object.assign({}, event.target, {value: value}),
                option: option
            }));
        }
    }


    render() {
        const classNames = ['autocomplete'];
        const inputClassNames = ['form-control'];
        const {className, options, transformValue, search, inputClassName, ...rest } = this.props;
        if (className) {
            classNames.push(className);
        }
        if (inputClassName) {
            inputClassNames.push(inputClassName);
        }
        return <div className={classNames.join(' ')}>
            <input className={inputClassNames.join(' ')} {...rest}  value={this.state.value}
                   onBlur={(e) => {
                       this.close('');
                       //If there is no value selected we may need to propagate the change
                       if (this.state.value === '') {
                           this.onChange(e, '', undefined);
                       }
                   }}
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
                    return <a key={i} className={itemClass.join(' ')} onClick={(e) => this.onValueSelected(option, e)} >
                        {this.transformValue(option)}
                    </a>;
                })}
            </div>
        </div>

    }
}


export default Autocomplete;

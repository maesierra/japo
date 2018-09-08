import React from 'react';
import ReactTooltip from 'react-tooltip'
import './LeftTabs.css'
const _ = require('lodash');


class LeftTabs extends React.Component {
    constructor(props) {
        super(props);
        let items =  [];
        _.forIn(props.items, (i, key) => { items.push({
                element: i.element,
                label: i.label,
                value: key,
                selected: props.selected !== undefined && key === props.selected
            });
        });
        this.state = {
            items: items,
            selected: props.selected !== undefined ? props.selected : ''
        };
        this.onClick = this.onClick.bind(this);

    }
    tabItem(item) {
        return [
            <button key="button" data-tip data-for={item.value} type="button" onClick={(e) => this.onClick(e, item.value)} className={'btn btn-' + (item.selected ? 'primary' : 'default')}>{item.element}</button>,
            <ReactTooltip  key="tooltip" id={item.value}>{item.label}</ReactTooltip>
        ]
    }
    onClick(event, value)  {
        let items = this.state.items.slice().map((item, pos) => {
            return  Object.assign({}, item, {selected: item.value === value})
        });
        this.setState(Object.assign({}, this.state, {items: items, selected: value}));
        if (this.props.onChange) {
            this.props.onChange(Object.assign({}, event, {
                target: Object.assign({}, event.target, {value: value})
            }));
        }
    }
    render() {
        const classNames = ['btn-group', 'btn-group-sm'];
        if (this.props.className) {
            classNames.push(this.props.className);
        }
        let tabs = this.props.children.filter((child, i) => child.props.isTab);
        let currentTab = tabs.filter((tab, i) => tab.props.name === this.state.selected).pop();
        return  <div className="left-tabs">
                    <div className={classNames.join(' ')}>
                        {this.state.items.map((item, i) => this.tabItem(item))}
                    </div>
                    {currentTab}
                </div>


    }
}

export default LeftTabs;
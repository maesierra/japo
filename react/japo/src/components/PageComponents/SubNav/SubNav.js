import React from 'react';
import './SubNav.css'
import {withRouter} from "react-router-dom";
const _ = require('lodash');

const NavItem = (props) => {
    let itemClass = 'menu-item';
    if (props.active) {
        itemClass += ' active';
    }
    return <li className={itemClass}><a href={props.link} onClick={() => props.onClick()}>{props.label}</a></li>
};

class SubNav extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            items: {},
            defaultItems: {
                kanji:      {link: "/",    selected:false, label: "Kanji"}
            }
        };
        this.onClick = this.onClick.bind(this);

    }

    getCurrentItems(props) {
        let items = props.empty === undefined ? this.state.defaultItems : {};
        if (props.selected !== undefined) {
            items[props.selected].selected = true;
        }
        return items;
    }
    componentWillReceiveProps(nextProps) {
        this.setState(Object.assign({}, this.state, {items: this.getCurrentItems(nextProps)}));
    }
    onClick(link)  {
        this.props.history.push(link);
    }
    render() {
        return <div className="row sub-nav">
                <ul className="nav nav-tabs">
                    {_.values(this.state.items).map((item, i) => <NavItem key={i} href={item.link} onClick={() => this.onClick(item.link)} label={item.label} active={item.selected}/>)}
                </ul>
            </div>

    }
}


export default withRouter(SubNav);

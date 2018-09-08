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
        let items = props.empty === undefined ? {
            dictionary: {link: "/japo/index.php", selected:false, label: "Diccionario"},
            kanji:      {link: "/",    selected:false, label: "Kanji"},
            tests:      {link: "/japo/test/list/",       selected:false, label: "Tests"}
        } : {};
        if (props.selected !== undefined) {
            items[props.selected].selected = true;
        }
        this.state = {
            items: items
        };
        this.onClick = this.onClick.bind(this);

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

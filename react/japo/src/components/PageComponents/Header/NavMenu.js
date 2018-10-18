import React from 'react';
import './Header.css'
import { withNamespaces} from 'react-i18next';

const _ = require('lodash');
const Japo = require('../../../lib/japo/japo');

const MenuItem = (props) => {
    let hasHref = props.href !== undefined;
    let href = hasHref ? process.env.PUBLIC_URL + props.href : '#' + props.label;
    let onClick = function (e) {
        if (!hasHref && props.onClick !== undefined) {
            e.preventDefault();
            props.onClick(e);
        }
    };
    return <li className="menu-item"><a href={href} onClick={(e) => onClick(e)}>{props.label}</a></li>
};

class NavMenu extends React.Component {
    constructor(props) {
        super(props);
        let items = this.items(props);
        this.state = {
            open: false,
            items: items
        };
    }

    items(props) {
        return props.authorized ? {
            start: {link: "/", label: "NavMenu.labels.start"},
            /*profile: {link: "/profile", label: "Profile"},*/
            logout: {onClick: (e) => Japo.logout(), label: "NavMenu.labels.logout"}
        } : {
            login: {onClick: (e) => Japo.login(), label: "NavMenu.labels.login"}
        };
    }

    componentDidUpdate(prevProps) {
        if (prevProps.authorized !== this.props.authorized) {
            this.setState(Object.assign({}, this.state, {items: this.items(this.props)}));
        }
    }

    open() {
        this.setState(Object.assign({}, this.state, {open: true}));
    }

    close() {
        this.setState(Object.assign({}, this.state, {open: false}));
    }

    render() {
        const { t } = this.props;
        if (this.state.open) {
            return <div className="menu open">
                <button onClick={() => this.close()}><span className="fa fa-angle-down fa-1"/></button>
                <ul>
                    {_.values(this.state.items).map((item, i) => <MenuItem key={i} href={item.link} label={t(item.label)} onClick={item.onClick}  />)}
                </ul>
            </div>
        } else {
            return <div className="menu">
                <button onClick={() => this.open()}><span className="fa fa-bars fa-1"/></button>
            </div>
        }

    }
}

export default withNamespaces('japo')(NavMenu);
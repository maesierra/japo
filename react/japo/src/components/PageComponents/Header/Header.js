import React from 'react';
import './Header.css'
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
            start: {link: "/", label: "Inicio"},
            /*profile: {link: "/profile", label: "Profile"},*/
            logout: {onClick: (e) => Japo.logout(), label: "Salir"}
        } : {
            login: {onClick: (e) => Japo.login(), label: "Acceder"}
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
        if (this.state.open) {
            return <div className="menu open">
                <button onClick={() => this.close()}><span className="fa fa-angle-down fa-1"/></button>
                <ul>
                    {_.values(this.state.items).map((item, i) => <MenuItem key={i} href={item.link} label={item.label} onClick={item.onClick}  />)}
                </ul>
            </div>
        } else {
            return <div className="menu">
                <button onClick={() => this.open()}><span className="fa fa-bars fa-1"/></button>
            </div>
        }

    }
}

class Header extends React.Component {

    componentWillMount() {
        document.getElementsByTagName('html')[0].className = this.props.pageClass;
        document.title = 'japo' + (this.props.pageTitle ? ' - ' + this.props.pageTitle : '' );
    }

    render() {
        return (
            <nav className="mainmenu row">
                <div className="col-sm-1">
                    <NavMenu authorized={this.props.authorized}/>
                </div>
                <div className="col-sm-4 col-sm-offset-7 text-right">
                    <a className="logo" href="">japo</a>
                    <a className="logo" href=""><span lang="jp">ハポ</span></a>
                </div>
            </nav>
        );
    }
}



export default Header;

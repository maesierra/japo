import React from 'react';
import './Header.css'
import { withNamespaces} from 'react-i18next';
import NavMenu from './NavMenu'
import LanguageSelector from './../../Elements/LanguageSelector/LanguageSelector'


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
                <div className="col-sm-4 col-sm-offset-6 text-right">
                    <a className="logo" href="">japo</a>
                    <a className="logo" href=""><span lang="jp">ハポ</span></a>
                    <LanguageSelector/>
                </div>
            </nav>
        );
    }
}



export default withNamespaces('japo')(Header);

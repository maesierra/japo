import React from 'react';
import './LanguageSelector.css'
import {withRouter} from "react-router-dom";
import { withNamespaces} from 'react-i18next';
import enImage from './images/flags/en.png';
import esImage from './images/flags/es.png';
const _ = require('lodash');

class LanguageSelector extends React.Component {
    constructor(props) {
        super(props);
        this.onChangeLanguage = this.onChangeLanguage.bind(this);
        let languages = [
            {code: 'es', image: esImage},
            {code: 'en', image: enImage}
        ];
        this.state = {
            lang: LanguageSelector.findLanguage(languages, process.env.REACT_APP_LANGUAGE),
            languages: languages
        }

    }

    static findLanguage(languages, lang) {
        if (lang === undefined) {
            return languages[0];
        }
        let langObj = _.find(languages, (el) => el.code === lang);
        return langObj !== undefined ? langObj : languages[0]
    }

    onChangeLanguage(e, lang)  {
        if (e) {
            e.preventDefault();
        }
        this.setState(Object.assign({}, this.state, {lang: LanguageSelector.findLanguage(this.state.languages, lang)}));
        this.props.i18n.changeLanguage(lang);
    }

    render() {
        return <nav className="language-selector text-right">
                {_.concat(this.state.lang, this.state.languages).map((lang, i) =>
                    <a key={i} href={'#' + lang.code} onClick={(e) => this.onChangeLanguage(e, lang.code)} className="img-thumbnail">
                        <img className="img-responsive" src={lang.image} alt={lang.code}/>
                    </a>
                )}
            </nav>

    }
}


export default withRouter(withNamespaces('japo')(LanguageSelector));

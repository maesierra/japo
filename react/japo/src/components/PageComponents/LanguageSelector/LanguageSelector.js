import React from 'react';
import './LanguageSelector.css'
import {withRouter} from "react-router-dom";
import enImage from './images/flags/en.png';
import esImage from './images/flags/es.png';

class LanguageSelector extends React.Component {
    constructor(props) {
        super(props);
        this.onChangeLanguage = this.onChangeLanguage.bind(this);

    }

    onChangeLanguage(e, lang)  {
        if (e) {
            if (e.button === 1) {
                return; //Middle clicks are allowed to open in a new tab
            }
            e.preventDefault();
        }
        if (this.props.onChangeLanguage) {
            this.props.onChangeLanguage(e, lang);
        }
    }

    render() {
        //Take out any current lang from path
        let location = this.props.location.pathname.replace(/^\/[a-z]{2}\//, '/');
        let languages = [
            {code: 'en', image: enImage},
            {code: 'es', image: esImage}
        ];
        return <div className="row">
            <div className="col-sm-4 col-sm-offset-7">
            <nav className="language-selector text-right">
                {languages.map((lang, i) =>
                    <a key={i} href={'/' + lang.code + location} onClick={(e) => this.onChangeLanguage(e, lang.code)} className="img-thumbnail">
                        <img className="img-responsive" src={lang.image} alt={lang.code}/>
                    </a>
                )}
            </nav>
            </div>
        </div>;

    }
}


export default withRouter(LanguageSelector);

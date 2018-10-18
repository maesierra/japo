import React, { Component } from 'react';
import { withRouter } from 'react-router-dom'

import LeftNav from './components/PageComponents/LeftNav'
import MainArea from './components/PageComponents/MainArea'
import {Header} from './components/PageComponents/Header'
import PageTitle from './components/PageComponents/PageTitle'
import SubNav from './components/PageComponents/SubNav'


import LoadingSpinner from './components/LoadingSpinner'
import JDictAutocomplete from './components/kanji/JdictAutocomplete'
import LeftTabs from './components/Elements/LeftTabs'
import LeftTab from './components/Elements/LeftTabs/LeftTab'
import CatalogGrid from './components/kanji/CatalogGrid'

import KanjiList from './KanjiList'
import KanjiDetails from "./KanjiDetails";

import './KanjiApp.css';
import { withNamespaces} from 'react-i18next';

const Japo = require('./lib/japo/japo');
const _ = require('lodash');
const wanakana = require('wanakana');





class KanjiApp extends Component {

    constructor(props) {
        super();
        //Levels will appear as params[0]
        let levels = props.match.params[0] !== undefined ? props.match.params[0].split('/') : [];
        let kanji = '';
        let catalog = '';
        let reading = '';
        let meaning = '';
        let mode = 'catalog';
        let loadList = false;
        if (props.match.params.catalog) {
            catalog = props.match.params.catalog;
            loadList = true;
        } else if (props.match.params.reading) {
            mode = 'reading';
            reading = props.match.params.reading;
            loadList = true;
        } else if (props.match.params.meaning) {
            mode = 'meaning';
            meaning = props.match.params.meaning;
            loadList = true;
        } else if (props.match.params.kanji) {
            mode = props.location.pathname.match(/\/edit\//) ? 'edit-kanji' : 'kanji';
            kanji = props.match.params.kanji;
        }
        //console.log(props.location.pathname);

        this.state = {
            authorized: false,
            authorizing: true,
            page: 0,
            pageSize: 24,
            hasMoreItems: true,
            kanji: kanji,
            levels: levels,
            catalog: catalog,
            catalogs: [],
            reading: reading,
            meaning: meaning,
            mode: mode,
            prevMode: mode,
            loadList: loadList,
            user: null

        };
        this.changeLevels.bind(this);
        this.changeCatalog.bind(this);
        this.changeReading.bind(this);
        this.changeMeaning.bind(this);
        this.changeMode.bind(this);
        this.changeKanji.bind(this);
        this.selectKanji.bind(this);
        this.onLevelsChanged.bind(this);
        this.back.bind(this);
    };

    componentDidMount() {
        Japo.isAuthorized().then(user => {
            let authorized = user !== false;
            this.setState(Object.assign({}, this.state, {
                authorized:authorized,
                authorizing:false,
                user: user
            }));
            return authorized;
        }).then(authorized => {
            if (authorized) {
                Japo.kanjiCatalogs().then(catalogs => {
                    this.setState(Object.assign({}, this.state, {
                        catalogs: catalogs,
                        authorized: true
                    }));
                });
            }
        });

    };


    loadingSpinner() {
        return <LoadingSpinner loading={true} />;
    }

    changeMode(mode, params) {
        let newState = {
            prevMode: this.state.mode,
            mode: mode
        };
        if (params) {
            newState = Object.assign({}, newState, params);
        }
        this.setState(Object.assign({}, this.state, newState));
    }

    changeCatalog(event, catalog) {
        this.changeMode('catalog', {
            catalog: catalog,
            levels: [],
            loadList: true
        });
        if (!catalog || catalog.trim() === '') {
            this.props.history.push("/kanji/");
        } else {
            this.props.history.push("/kanji/catalog/" + catalog);
        }

    }
    changeLevels(e, levels) {
        this.changeMode('catalog', {
            levels: levels,
            loadList: true
        });
    }
    onLevelsChanged(e, levels) {
        this.changeMode('catalog', {
            levels: levels
        });
        this.props.history.push("/kanji/catalog/" + this.state.catalog + '/' + _.join(levels, '/'));
    }

    changeReading(event) {
        let reading = wanakana.toKana(event.target.value, { IMEMode: true });
        if (wanakana.isKana(reading)) {
            this.changeMode('reading', {
                reading: reading,
                levels: [],
                loadList: true,
            });
            this.props.history.push("/kanji/reading/" + reading);
        } else {
            this.changeMode('reading', {
                reading: reading
            });
        }
    }

    changeMeaning(event) {
        let meaning = event.target.value;
        if (meaning.length > 2) {
            this.changeMode('meaning', {
                meaning: meaning,
                levels: [],
                loadList: true,
            });
            this.props.history.push("/kanji/meaning/" + meaning.trim());
        } else {
            this.changeMode('meaning', {
                meaning: meaning
            });
        }
    }

    changeKanji(event) {
        let kanji = event.target.value;
        if (!kanji || !wanakana.isKanji(kanji) || kanji.length > 1) {
            return;
        }
        this.changeMode('kanji', {kanji: kanji});
    }

    selectKanji(event, kanji) {
        if (event) {
            if (event.button === 1) {
                return; //Middle clicks are allowed to open in a new tab
            }
            event.preventDefault();
        }
        this.changeMode('kanji', {kanji: kanji});

    }

    back(event) {
        this.changeMode(this.state.prevMode, {});
        this.props.history.goBack();
    }

    editKanji(e, kanji) {
        if (kanji) {
            this.props.history.push("/kanji/edit/" + kanji);
        }
    }

    mainArea() {
        if (this.state.authorizing || !this.state.authorized) {
            return (<div></div>);
        }
        let activeProperty = undefined;
        switch (this.state.mode) {
            case 'reading':
                activeProperty = this.state.reading;
                break;
            case 'meaning':
                activeProperty = this.state.meaning;
                break;
            case 'kanji':
            case 'edit-kanji':
                activeProperty = this.state.kanji;
                break;
            default:
            case 'catalog':
                activeProperty = this.state.catalog;
        }
        if (!activeProperty) {
            return this.state.mode === 'catalog' ? <CatalogGrid catalogs={this.state.catalogs} onChange={this.changeCatalog.bind(this)}/> : '';
        } else {
            return (this.state.mode === 'kanji' || this.state.mode === 'edit-kanji') ?
                <KanjiDetails kanji={this.state.kanji} edit={this.state.mode === 'edit-kanji'}
                              backButton={this.state.prevMode !== 'kanji' && this.state.prevMode !== 'edit-kanji'}
                              onBack={this.back.bind(this)} allowEdit={['admin', 'editor'].indexOf(this.state.user.role) !== -1} /> :
                <KanjiList  hasMoreItems={this.state.loadList} mode={this.state.mode}
                            catalog={this.state.catalog} levels={this.state.levels}
                            reading={this.state.reading} meaning={this.state.meaning}
                            changeLevel={this.onLevelsChanged.bind(this)} selectKanji={this.selectKanji.bind(this)}
                />;
        }
    }

    leftNav() {
        const { t } = this.props;
        let buttons = {
            catalog: {
                label: t('leftNav.buttons.catalog'),
                element: <span className="fa fa-book fa-1" />
            },
            kanji:   {
                label: t('leftNav.buttons.kanji'),
                element: <span>漢字</span>
            },
            reading: {
                label: t('leftNav.buttons.reading'),
                element: <span>あア</span>
            },
            meaning: {
                label: t('leftNav.buttons.meaning'),
                element: <span className="fa fa-comment" />
            }
        };
        return <LeftTabs className="kanji-search-group"
                         selected={this.state.mode === 'edit-kanji' ? 'kanji' : this.state.mode}
                         items={buttons} onChange={(e) => this.changeMode(e.target.value)}>
            <LeftTab name="catalog">
                <div className="row">
                    <div className="col-sm-10">
                        <label className="control-label">{t('leftNav.labels.catalog')}</label>
                        <select className="form-control" tabIndex="1" value={this.state.catalog} onChange={(e) => this.changeCatalog(e, e.target.value)}>
                            {[{slug:'', name:'---'}].concat(this.state.catalogs).map((catalog, i) => <option key={i} value={catalog.slug}>{catalog.name}</option>)}
                        </select>
                    </div>
                </div>
                {this.state.catalog ? <div className="row">
                    <div className="col-sm-3">
                        <label htmlFor="romaji" className="control-label">{t('leftNav.labels.level')}</label>
                    </div>
                    <div className="col-sm-4">
                        <input className="form-control" name="romaji" autoComplete="off"
                               onChange={(e) => this.changeLevels(e, [e.target.value])}
                               tabIndex="2" value={this.state.levels.length === 1 ? this.state.levels[0] : ''} type="number" />
                    </div>
                </div> : ''}
            </LeftTab>
            <LeftTab name="kanji">
                <label className="control-label">{t('leftNav.labels.kanji')}</label>
                <JDictAutocomplete value={this.state.kanji} onChange={(e) => this.changeKanji(e)} autoComplete="off" type="text"/>
            </LeftTab>
            <LeftTab name="reading">
                <div className="row">
                    <div className="col-sm-10">
                        <label className="control-label">{t('leftNav.labels.reading')}</label>
                        <input className="form-control" value={this.state.reading} onChange={(e) => this.changeReading(e)} placeholder={t('leftNav.labels.reading')}/>
                    </div>
                </div>
            </LeftTab>
            <LeftTab name="meaning">
                <div className="row">
                    <div className="col-sm-10">
                        <label className="control-label">{t('leftNav.labels.meaning')}</label>
                        <input className="form-control" value={this.state.meaning} onChange={(e) => this.changeMeaning(e)} placeholder={t('leftNav.labels.meaning')}/>
                    </div>
                </div>
            </LeftTab>
        </LeftTabs>
    }


    render() {
        const { t } = this.props;
        return this.state.authorized ? (
                <div className="container">
                    <Header pageClass="japo" authorized={this.state.authorized}/>
                    <SubNav selected="kanji"/>
                    <div className="row">
                        <LeftNav className="kanji-grid-left">
                            {this.leftNav()}
                        </LeftNav>
                        <MainArea>
                            {this.mainArea()}
                        </MainArea>
                    </div>
                </div>
        ) : (
            <div className="container">
                <Header pageClass="japo" authorized={this.state.authorized}/>
                <PageTitle pageTitle={t('home.title')} sub={t('home.title-sub')}>{t('home.main')}</PageTitle>
                <SubNav empty/>
                <div className="row">
                    <div className="col-sm-12">
                        <button className="btn btn-primary " onClick={(e) => Japo.login()}>{t('common.login')}</button>
                    </div>
                </div>
            </div>
        );
    }
}

export default withRouter(withNamespaces('japo')(KanjiApp));

import React, { Component } from 'react';
import ReactTooltip from 'react-tooltip'
import { withRouter } from 'react-router'

import LoadingSpinner from './components/LoadingSpinner'
import HorizontalItem from './components/Elements/HorizontalItem'
import DetailsRow from './components/Elements/DetailsRow'
import KanjiDetailsRow from './components/kanji/KanjiDetailsRow'
import Autocomplete from './components/Elements/Autocomplete'

import './KanjiDetails.css';
import { withNamespaces} from 'react-i18next';

const Japo = require('./lib/japo/japo');
const wanakana = require('wanakana');
const _ = require('lodash');

const Reading = (props) => {
    const reading = props.reading;
    let helpWord = '';
    if (reading.helpWord !== undefined && reading.helpWord !== null) {
        helpWord = <span className="helpword" lang="ja">
        「<span className="helpword-kanji">{reading.helpWord.kanji}</span>: <span className="helpword-kana">{reading.helpWord.kana}</span>」
        </span>;
    }
    return <span className="reading-item">
        <span className="reading-reading" lang="ja">{reading.reading}</span>
        {helpWord}
    </span>;
};

class KanjiDetails extends Component {

    constructor(props) {
        super();
        this.state = {
            edit: !!props.edit,
            kanji: props.kanji ? props.kanji : '',
            strokes: [],
            meanings: [],
            kun: [],
            on: [],
            words: [],
            catalogs: [],
            loading: false,
            kanjiCatalogs: [],
            allowEdit: props.allowEdit !== undefined ? props.allowEdit : false
        };
        this.editMode.bind(this);
        this.meaningChanged.bind(this);
        this.readingChanged.bind(this);
        this.catalogChanged.bind(this);
    };

    componentDidMount() {
        if (this.state.kanji !== '') {
            this.loadKanji();
        }
        Japo.kanjiCatalogs().then(catalogs => {
            this.setState(Object.assign({}, this.state, {kanjiCatalogs: catalogs}));
        });
    }

    componentWillReceiveProps(nextProps) {
        let newState = {
            edit: !!nextProps.edit,
            kanji: nextProps.kanji ? nextProps.kanji : '',
            allowEdit: nextProps.allowEdit !== undefined ? nextProps.allowEdit : false
        };
        if (this.props.location.pathname !== nextProps.location.pathname) {
            newState.edit = nextProps.location.pathname.match(/\/edit\//);
        }
        let reload = nextProps.kanji !== this.state.kanji;
        if (reload) {
            this.loadKanji(nextProps.kanji);
        } else {
            this.setState(Object.assign({}, this.state, newState));
        }
    }

    loadKanji(kanji) {
        if (!kanji) {
            kanji = this.state.kanji;
        }
        if (kanji === undefined || kanji === '' || !wanakana.isKanji(kanji) || kanji.length > 1) {
            return;
        }
        this.setState(Object.assign({}, this.state, {
            kanji: '',
            strokes: [],
            meanings: [],
            kun: [],
            on: [],
            words: [],
            catalogs: [],
            loading: true
        }));
        Japo.kanji(kanji).then((k) => {
            if (k) {
                this.setState(Object.assign({}, this.state, {
                    kanji: kanji,
                    strokes: k.strokes,
                    meanings: k.meanings,
                    kun: k.kun,
                    on: k.on,
                    words: k.words,
                    catalogs: k.catalogs,
                    loading: false
                }));
                let expectedPath = "/kanji/" + (this.state.edit ? 'edit/' : 'details/') + k.kanji;
                if (this.props.location.pathname !== expectedPath)　{
                    this.props.history.push(expectedPath);
                }　
            } else {
                this.setState(Object.assign({}, this.state, {loading:false}));
            }
        })
        .catch((reason) => {
            this.setState(Object.assign({}, this.state, {loading:false}));
            console.log(reason);
        });
    }

    meaningChanged(event, pos) {
        let meaning = event.target.value;
        let meanings = this.state.meanings.slice();
        meanings[pos] = meaning;
        this.setState(Object.assign({}, this.state, {
            meanings: _.compact(meanings)
        }));
    }

    catalogChanged(catalog, level, n, pos) {
        let catalogs = this.state.catalogs.slice();
        catalogs[pos] = Object.assign({}, catalogs[pos], {
            name: catalog.name,
            id: catalog.id,
            level: level,
            n: n
        });
        this.setState(Object.assign({}, this.state, {
            catalogs: catalogs.filter(c => c.name !== '')
        }));

    }

    readingChanged(event, type, pos) {
        let reading = event.target.value, property;
        if (type === 'K') {
            reading = wanakana.toHiragana(reading);
            property = 'kun';
        } else {
            reading = wanakana.toKatakana(reading);
            property = 'on';
        }
        let readings = this.state[property].slice();
        readings[pos] = Object.assign({}, readings[pos], {reading: reading, type: type});
        let newProps = {};
        newProps[property] = readings.filter(r => r.reading !== '');
        this.setState(Object.assign({}, this.state, newProps));

    }

    helpWordChange(word, type, pos) {
        let property = type === 'K' ? 'kun' : 'on';
        let readings = this.state[property].slice();
        readings[pos] = Object.assign({}, readings[pos], {helpWord: word, type: type});
        let newProps = {};
        newProps[property] = readings;
        this.setState(Object.assign({}, this.state, newProps));
    }



    editReading(reading, pos) {
        //transformValue={(option) => <span lang="ja">{option ? option.kanji + '(' + option.kana + ')' : ''}</span>}
        return <div className="row" key={pos}>
            <div className="col-sm-5">
                <input type="text" value={reading.reading} className="form-control" onChange={(e) => this.readingChanged(e, reading.type, pos)} />
            </div>
            <div className="col-sm-5">
                <Autocomplete value={reading.helpWord}
                              options={this.state.words}
                              className="helpword"
                              lang="ja"
                              transformValue={(option) => option ? option.kanji + '(' + option.kana + ')' : ''}
                              onChange={(e) => {
                                  if (e.option) {
                                      this.helpWordChange(e.option, reading.type, pos)
                                  }
                              }}
                              search={(option, value) => {
                                  if (value.length < 2) {
                                      return true;
                                  } else {
                                      return option.kana.indexOf(wanakana.toKana(value)) !== -1;
                                  }
                              }}

                />
            </div>

        </div>;
    };

    editCatalog(catalog, pos) {
        return <div className="row" key={pos}>
            <div className="col-sm-5">
                <Autocomplete value={catalog}
                              className="catalogs"
                              inputClassName="input-sm"
                              options={!this.state.kanjiCatalogs ? [] : this.state.kanjiCatalogs.filter(c => {
                                  return this.state.catalogs.filter(current => current.id === c.id).length === 0;
                              })}
                              transformValue={(option) => option ? option.name : ''}
                              onChange={(e) => {
                                  let option = e.option ? e.option : {name: '', id: ''};
                                  this.catalogChanged(option, catalog.level, catalog.n, pos)
                              }}
                />
            </div>
            <div className="col-sm-2 col-sm-offset-1">
                <input className="form-control input-sm"
                       value={catalog.level}
                       onChange={(e) => this.catalogChanged(catalog, e.target.value, catalog.n, pos)} />
            </div>
            <div className="col-sm-2">
                <input className="form-control input-sm"
                       value={catalog.n}
                       onChange={(e) => this.catalogChanged(catalog, catalog.level, e.target.value, pos)} />
            </div>
        </div>;
    }

    back(event) {
        if (this.props.onBack) {
            this.props.onBack(event);
        }
    }


    saveKanji(event) {
        if (!this.state.kanji || this.state.kanji.length === '') {
            this.editMode(event, false);
        }
        this.setState(Object.assign({}, this.state, {loading:true}));
        Japo.saveKanji({
            'kanji': this.state.kanji,
            'kun': this.state.kun.filter(r => r.reading !== '').map((r, i) => {return {reading: r.reading, helpWord: r.helpWord ? r.helpWord.id : undefined}}),
            'on':   this.state.on.filter(r => r.reading !== '').map((r, i) => {return {reading: r.reading, helpWord: r.helpWord ? r.helpWord.id : undefined}}),
            'meanings': this.state.meanings.filter(m => m !== ''),
            'catalogs': this.state.catalogs.filter(c => c.id !== '').map((c, i) => {return {id: c.id, level: c.level, n: c.n}})
        }).then((k) => {
            this.setState(Object.assign({}, this.state, {loading:false}));
            this.editMode(event, false);
        })
        .catch((reason) => {
            console.log(reason);
            this.setState(Object.assign({}, this.state, {loading:false}));
        });
    }



    editKanji() {
        return <form className="kanji-details">
            <KanjiDetailsRow kanji={this.state.kanji} strokes={this.state.strokes} />
            <DetailsRow className="kanji-meanings" label="Significados">
                {_.chunk(this.state.meanings.concat(['']), 2).map((meanings, i) => {
                    return <div key={i} className="row">
                        {meanings.map((meaning, j) => {
                            let pos = (i * 2)+ j;
                            return <div key={j} className="col-sm-5">
                                <input type="text" className="form-control" value={meaning} onChange={(e) => this.meaningChanged(e, pos)}/>
                            </div>;
                        })}
                    </div>
                })}
            </DetailsRow>
            <DetailsRow className="kanji-kun" label="Kun">
                {this.state.kun.concat([{reading:'', type:'kun'}]).map((reading, i) => {
                    return this.editReading(reading, i);
                })}
            </DetailsRow>
            <DetailsRow className="kanji-on" label="On">
                {this.state.on.concat([{reading:'', type:'on'}]).map((reading, i) => {
                    return this.editReading(reading, i);
                })}
            </DetailsRow>
            <DetailsRow className="kanji-catalogs" label="Catálogos">
                {this.state.catalogs.concat([{name:'', id:'', level: '', n: ''}]).map((c, i) => {
                    return this.editCatalog(c, i);
                })}
            </DetailsRow>
            <DetailsRow className="buttons" noLabel={true}>
                <div className="row">
                    <div className="col-sm-4 col-sm-offset-6 save-button">
                        <button type="button" className="btn btn-primary" onClick={this.saveKanji.bind(this)}>Guardar</button>
                        <button type="button" className="btn btn-secondary" onClick={(e) => this.editMode(e, false)}>Listo</button>
                    </div>
                </div>
            </DetailsRow>
            <DetailsRow className="kanji-words" label="Palabras">
                {this.state.words.map((word, i) => {
                    return <HorizontalItem key={i} separator=" " last={i === this.state.words.length - 1}>
                        <a className="word" data-tip data-for={'word-' + word.id} >{word.kanji}({word.kana})</a>
                        <ReactTooltip id={'word-' + word.id}>
                            <div className="tooltip-word">{word.kanji}({word.kana})</div>
                            {word.meanings.map((m, i) => {
                                return <div className="tooltip-meaning" key={i}>{m}</div>
                            })}
                        </ReactTooltip>
                    </HorizontalItem>
                })}
            </DetailsRow>
        </form>
    }

    editMode(event, mode) {
        this.setState(Object.assign({}, this.state, {edit: mode}));
        if (this.state.kanji) {
            let url = '';
            if (mode) {
                url = "/kanji/edit/" + this.state.kanji;
            } else {
                url  ="/kanji/details/" + this.state.kanji;
            }
            if (this.props.location.pathname !== url) {
                this.props.history.push(url);
            }


        }
    }

    kanjiDetails() {
        const { t } = this.props;
        let buttons = <div className="kanji-action-buttons">
                        {this.state.allowEdit ?
                            <button className="btn btn-primary" onClick={(e) => this.editMode(e, true)}><span className="fa fa-pencil-square-o fa-1"/></button> : ''
                        },
                        {this.props.backButton ?
                            <button className="btn btn-primary" onClick={this.back.bind(this)}><span className="fa fa-reply fa-1"/></button> : ''
                        }
                     </div>;
        return <div className="kanji-details">
                    <KanjiDetailsRow kanji={this.state.kanji} strokes={this.state.strokes} buttons={buttons} />
                    <DetailsRow className="kanji-meanings" label={t('KanjiDetails.labels.meanings')}>
                        {this.state.meanings.map((meaning, i) => {
                            return <HorizontalItem key={i} last={i === this.state.meanings.length - 1}>{meaning}</HorizontalItem>
                        })}
                    </DetailsRow>
                    <DetailsRow className="kanji-kun" label={t('KanjiDetails.labels.kun')}>
                        {this.state.kun.map((reading, i) => {
                            return <HorizontalItem key={i} separator="/ " last={i === this.state.kun.length - 1}><Reading reading={reading}/></HorizontalItem>
                        })}
                    </DetailsRow>
                    <DetailsRow className="kanji-on" label={t('KanjiDetails.labels.on')}>
                        {this.state.on.map((reading, i) => {
                            return <HorizontalItem key={i} separator="/ " last={i === this.state.on.length - 1}><Reading reading={reading}/></HorizontalItem>
                        })}
                    </DetailsRow>
                    <DetailsRow className="kanji-catalogs" label={t('KanjiDetails.labels.catalogs')}>
                        {this.state.catalogs.map((catalog, i) => {
                            return <HorizontalItem key={i} separator=" " last={i === this.state.catalogs.length - 1}>
                                <span className="catalog">{catalog.name} {catalog.level}-{catalog.n}</span>
                            </HorizontalItem>
                        })}
                    </DetailsRow>
                    <DetailsRow className="kanji-words" label={t('KanjiDetails.labels.words')}>
                        {this.state.words.map((word, i) => {
                            return <HorizontalItem key={i} separator=" " last={i === this.state.words.length - 1}>
                                <a className="word" lang="ja" data-tip data-for={'word-' + word.id} >{word.kanji}({word.kana})</a>
                                <ReactTooltip id={'word-' + word.id}>
                                    <div className="tooltip-word" lang="ja">{word.kanji}({word.kana})</div>
                                    {word.meanings.map((m, i) => {
                                        return <div className="tooltip-meaning" key={i}>{m}</div>
                                    })}
                                </ReactTooltip>
                            </HorizontalItem>
                        })}
                    </DetailsRow>
            </div>;

    }

    render() {
        let mode = 'details';
        if (this.state.loading) {
            mode = 'loading';
        } else if (!this.state.kanji) {
            mode = 'blank';
        } else if (this.state.edit) {
            mode = 'edit';
        }
        switch (mode) {
            case 'loading':
                return <LoadingSpinner loading={true} />;
            case 'details':
                return this.kanjiDetails();
            case 'edit':
                return this.editKanji();
            default:
            case 'blank':
                return <div/>
        }

    }
}

export default withRouter(withNamespaces('japo')(KanjiDetails));

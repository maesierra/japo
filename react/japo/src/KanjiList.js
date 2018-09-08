import React, { Component } from 'react';

import KanjiGrid from './components/kanji/KanjiGrid'
import LoadingSpinner from './components/LoadingSpinner'
import HorizontalItem from './components/Elements/HorizontalItem'
import { withRouter } from 'react-router-dom'

import './KanjiList.css';
import InfiniteScroll from 'react-infinite-scroller';
const Japo = require('./lib/japo/japo');
const _ = require('lodash');

class KanjiList extends Component {
    
    constructor(props) {
        super();
        this.state = {
            page: 0,
            pageSize: 24,
            hasMoreItems: props.hasMoreItems,
            list: [],
            levels: props.levels,
            catalog: props.catalog ? props.catalog : '',
            catalogLevels: [],
            reading: props.reading ? props.reading : '',
            meaning: props.meaning ? props.meaning : '',
            mode: props.mode,

        };
        this.changeLevel.bind(this);
        this.selectKanji.bind(this);
    };

    componentWillReceiveProps(nextProps) {
        let newState = {
            hasMoreItems: nextProps.hasMoreItems,
            levels: nextProps.levels,
            catalog: nextProps.catalog ? nextProps.catalog : '',
            reading: nextProps.reading ? nextProps.reading : '',
            meaning: nextProps.meaning ? nextProps.meaning : '',
            mode: nextProps.mode,

        };
        let reload = false;
        if (nextProps.mode !== this.state.mode) {
            reload = true;
        } else {
            switch (this.state.mode) {
                case 'catalog':
                    reload = !_.isEqual(this.state.levels, nextProps.levels) || this.state.catalog !== nextProps.catalog;
                    break;
                case 'reading':
                    reload = this.state.reading !== nextProps.reading;
                    break;
                case 'meaning':
                    reload = this.state.meaning !== nextProps.meaning;
                    break;
                default:
                    break;
            }
        }
        if (reload) {
            newState = Object.assign({}, newState, {
                list:[],
                catalogLevels:[],
                page: 0
            });
        }
        this.setState(Object.assign({}, this.state, newState));
    }



    load(page) {
        let params = undefined;
        let mode = undefined;
        switch (this.state.mode) {
            case 'reading':
                mode = 'reading';
                params = {
                    reading: this.state.reading
                };
                break;
            case 'meaning':
                mode = 'meaning';
                params = {
                    meaning: this.state.meaning
                };
                break;
            case 'catalog':
            default:
                mode = 'catalog';
                params = {
                    'catalog': this.state.catalog,
                    'level[]': this.state.levels
                };
        }
        Japo.kanjiQuery(params, this.state.page, this.state.pageSize).then((response) => {
            if (response) {
                //We need to verify if the current state matches the requested data
                if (mode !== this.state.mode) {
                    return;
                }
                let changed = undefined;
                switch (this.state.mode) {
                    case 'reading':
                        changed = this.state.reading !== response.request.reading;
                        break;
                    case 'meaning':
                        changed = this.state.meaning !== response.request.meaning;
                        break;
                    default:
                    case 'catalog':
                        changed = this.state.catalog !== response.request.catalog ||
                                  !response.request.level ? this.state.levels.length !== 0 : !_.isEqual(this.state.levels, response.request.level);

                }
                if (changed) {
                    return;
                }
                let currentPage = this.state.page;
                this.setState(Object.assign({}, this.state, {
                    list: this.state.list.concat(response.kanjis.map((k, i) => { return {
                        kanji: k.kanji,
                        meanings: k.meanings,
                        kun: k.kun,
                        on: k.on,
                        catalog: mode === 'catalog' ? k.catalogs.filter(c => c.slug === this.state.catalog)[0] : {n: '', level:''}
                    }})),
                    page: currentPage + 1,
                    hasMoreItems: response.hasMore,
                    catalogLevels: response.catalogLevels
                }));
            }
        })
         .catch((reason) => {
            console.log(reason);
         });
    }

    loadingSpinner() {
        return <LoadingSpinner loading={true} />;
    }

    changeLevel(e, level) {
        let levels =  level ? [level] : [];
        if (e.ctrlKey) {
            levels = this.state.levels.concat(levels);
        }
        this.setState(Object.assign({}, this.state, {
            levels: levels,
            list: [],
            hasMoreItems: true,
            page: 0
        }));
        if (this.props.changeLevel) {
            this.props.changeLevel(e, levels);
        }
    }

    selectKanji(event, kanji) {
        if (this.props.selectKanji) {
            this.props.selectKanji(event, kanji);
        }
    }

    render() {
        return (
              <div className="kanji-list">
              <InfiniteScroll pageStart={0} loadMore={this.load.bind(this)} hasMore={this.state.hasMoreItems} loader={this.loadingSpinner()} threshold={800}>
                <div key="level-selector" className="kanji-grid-levels">
                    {this.state.catalogLevels.map((level, i) =>
                        <HorizontalItem key={i} last={i === this.state.catalogLevels.length - 1} separator=" | ">
                            <a key={i} className={this.state.levels.includes(level) ? 'selected' : ''} onClick={(e) => this.changeLevel(e, level)}>{level}</a>
                        </HorizontalItem>
                    )}
                    {(this.state.catalogLevels.length > 0  && this.state.levels.length > 0) ?
                        <button className="btn btn-primary btn-xs" onClick={(e) => this.changeLevel(e)}><span className="fa fa-times fa-1"/></button> :
                        ''
                    }
                </div>
                <KanjiGrid>
                    {this.state.list.map((kanji, i) =>
                        <div className="kanji" key={i}>
                            <div className="row">
                                <div className="col-sm-3 text-right kanji-level">{kanji.catalog.level}</div>
                                <div className="col-sm-6 col-sm-offset-3 text-right kanji-n">{kanji.catalog.n}</div>
                            </div>
                            <div className="row">
                                <div className="col-sm-12 text-center">
                                    <a className="kanji-char" lang="ja" href={'/kanji/details/' + kanji.kanji} onClick={(e) => this.selectKanji(e, kanji.kanji)}>{kanji.kanji}</a>
                                </div>
                            </div>
                            <div className="row">
                                <div className="col-sm-11 col-sm-offset-1 text-left kanji-kun-readings" lang="ja">{_.join(kanji.kun.map((r, i) => r.reading), '/ ')}</div>
                            </div>
                            <div className="row">
                                <div className="col-sm-11 col-sm-offset-1 text-left kanji-on-readings" lang="ja">{_.join(kanji.on.map((r, i) => r.reading), '/ ')}</div>
                            </div>
                            <div className="row">
                                <div className="col-sm-11 col-sm-offset-1 text-left kanji-meanings">{_.join(kanji.meanings, ', ')}</div>
                            </div>
                        </div>
                    )}
                </KanjiGrid>
            </InfiniteScroll>
            </div>
        );
    }
}

export default withRouter(KanjiList);

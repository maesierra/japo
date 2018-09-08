import React, { Component } from 'react';

import KanjiGrid from '../KanjiGrid'
import './CatalogGrid.css';

class CatalogGrid extends  Component {

    constructor(props) {
        super();
        this.changeCatalog.bind(this);
    }

    changeCatalog(event, catalog) {
        if (event) {
            if (event.button === 1) {
                return; //Middle clicks are allowed to open in a new tab
            }
            event.preventDefault();
        }
        if (this.props.onChange) {
            this.props.onChange(event, catalog);
        }
    }

    render() {
        return (
            <KanjiGrid className="kanji-catalog-list">
                {this.props.catalogs.map((catalog, i) => <a key={i} href={'/catalog/' + catalog.slug} onClick={(e) => this.changeCatalog(e, catalog.slug)} className="catalog-link">{catalog.name}</a>)}
            </KanjiGrid>
        );
    }
}

export default CatalogGrid;

import React from 'react';
import './KanjiGrid.css';
const _ = require('lodash');

class KanjiGrid extends React.Component {


    row(items, pos) {
        return <div key={pos} className="row kanji-row">
            {items.map((item, i) => <div key={i} className="col-sm-2"><div className="kanji-grid-item">{item}</div></div>)}
        </div>;
    }

    render() {
        let {children, className} = this.props;
        const classNames = ['kanji-grid', 'col-sm-12'];
        if (className) {
            classNames.push(className);
        }
        return <div className={classNames.join(' ')}>
            {_.chunk(children, 6).map((items, i) => this.row(items, i))}
        </div>;
    }

};

export default KanjiGrid;

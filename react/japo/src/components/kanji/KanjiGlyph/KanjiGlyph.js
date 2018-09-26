import React from 'react';
import './KanjiGlyph.css'


class KanjiGlyph extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            hover: false
        };
        this.hover.bind(this);
    }

    hover(hover) {
        this.setState(Object.assign({}, this.state, {'hover': hover}));
    }

    render()  {
        let kanji = this.props.kanji;
        if (kanji === undefined || kanji === '') {
            return <div className="no-kanji" />;
        } else {
            return <div className="kanji-glyph">
                <a onMouseOver={() => this.hover(true)} onMouseOut={() => this.hover(false)} className={this.state.hover ? '' : 'serif'} href={"/kanji/details/" + kanji} lang="ja">{kanji}</a>
            </div>
        }
    }
}

export default KanjiGlyph;

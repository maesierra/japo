import React from 'react';
import DetailsRow from '../../Elements/DetailsRow'
import KanjiGlyph from './../KanjiGlyph/'
import KanjiStrokes from '../KanjiStrokes'


const KanjiDetailsRow = (props) => {
    let {kanji, strokes, buttons} = props;
    /** We're going to aim for 2 kanji rows of 12 strokes... if the kanji has more than 24... we need to reduce
     Jdict doesn't have any kanji with more than 33 characters that are in the unicode standard... so we could
     assume that 3 rows will be enough
     */
    let strokesArea = <KanjiStrokes strokes={strokes} maxColumns={12} colSize={strokes.length >= 24 ? 33 : 50}  />;
    if (buttons) {
        strokesArea = <div className="row">
                        <div className="col-sm-11">{strokesArea}</div>
                        <div className="col-sm-1">{buttons}</div>
                      </div>;
    }

    return <DetailsRow className="kanji-main">
        <KanjiGlyph kanji={kanji} />
        {strokesArea}
    </DetailsRow>
};

export default KanjiDetailsRow;

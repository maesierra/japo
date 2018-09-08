import React from 'react';


const Stroke = (props) => {
    let {column, row, index, strokes} = props;
    const strokeStyle = {
        fill: 'none',
        stroke: 'black',
        strokeWidth: 6
    };
    return <g id={'draw-group' + index} style={strokeStyle} transform={'translate(' + (column * 100) + ' , ' + (row * 100) + ')'}>
        {strokes.map((s, i) => {
            let currentStyle = (i === strokes.length)?  {strokeWidth: 9} : {};
            return <use key={i} href={'#stroke' + i} style={currentStyle} />
        })}
    </g>;
};

const KanjiStrokes = (props) => {
    let { strokes, maxColumns, colSize } = props;
    if (strokes.length === 0) {
        return <div className="no-strokes"></div>;
    }
    let nColumns = 0;
    if (maxColumns === undefined) {
        nColumns = strokes.length;
    } else {
        nColumns = Math.min(strokes.length, maxColumns);
    }
    let nRows = Math.ceil(strokes.length / nColumns);
    let width = 100 * nColumns;
    let height = 100 * nRows;
    if (colSize === undefined) {
        colSize = 100;
    }
    let imageWidth = colSize * nColumns;
    let imageHeight = colSize * nRows;
    let column = 0;
    let row = 0;
    return <div className="kanji-strokes">
        <svg width={imageWidth} height={imageHeight} version="1.1">
            <defs>
                {strokes.map((s, i) => <path key={i} d={s.path} id={'stroke' + i}/>)}
            </defs>
            <g transform={'scale(' + (imageWidth / width) + ' , ' + (imageHeight / height) + ')'}>
                {strokes.map((s, i) => {
                    if (column === nColumns) {
                        row++;
                        column = 0;
                    }
                    return <g key={i}>
                        <Stroke index={i} strokes={strokes.slice(0, i + 1)} column={column++} row={row} />
                    </g>;
                })}
            </g>
        </svg>
    </div>
};



export default KanjiStrokes;

import React from 'react';


const DetailsRow = (props) => {
    let {className, children, label, noLabel} = props;
    const classNames = ['row'];
    const leftCol = 'col-sm-2 left-column';
    const rightCol = 'col-sm-10 right-column';
    if (className) {
        classNames.push(className);
    }
    let left = '';
    if (label) {
        left = <label>{label}:</label>
    } else if (noLabel) {
        left = '';
    } else {
        left = children[0];
        children = children.slice(1);
    }
    return <div className={classNames.join(' ')}>
        <div className={leftCol}>{left}</div>
        <div className={rightCol}>{children}</div>
    </div>;
}

export default DetailsRow;

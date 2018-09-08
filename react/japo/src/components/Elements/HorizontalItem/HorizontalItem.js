import React from 'react';
import './HorizontalItem.css'


const HorizontalItem = (props) => {
    let {last, className, children, separator, ...rest} = props;
    let classNames = ['horizontal-item'];
    if (className) {
        classNames.push(className);
    }
    if (!separator) {
        separator = ', ';
    }
    return <span className={classNames.join(' ')} {...rest}>{children}{!last ? separator : ''}</span>
};

export default HorizontalItem;

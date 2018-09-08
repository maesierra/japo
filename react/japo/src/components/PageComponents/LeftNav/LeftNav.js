import React from 'react';


const LeftNav = (props) => {
    const classNames = ['row', 'col-sm-3', 'left-nav'];
    const { children, className, ...rest } = props;
    if (className) {
        classNames.push(className);
    }
    return <div className={classNames.join(' ')} {...rest}>{children}</div>;
};

export default LeftNav;

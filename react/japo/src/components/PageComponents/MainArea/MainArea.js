import React from 'react';


const MainArea = (props) => {
    const classNames = ['col-sm-9', 'main-area'];
    const { children, className, ...rest } = props;
    if (className) {
        classNames.push(className);
    }
    return <div className={classNames.join(' ')} {...rest}>{children}</div>;
};

export default MainArea;

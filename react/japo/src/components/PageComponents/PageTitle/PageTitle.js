import React from 'react';
import './PageTitle.css'


const PageTitle = (props) => {
    return <div className="row">
            <div className="col-sm-12">
                <h1 className="title">{props.children}</h1>
                {(props.sub !== undefined) ?
                    <h4>{props.sub}</h4> :
                    ''
                }
            </div>
        </div>
};


export default PageTitle;

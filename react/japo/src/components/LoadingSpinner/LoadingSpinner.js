import React from 'react';
import './LoadingSpinner.css'


const LoadingSpinner = (props) => {
    if (props.loading) {
        return <div className="spinner">
            <span className="fa fa-spinner fa-spin fa-3x fa-fw"></span>
            <span className="sr-only">Cargando...</span>
        </div>;
    } else {
        return <div></div>;
    }
};


export default LoadingSpinner;

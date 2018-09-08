import React from 'react';
import { withRouter } from 'react-router-dom'


import Header from '../../components/PageComponents/Header/index'
import PageTitle from '../../components/PageComponents/PageTitle/index'


class NotFound extends React.Component {

    constructor(props) {
        super();
    }


    render() {
        return (
            <div className="container">
                <Header noMenu />
                <PageTitle pageTitle="404 - Not Found">404... Are you lost?</PageTitle>
                <div className="row">
                    <div className="col-sm-10">
                        If you have no place to go you could try go to the <a className="strong" href="/">homepage</a>
                    </div>
                </div>
            </div>
        );
    }
}

export default withRouter(NotFound);

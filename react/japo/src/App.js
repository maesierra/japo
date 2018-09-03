import React, { Component } from 'react';
import logo from './logo.svg';
import './App.css';
const Japo = require('./lib/japo/japo');

const ActionButton = (props) => {
    return (!props.authorized  ? <LoginButton /> : <LogoutButton />)
};

const LoginButton = (props) => {
    return <button className="btn btn-primary" onClick={(e) => Japo.login()}>Login</button>
};

const LogoutButton = (props) => {
    return <button className="btn btn-primary" onClick={(e) => Japo.logout()}>Logout</button>
};




class App extends Component {
    constructor(props) {
        super();
        this.state = {
            authorized: false,
            authorizing: true
        }
    };
    componentDidMount() {
        Japo.isAuthorized().then(authorized => {
            this.setState(Object.assign({}, this.state, {authorized:authorized, authorizing:false}));
            return authorized;
        }).then(authorized => {
            console.log('Authorized: ' + authorized);
        });

    };
    render() {
    return (
      <div className="App">
        <header className="App-header">
          <img src={logo} className="App-logo" alt="logo" />
          <h1 className="App-title">Welcome to React</h1>
        </header>
        <p className="App-intro">
            <ActionButton authorized={this.state.authorized}/>
        </p>
      </div>
    );
  }
}

export default App;

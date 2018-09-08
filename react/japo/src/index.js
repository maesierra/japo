import React from 'react';
import ReactDOM from 'react-dom';
import KanjiApp from './KanjiApp';
import NotFound from './modules/NotFound/NotFound';
import './main.css';

import { BrowserRouter,Switch, Route } from 'react-router-dom'

ReactDOM.render(
    <BrowserRouter>
        <Switch>
            <Route exact path='/kanji/'                   component={KanjiApp}/>
            <Route component={KanjiApp} />
        </Switch>
    </BrowserRouter>,
document.getElementById('root'));
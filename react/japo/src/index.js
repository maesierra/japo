import React from 'react';
import ReactDOM from 'react-dom';
import KanjiApp from './KanjiApp';
import NotFound from './modules/NotFound/NotFound';
import './main.css';
import * as serviceWorker from "./registerServiceWorker"

import { BrowserRouter,Switch, Route } from 'react-router-dom'

ReactDOM.render(
    <BrowserRouter basename={process.env.PUBLIC_URL}>
        <Switch>
            <Route exact path='/kanji/'                   component={KanjiApp}/>
            <Route exact path='/kanji/'                   component={KanjiApp}/>
            <Route exact path='/kanji/catalog/:catalog'   component={KanjiApp} />
            <Route exact path='/kanji/catalog/:catalog/*' component={KanjiApp} />
            <Route exact path='/kanji/reading/:reading'   component={KanjiApp} />
            <Route exact path='/kanji/meaning/:meaning'   component={KanjiApp} />
            <Route exact path='/kanji/details/:kanji'     component={KanjiApp}/>
            <Route exact path='/kanji/edit/:kanji'        component={KanjiApp}/>
            <Route component={KanjiApp} />
        </Switch>
    </BrowserRouter>,
document.getElementById('root'));
serviceWorker.unregister();
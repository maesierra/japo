require('es6-promise').polyfill();
require('isomorphic-fetch');
const _ = require('lodash');
const querystring = require('querystring');

const serverUrl = (process.env.NODE_ENV !== 'production') ? 'https://localhost:8043' : '';
const defaultRequestOptions = (process.env.NODE_ENV !== 'production') ? {mode: 'cors'} : {credentials: 'include'};
const Japo = {

};

function makeRequest(path, params, options) {
    options = Object.assign({}, defaultRequestOptions, !options ? {} : options);
    let url = serverUrl + path;
    if (params) {
        url = url + '?' + querystring.stringify(params);
    }
    return new Promise((resolve, reject) => {
        fetch(url, options).then((response) => {
            resolve(response)
        })
    });
}



Japo.isAuthorized = () => {
    return new Promise((resolve, reject) => {
        makeRequest(
            '/api/japo/index.php',
            {},
            {cache: 'no-cache'}
        ).then((response) => {
            let authorized = response.status === 200;
            /*
            if (!authorized) {
                document.location.href = '/api/japo/login.php';
            }*/
            resolve(authorized);
        })
        .catch(reject);
    });
};

Japo.login = () => {
    document.location.href = serverUrl + '/api/japo/login.php';
};

Japo.logout = () => {
    document.location.href = serverUrl + '/api/japo/logout.php';
};


module.exports = Japo;
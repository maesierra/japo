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
        }).catch(function(error) {
            console.log("Fetch error " + error);
            reject(error);
        });
    });
}



Japo.isAuthorized = () => {
    return new Promise((resolve, reject) => {
        makeRequest(
            '/api/japo/index.php',
            {},
            {cache: 'no-cache'}
        ).then((response) => {
            console.log(response.status);
            let authorized = response.status === 200;
            resolve(authorized);
        })
        .catch((error) => resolve(false));
    });
};

Japo.login = () => {
    document.location.href = serverUrl + '/api/japo/login.php';
};

Japo.logout = () => {
    document.location.href = serverUrl + '/api/japo/logout.php';
};


Japo.kanjiCatalogs = () => {
    return new Promise((resolve, reject) => {
        /*
        makeRequest('/japo/api/kanji_catalogs.php').then((response) => {
            resolve(response.json());
        })
            .catch(reject);*/
        resolve([{"id": "0", "name": "Basic Kanji Book I", "deprecated": false, "slug": "basic-kanji-book"}, {
            "id": "2",
            "name": "Ky\u014diku kanji",
            "deprecated": false,
            "slug": "kyoiku-kanji"
        }, {"id": "1", "name": "JLPT", "deprecated": false, "slug": "jlpt"}, {
            "id": "3",
            "name": "Ky\u014diku kanji (higher levels)",
            "deprecated": true,
            "slug": null
        }, {"id": "4", "name": "Minna no nihongo 2", "deprecated": false, "slug": "minna-no-nihongo-2"}, {
            "id": "5",
            "name": "Intermediate Kanji Book",
            "deprecated": false,
            "slug": "ikb"
        }])
    });

};
module.exports = Japo;
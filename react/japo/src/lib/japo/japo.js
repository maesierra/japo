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
            '/api/japo/',
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
    document.location.href = serverUrl + '/api/japo/auth/login';
};

Japo.logout = () => {
    document.location.href = serverUrl + '/api/japo/auth/logout';
};


Japo.kanjiCatalogs = () => {
    return new Promise((resolve, reject) => {

        makeRequest('/api/japo/kanji/catalogs').then((response) => {
            resolve(response.json());
        })
        .catch(reject);
    });

};

Japo.kanjiQuery = (queryParams, page = null, pageSize = null) => {
    let params = Object.assign(
        {},
        queryParams,
        (page !== null && pageSize !== null) ? {'page': page, 'pageSize': pageSize} : {}
    );
    return new Promise((resolve, reject) => {
        makeRequest('/api/japo/kanji/query', params).then((response) => {
            return response.json();
        }).then((results) => {
            resolve({
                catalog: params.catalog,
                catalogLevels: results.catalog ? results.catalog.levels : [],
                page: page,
                pageSize: pageSize,
                kanjis: results.kanjis.map((k, i) => {
                    return {
                        id: k.id,
                        kanji: k.kanji,
                        kun: k.readings.filter(r => r.type === 'K'),
                        on: k.readings.filter(r => r.type === 'O'),
                        catalogs: _.values(k.catalogs),
                        meanings: k.meanings
                    };
                }),
                total: results.total,
                hasMore: results.page.hasMore,
                request: params
            });
        })
        .catch(reject);

    });
};

Japo.jDict = (params) => {
    return new Promise((resolve, reject) => {
        makeRequest('/api/japo/jdict/query', params).then((response) => {
            return response.json();
        }).then((results) => {
            resolve({
                nPages: results.page.nPages,
                page: results.page.page,
                total: results.total,
                entries: results.entries.map((e, i) => {
                    return {
                        id: e.id,
                        reading: _.first(e.readings),
                        kanji: _.first(e.kanji),
                        gloss: e.gloss,
                        metadata: e.meta
                    };
                }),
            });
        })
        .catch(reject);
    });
};

module.exports = Japo;
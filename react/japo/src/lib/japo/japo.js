require('es6-promise').polyfill();
require('isomorphic-fetch');
const _ = require('lodash');
const querystring = require('querystring');

const serverUrl = ((process.env.NODE_ENV !== 'production') ? 'https://localhost:8043' : '') + process.env.PUBLIC_URL;
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



let createKanjiFromJson = function (kanji) {
    return Object.assign({}, kanji, {
        catalogs: _.values(kanji.catalogs).map((c, i) => {
            return {
                "name": c.catalogName,
                "id": c.catalogId,
                "level": c.level,
                "n": c.n,
                "slug": c.catalogSlug
            }
        })
    });
};

Japo.isAuthorized = () => {
    return new Promise((resolve, reject) => {
        makeRequest(
            '/api/',
            {},
            {cache: 'no-cache'}
        ).then((response) => {
            let authorized = response.status === 200;
            resolve(authorized);
        })
        .catch((error) => resolve(false));
    });
};

Japo.login = () => {
    document.location.href = serverUrl + '/api/auth/login?t' +  new Date().getTime();
};

Japo.logout = () => {
    document.location.href = serverUrl + '/api/auth/logout?t' +  new Date().getTime();
};

Japo.kanjiCatalogs = () => {
    return new Promise((resolve, reject) => {

        makeRequest('/api/kanji/catalogs').then((response) => {
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
        makeRequest('/api/kanji/query', params).then((response) => {
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
        makeRequest('/api/jdict/query', params).then((response) => {
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

Japo.kanji = (kanji) => {
    return new Promise((resolve, reject) => {
        makeRequest('/api/kanji/' + kanji, {}).then((response) => {
            return response.json();
        }).then((json) => {
            resolve(createKanjiFromJson(json));
        })
        .catch(reject);
    });
};


Japo.saveKanji = (kanji) => {
    kanji = Object.assign({}, kanji, {
        catalogs: _.keyBy(kanji.catalogs.map((c, i) => {
            return {
                "catalogName": c.name,
                "catalogId": c.id,
                "level": c.level,
                "n": c.n,
                "catalogSlug": c.slug
            }
        }), (c) => c.catalogId),
        kun: kanji.kun.map((r, i) => {
            return {
                "reading": r.reading,
                "type": 'K',
                "helpWord": r.helpWord === undefined ? null : {
                    "id": r.helpWord
                }
            }
        }),
        on: kanji.on.map((r, i) => {
            return {
                "reading": r.reading,
                "type": 'O',
                "helpWord": r.helpWord === undefined ? null : {
                    "id": r.helpWord
                }
            }
        })
    });
    return new Promise((resolve, reject) => {
        makeRequest('/api/kanji/' + kanji.kanji, {}, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(kanji)
        }).then((response) => {
            return response.json();
        }).then((json) => {
            resolve(createKanjiFromJson(json));
        })
        .catch(reject);
    });

};

module.exports = Japo;
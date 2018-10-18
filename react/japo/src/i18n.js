import i18n from 'i18next';
import Backend from 'i18next-xhr-backend';
import { reactI18nextModule } from 'react-i18next';

i18n
    .use(Backend)
    .use(reactI18nextModule)
    .init({
        fallbackLng: process.env.REACT_APP_LANGUAGE === undefined ? 'en' : process.env.REACT_APP_LANGUAGE,
        debug: false,

        interpolation: {
            escapeValue: false, // not needed for react as it escapes by default
        },
        react: {
            wait: true
        }
    });


export default i18n;
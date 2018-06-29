import CodeToggle from './CodeToggle'
import Vuex from 'vuex';
import { setStorage } from './Storage'

export default ({ Vue, options, router, siteData }) => {
    Vue.component('code-toggle', CodeToggle)

    Vue.use(Vuex)

    Object.assign(options, {
        data: {
            codeLanguage: null,
        },

        store: new Vuex.Store({
            state: {
                codeLanguage: null
            },
            mutations: {
                changeCodeLanguage(state, language) {
                    state.codeLanguage = language;
                    setStorage('codeLanguage', language);
                }
            }
        })
    })
}
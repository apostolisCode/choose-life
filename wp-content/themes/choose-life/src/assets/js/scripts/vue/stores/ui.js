import { defineStore } from 'pinia';
import { helpers } from '../helpers';

let defaultData = {};
const storageData = helpers.getDefaultStoredData('ui');
if (storageData) {
    defaultData = storageData;
}

export const uiStore = defineStore('ui', {
    state: () => ({
        ...{
            strings: window.app_config.strings,
            loading: false
        },
        ...storageData
    }),
    getters: {
        storeStrings(state) {
            return state.strings;
        },
        isLoading(state) {
            return state.loading;
        }
    },
    actions: {
        toggleLoading(status) {
            this.loading = status;
        }
    }
});

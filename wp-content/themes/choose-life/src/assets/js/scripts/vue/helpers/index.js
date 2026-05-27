import storage from '../storage';

const now = Date.now();
const expiresAt = now + (60 * 60 * 4000); // 4 hours

const storageItems = {
    user: {
        name: 'CL.USER',
        expiresAt
    },
    ui: {
        name: 'CL.UI',
        expiresAt
    },
    submission: {
        name: 'CL.SUBMISSION',
        expiresAt
    }
};

const customEventNames = {
    'donate': 'add_donation_amount',
};

const helpers = {
    maybeClearStorage() {
        for (const key in storageItems) {
            const storageName = storageItems[key].name;
            const expiresAt = storage.get(storageName, 'expiresAt');
            if (now > expiresAt) {
                storage.remove(storageName);
            }
        }
    },
    updateStorage(name, data) {
        let dataToStore = data;
        const key = storageItems[name].name;
        const storageData = storage.get(key, 'data');
        if (storageData) {
            for (const row in data) {
                storageData[row] = data[row];
            }
            dataToStore = storageData;
        }
        storage.set(key, {
            data: dataToStore,
            expiresAt
        });
    },
    getDefaultStoredData(name) {
        const key = storageItems[name].name;
        return storage.get(key, 'data');
    },
    clearStorage(name) {
        const key = storageItems[name].name;
        storage.remove(key);
    },
    getPriceWithCurrency(amount) {
        return new Intl.NumberFormat(document.documentElement.lang, {
            style: 'currency',
            currency: 'EUR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount);
    },
    /**
     * sends a request to the specified url from a form. this will change the window location.
     * @param {string} action the path to send the post request to
     * @param {object} params the parameters to add to the url
     * @param {string} [method=post] the method to use on the form
     * @param {string} [target=_top] the target to use on the form
     */
    postForm(action, params, method='post', target='_top') {

        // The rest of this code assumes you are not using a library.
        // It can be made less wordy if you use one.
        const form = document.createElement('form');
        form.method = method;
        form.action = action;
        form.target = target;

        for (const key in params) {
            if (params.hasOwnProperty(key)) {
                const hiddenField = document.createElement('input');
                hiddenField.type = 'hidden';
                hiddenField.name = key;
                hiddenField.value = params[key];

                form.appendChild(hiddenField);
            }
        }

        document.body.appendChild(form);
        form.submit();
    },
    strongPasswordCheck(value) {
        return /^(?=.*\d)(?=.*[~!@#$%^&*)(_+:[}="`-])(?=.*[a-z])(?=.*[A-Z])[~!@#$%^&*)(+:[}="`\w-]{8,}$/.test(value);
    },
    dynamicString(string = '', params = []) {
        if (!Array.isArray(params)) {
            params = [params];
        }
        if (string === '' || params.length === 0) {
            return '';
        }
        let output = string;
        for (let i = 0; i < params.length; i++) {
            output = output.replace('%s', `${params[i]}`);
        }
        return output;
    },
    sendCustomEvent(eventKeyName = '', data = {}) {
        if (!eventKeyName in customEventNames) {
            console.log(`Event name ${eventKeyName} not found`);
            return;
        }
        const customEvent = new CustomEvent(customEventNames[eventKeyName], {
            detail: data
        });
        document.dispatchEvent(customEvent);
    },
};

export {
    storageItems,
    helpers
}
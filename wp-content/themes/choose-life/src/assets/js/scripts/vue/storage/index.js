export default { 
    set(key, data) {
        localStorage.setItem(key, JSON.stringify(data));
    },
    get(key, item) {
        if ( localStorage.getItem(key) && item) {
            const data = JSON.parse(localStorage.getItem(key))
            return data[item];
        } else if (localStorage.getItem(key)) {
            return JSON.parse(localStorage.getItem(key));
        }
        return false;
    },
    remove(key = false) {
        if (key) {
            localStorage.removeItem(key);
        } else {
            localStorage.clear();
        }
    }
};

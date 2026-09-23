// Tracks, for the current checkout, whether the visitor goes through the
// "Donation" step (step 2): always when not logged in (an amount chosen on the
// donation page is just preselected there), and when logged in without an amount.
// Kept in sessionStorage so the steps don't change once the amount is picked
// or the page is reloaded mid-checkout.
const KEY = 'CL.CHECKOUT.AMOUNT_STEP';

const read = () => {
    try {
        return window.sessionStorage.getItem(KEY) === '1';
    } catch (e) {
        return false;
    }
};

const write = (value) => {
    try {
        if (value) {
            window.sessionStorage.setItem(KEY, '1');
        } else {
            window.sessionStorage.removeItem(KEY);
        }
    } catch (e) {
        // storage unavailable: the stepper falls back to the default steps
    }
};

export const checkoutFlow = {
    // called on the checkout's first navigation
    init(donationAmount, isLoggedIn) {
        if (!isLoggedIn || !(donationAmount > 0)) {
            write(true);
        }
    },
    enableAmountStep() {
        write(true);
    },
    hasAmountStep() {
        return read();
    },
    // checkout finished
    reset() {
        write(false);
    }
};

import axios from "axios";
import Cookies from "js-cookie"; // Assuming you're using js-cookie for managing cookies
import router from '../../router';   // Import the router instance (adjust the path as needed)

const state = {
    showSnackBar: {
        show: false,
        message: "",
        color: "",
    },
    updatePermissionSuccess: false,
    breadcrumb: [],
};

const getters = {};

const actions = {
    successSnackBar(context, message) {
        context.commit("SHOW_SNACKBAR", {
            show: true,
            color: "primary",
            message: message,
        });
    },

    errorSnackBar(context, message) {
        context.commit("SHOW_SNACKBAR", {
            show: true,
            color: "red",
            message: message,
        });

        // If the error message is 'Unauthenticated', trigger the logout process
        if (message === 'Unauthenticated.') {
            context.dispatch("logout");
        }
    },

    async logout(context) {
        try {
            const response = await axios.post('/api/logout');
            if (response.data.status == 200) {
                context.dispatch("login/setPermissions", []);
                localStorage.removeItem("user_login");
                Cookies.remove("access_token");
                localStorage.removeItem('access_token');
                router.push({name : 'adminLogin'});
            }
            
        } catch (error) {
            localStorage.removeItem("user_login");
            Cookies.remove("access_token");
            localStorage.removeItem('access_token');
            router.push({name : 'adminLogin'});
            const message = error.response ? error.response.data.message : "Something went wrong!";
            context.dispatch("globalState/errorSnackBar", message);
        }
    },

    updatePermission(context, data) {
        context.commit("UPDATE_PERMISSION_SUCCESS", true);
    },

    updateBread(context, list) {
        context.commit("UPDATE_BREADCRUMB", list);
    },
};

const mutations = {
    SHOW_SNACKBAR(state, snack) {
        state.showSnackBar.message = snack.message;
        state.showSnackBar.color = snack.color;
        state.showSnackBar.show = snack.show;
    },

    UPDATE_BREADCRUMB(state, list) {
        state.breadcrumb = list;
    },

    UPDATE_PERMISSION_SUCCESS(state, data) {
        state.updatePermissionSuccess = data;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

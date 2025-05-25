import { createApp } from "vue";
import axios from "axios";
import vuetify from "../plugins/vuetify.js";
import "../scss/style.scss";
import store from "./store.js";
import Cookies from "js-cookie";
import { abilitiesPlugin } from "@casl/vue";
import { ability } from "./ability.js";
import Login from "./frontend/auth/login.vue";
import Register from "./frontend/auth/register.vue";
import MyAccount from "./frontend/account/MyAccount.vue";
import ForgetPassword from "./frontend/auth/forgetPassword.vue";
import HomePage from "./frontend/home/HomePage.vue";
import AllProducts from "./frontend/products/AllProducts.vue";
import ProductDetail from "./frontend/products/ProductDetail.vue";
import labor from "./frontend/labors/labor.vue";
import laborDetail from "./frontend/labors/LaborDetail.vue";
import Cart from "./frontend/cart/Cart.vue";
import Checkout from "./frontend/checkout/Checkout.vue";
import ContactUs from "./frontend/support/ContactUs.vue";
import TermsConditions from "./frontend/support/TermsConditions.vue";
import PrivacyPolicy from "./frontend/support/PrivacyPolicy.vue";
import RefundPolicy from "./frontend/support/RefundPolicy.vue";

// Axios global configuration
const access_token =
    Cookies.get("access_token") || localStorage.getItem("access_token");
if (access_token) {
    axios.defaults.headers.common["Authorization"] = access_token;
}
axios.defaults.headers.common["Content-Type"] = "application/json";
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            Cookies.remove("access_token");
            localStorage.removeItem("access_token");
            localStorage.removeItem("user_login");
            store.dispatch("login/setPermissions", []);
            window.location.href = "/home";
        }
        return Promise.reject(error);
    }
);

const setupApp = (Component, mountElement) => {
    // Global Axios setup
    const appInstance = createApp(Component);
    appInstance.config.globalProperties.$axios = axios;
    appInstance.use(vuetify);
    appInstance.use(store);
    appInstance.use(abilitiesPlugin, ability, { useGlobalProperties: true });
    appInstance.mount(mountElement);
};

// login Page
setupApp(Login, "#login-page");

// register Page
setupApp(Register, "#register-page");

// forget-password Page
setupApp(ForgetPassword, "#forget-password-page");

// Home Page
setupApp(HomePage, "#home-page");

// Products Page
setupApp(AllProducts, "#product-page");

// Product Detail Page
setupApp(ProductDetail, "#product-detail");

// labor Page
setupApp(labor, "#labor-page");

// labor Detail Page
setupApp(laborDetail, "#labor-detail");

// Cart Page
setupApp(Cart, "#cart-page");

// Checkout Page
setupApp(Checkout, "#checkout-page");

// Contact Us Page
setupApp(ContactUs, "#contact-us-page");

// Terms & Conditions
setupApp(TermsConditions, "#terms-conditions-page");

// Privacy Policy
setupApp(PrivacyPolicy, "#privacy-policy-page");

// Refund Policy
setupApp(RefundPolicy, "#refund-policy-page");

// My Account
setupApp(MyAccount, "#account-page");
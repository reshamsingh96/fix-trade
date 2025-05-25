import Cookies from "js-cookie";
import { createRouter, createWebHistory } from "vue-router";
import Login from "./components/login/login.vue";
import Register from "./components/login/Register.vue";
import ForgotPassword from "./components/login/ForgotPassword.vue";
import User from "./components/user/index.vue";
import UserShow from "./components/user/Show.vue";
import Product from "./components/product/index.vue";
import ProductShow from "./components/product/Show.vue";
import ProductCreateEdit from "./components/product/ProductCreateEdit.vue";
import Labor from "./components/labor/index.vue";
import LaborCreateEdit from "./components/labor/LaborCreateEdit.vue";
import Order from "./components/order/index.vue";
import Category from "./components/lookup/category.vue";
import SubCategory from "./components/lookup/SubCategory.vue";
import Country from "./components/lookup/Country.vue";
import State from "./components/lookup/State.vue";
import City from "./components/lookup/City.vue";
import OrderReport from "./components/report/OrderList.vue";
import Profile from "./components/login/profile.vue";
import BlankLayout from "./layouts/BlankLayout.vue";
import FullLayout from "./layouts/FullLayout.vue";
import Dashboard from "./components/navbar/Dashboard.vue";

const routes = [
    {
        path: "/auth",
        component: BlankLayout,
        children: [
            {
                path: "/admin/login",
                name: "adminLogin",
                component: Login,
            },
            {
                path: "/admin/register",
                name: "Register",
                component: Register,
            },
            {
                path: "/admin/forgot-password",
                name: "forgot-password",
                component: ForgotPassword,
            },
        ],
    },

    {
        path: "/",
        name: "App",
        component: FullLayout,
        children: [
            {
                path: "/admin/dashboard",
                name: "dashboard",
                component: Dashboard,
            },
            {
                path: "/admin/profile/:uuid",
                name: "profile",
                component: Profile,
            },
            {
                path: "/admin/users",
                name: "users",
                component: User,
            },
            {
                path: "/admin/user/:uuid",
                name: "user.view",
                component: UserShow,
            },
            {
                path: "/admin/products",
                name: "products",
                component: Product,
            },
            {
                path: "/admin/product/create",
                name: "product.create",
                component: ProductCreateEdit,
            },
            {
                path: "/admin/product/edit/:id",
                name: "product.edit",
                component: ProductCreateEdit,
            },
            {
                path: "/admin/product/:id",
                name: "product.view",
                component: ProductShow,
            },
            {
                path: "/admin/labors",
                name: "labor",
                component: Labor,
            },
            {
                path: "/admin/labor/create",
                name: "labor.create",
                component: LaborCreateEdit,
            },
            {
                path: "/admin/labor/edit/:id",
                name: "labor.edit",
                component: LaborCreateEdit,
            },
            {
                path: "/admin/labor/view/:id",
                name: "labor.view",
                component: Labor,
            },
            {
                path: "/admin/orders",
                name: "order",
                component: Order,
            },
            {
                path: "/admin/reports",
                name: "report",
                component: OrderReport,
            },
            {
                path: "/admin/lookup/category",
                name: "lookup.category",
                component: Category,
            },
            {
                path: "/admin/lookup/sub-category",
                name: "lookup.sub_category",
                component: SubCategory,
            },
            {
                path: "/admin/lookup/country",
                name: "lookup.country",
                component: Country,
            },
            {
                path: "/admin/lookup/state",
                name: "lookup.state",
                component: State,
            },
            {
                path: "/admin/lookup/city",
                name: "lookup.city",
                component: City,
            },
        ],
    },

    {
        path: "/my-account",
        component: BlankLayout,
        children: [
            {
                path: "/admin/login",
                name: "adminLogin",
                component: Login,
            },
            {
                path: "/admin/register",
                name: "Register",
                component: Register,
            },
            {
                path: "/admin/forgot-password",
                name: "forgot-password",
                component: ForgotPassword,
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const token =
        Cookies.get("access_token") || localStorage.getItem("access_token");

    if (token) {
        if (to.name === "adminLogin" || to.name === "Register") {
            next({ path: "/admin/dashboard" });
        } else {
            next();
        }
    } else {
        if (to.name !== "adminLogin" && to.name !== "Register") {
            next({ path: "/admin/login" });
        } else {
            next();
        }
    }
});

export default router;

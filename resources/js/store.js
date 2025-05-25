// src/store/index.js
import { createStore } from 'vuex';
import globalState from "./vuex/modules/globalState";
import login from "./vuex/modules/login";
import emailStore from "./vuex/modules/emailStore";

export default createStore({
  state: {
    // Your state properties go here
  },
  mutations: {
    // Your mutations go here
  },
  actions: {
    // Your actions go here
  },
  modules: {
    globalState,
    login,
    emailStore,
  },
});

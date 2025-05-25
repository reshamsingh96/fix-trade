import { ability } from '../../ability.js';

const state = {
    user:null,
};

const getters = {};

const actions = {
    setPermissions (context, permissions) {
        const rules = permissions.map((p) => {
            let name = p.name.split("_");
            return {
                action: name[0],
                subject: name[1],
            };
        });
        // console.log(JSON.stringify(rules));
        ability.update(rules);
    },
    setUser({ commit }, user) {
        commit("SET_USER", user);
      },
};

const mutations = {
    SET_USER(state, user) {
        state.user = user;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};

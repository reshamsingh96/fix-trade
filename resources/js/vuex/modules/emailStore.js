import axios from "axios";
import Cookies from "js-cookie";

const state = {
    // emailList: [],
    // emailGet: {},
    mailData:{},
    email:'' ,
};

const getters = {};

const actions = {
    send_email(context, data) {
        var api_url = "/api/send_email";
        axios.post(api_url, data).then(response => {
                context.commit(
                    "globalState/SHOW_SNACKBAR",
                    {
                        message: response.data.message,
                        show: true,
                        color: "primary"
                    },
                    { root: true }
                );
            })

            .catch(error => {
                context.commit(
                    "globalState/SHOW_SNACKBAR",
                    {
                        message: "Something Wrong!",
                        show: true,
                        color: "Red"
                    },
                    { root: true }
                );
            });
    },
    // create_update_email(context, data) {
    //     // var api_url = "/api/create_update_email";
    //     // if(Cookies.get('group_id') == Vue.prototype.$constant.ADMIN_GROUP){
    //     var api_url = "/api/admin/create_update_email";
    //     // }

    //     axios
    //         .post(api_url, data)
    //         .then(response => {
    //             context.commit(
    //                 "globalState/SHOW_SNACKBAR",
    //                 {
    //                     message: response.data.message,
    //                     show: true,
    //                     color: "primary"
    //                 },
    //                 { root: true }
    //             );
    //         })

    //         .catch(error => {
    //             context.commit(
    //                 "globalState/SHOW_SNACKBAR",
    //                 {
    //                     message: "Something Wrong!",
    //                     show: true,
    //                     color: "Red"
    //                 },
    //                 { root: true }
    //             );
    //         });
    // },
    mailPreview(context, data) {
        var api_url = "/api/mail_preview";
        axios.post(api_url, data).then(response => {
                context.commit("EMAIL_DATA",response.data);
            }).catch(error => {
                context.commit(
                    "globalState/SHOW_SNACKBAR",
                    {
                        message: "Something Wrong!",
                        show: true,
                        color: "Red"
                    },
                    { root: true }
                );
            });
    }
};

const mutations = {
    MAIL_PREVIEW(state,data){
     state.email=data;
    },
    EMAIL_DATA(state,data){
        state.mailData=data;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};

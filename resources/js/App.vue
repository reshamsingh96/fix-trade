<template>
  <router-view />
</template>
<script>
import Cookies from "js-cookie";
export default {
  // async mounted() {
  //   const access_token = Cookies.get("access_token") || localStorage.getItem("access_token");
  //   // const user_login = JSON.parse(localStorage.getItem("user_login"));
  //   // if (access_token && user_login) {
  //     if (access_token) {
  //     axios.defaults.headers.common["Authorization"] = access_token;
  //     // this.$store.dispatch("login/setPermissions", user_login.permissions);
  //   }
  // },
};
</script>

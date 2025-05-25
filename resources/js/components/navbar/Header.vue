<template>
  <v-app-bar-title>Anaaj Culture</v-app-bar-title>
  <v-spacer></v-spacer>
  <!-- <v-btn icon> <v-icon>mdi-magnify</v-icon> </v-btn> <v-btn icon> <v-icon>mdi-heart</v-icon> </v-btn> -->

  <!-- Profile Menu -->
  <v-menu>
    <template v-slot:activator="{ props }" >
      <v-btn icon variant="text" v-bind="props">
        <v-avatar color="blue" size="36">
          <v-img v-if="user_info && user_info.image_url" :src="user_info.image_url"></v-img>
          <span v-else class="white--text">{{
            user_info ? nameInitial(user_info.name) : ""
          }}</span>
        </v-avatar>
      </v-btn>
    </template>

    <v-list>
      <v-list-item
        @click="goToProfile"
        :class="{ 'v-list-item--active': isActive('profile') }"
      >
        <v-list-item-title>Profile</v-list-item-title>
      </v-list-item>
      <v-list-item v-if="user && user.account_type == 'Super Admin'"
        @click="goToWeb"
        :class="{ 'v-list-item--active': isActive('profile') }"
      >
        <v-list-item-title>View Web</v-list-item-title>
      </v-list-item>

      <v-list-item @click="logout">
        <v-list-item-title>Logout</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-menu>
</template>

<script>
import { mapState } from "vuex";
import Cookies from 'js-cookie';

export default {
  name: "Header",
  data() {
    return {
      user_info: null,
    };
  },

  computed: {
    ...mapState({
      user: (state) => state.login.user,
    }),
  },
  watch: {
    user(val) {
      if (!val) {
        this.user_info = this.checkLocalStorage();
      } else {
        this.user_info = val ?? localStorage.getItem("user_login") ?? null;
        // console.log(this.user_info);
      }
    },
  },

  mounted() {
    if (this.user == null) {
      this.checkLocalStorage();
    } else {
      this.user_info = this.user;
    }
  },

  methods: {
    checkLocalStorage() {
      let info = localStorage.getItem("user_login") ?? null;

      if (info) {
        this.$store.dispatch("login/setUser", JSON.parse(info)); // Setting user in Vuex store
      }
      return info ? JSON.parse(info) : null;
    },

    nameInitial(name) {
      const nameParts = name.trim().split(" ");
      const firstInitial = nameParts[0] ? nameParts[0][0].toUpperCase() : "";
      const secondInitial = nameParts[1] ? nameParts[1][0].toUpperCase() : "";
      return `${firstInitial}${secondInitial}`;
    },

    isActive(route) {
      return this.$route.name === route;
    },

    goToProfile() {
      this.$router.push({
        name: "profile",
        params: { uuid: this.user_info.uuid },
      });
    },

    goToWeb(){
      window.location.href = '/home'
    },

    async logout() {
      try {
        const response = await axios.post("/api/logout");
        if (response.data.status == 200) {
          this.$store.dispatch("login/setPermissions", []);
          localStorage.removeItem("user_login");
          Cookies.remove("access_token");
          localStorage.removeItem("access_token");
          this.$store.dispatch("login/setUser", null);
          this.$router.push({name : 'adminLogin'});
        }
      } catch (error) {
        localStorage.removeItem("user_login");
        Cookies.remove("access_token");
        localStorage.removeItem("access_token");
        this.$store.dispatch("login/setUser", null);
        this.$router.push({name : 'adminLogin'});
        let message = error.response ? error.response.data.message : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
    },

  },
};
</script>
<style scoped>
.v-list-item--active {
  background-color: #e0e0e0;
}
</style>
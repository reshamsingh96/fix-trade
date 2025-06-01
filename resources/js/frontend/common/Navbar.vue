<template>
  <header class="my-header">
    <v-container class="my-container">
      <v-row>
        <v-col cols="8" sm="6" md="3">
          <div class="logo-container">
            <a href="/">
              <img :src="'/images/logo.jpg'" alt="Logo" />
            </a>
            <h3 class="text-h3">Home Service</h3>
          </div>
        </v-col>

        <v-col
          v-if="$vuetify.display.mdAndUp"
          align-self="center"
          cols="6"
          sm="6"
          md="6"
        >
          <ul class="navbar-links">
            <li>
              <a class="navbar-link" href="/">Home</a>
            </li>
            <li>
              <a class="navbar-link" href="/#category">Category</a>
            </li>
            <li>
              <a class="navbar-link" href="/products">Products</a>
            </li>
            <li>
              <a class="navbar-link" href="/labors">Labour</a>
            </li>
            <li>
              <a class="navbar-link" href="/#team">Team</a>
            </li>
            <li>
              <a class="navbar-link" href="/#about">About</a>
            </li>
          </ul>
        </v-col>

        <v-col align-self="center" class="text-end" cols="4" sm="6" md="3">
          <v-btn v-if="$vuetify.display.smAndDown" icon variant="flat">
            <MenuIcon />
          </v-btn>
          <v-menu v-if="user_info">
            <template v-slot:activator="{ props }">
              <v-btn icon variant="text" v-bind="props">
                <v-avatar color="primary" size="45" variant="tonal">
                  <v-img
                    v-if="user_info && user_info.image_url"
                    :src="user_info.image_url"
                  ></v-img>
                  <UserIcon v-else stroke-width="1.5" />
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
                @click="goToAdminPanel"
                :class="{ 'v-list-item--active': isActive('profile') }"
              >
                <v-list-item-title>Admin Panel</v-list-item-title>
              </v-list-item>

              <v-list-item @click="logout">
                <v-list-item-title>Logout</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>

          <v-btn v-if="!user_info && $vuetify.display.mdAndUp" variant="flat" href="/login" color="primary"
            >Login</v-btn
          >
        </v-col>

        <!-- Snackbar dynamically bound to Vuex state -->
        <v-snackbar
          v-model="snackbar.show"
          :color="snackbar.color"
          :timeout="2000"
          top
        >
          {{ snackbar.message }}

          <template v-slot:actions>
            <v-btn color="white" variant="text" @click="snackbar.show = false">
              Close
            </v-btn>
          </template>
        </v-snackbar>
      </v-row>
    </v-container>
  </header>
</template>

<script>
import { mapState } from "vuex";
import Icon from "../../common/Icon.vue";
import {
  SearchIcon,
  UserIcon,
  ShoppingCartIcon,
  HeartIcon,
  MenuIcon,
} from "vue-tabler-icons";

export default {
  components: {
    Icon,
    SearchIcon,
    UserIcon,
    ShoppingCartIcon,
    HeartIcon,
    MenuIcon,
  },

  data() {
    return {
      user_info: null,
    };
  },

  computed: {
    ...mapState({
      user: (state) => state.login.user,
      snackbar: (state) => state.globalState.showSnackBar,
    }),
  },
  watch: {
    user(val) {
      if (!val) {
        this.user_info = this.checkLocalStorage();
      } else {
        this.user_info = val ?? localStorage.getItem("user_login") ?? null;
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
      // return this.$route.name === route;
    },

    goToProfile() {
      window.location.href = "/my-account";

      // this.$router.push({
      //   name: "profile",
      //   params: { uuid: this.user_info.uuid },
      // });
    },

    goToAdminPanel() {
      window.location.href = "/admin/dashboard";
      // this.$router.push({ name: "profile", params: { uuid: this.user_info.uuid }, });
    },

    async logout() {
      try {
        const response = await axios.post("/api/logout");
        if (response.data.status == 200) {
          this.$store.dispatch("login/setPermissions", []);
          localStorage.removeItem("user_login");
          Cookies.remove("access_token");
          this.$store.dispatch("login/setUser", null);
          window.location.href = "/";
        }
      } catch (error) {
        localStorage.removeItem("user_login");
        Cookies.remove("access_token");
        localStorage.removeItem("access_token");
        this.$store.dispatch("login/setUser", null);
        // this.$router.push({ name: 'adminLogin' });
        window.location.href = "/";
        let message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
    },
  },
};
</script>

<style></style>
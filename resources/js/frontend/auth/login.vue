<template>
  <v-app>
    <Navbar />
    <v-main>
      <v-container class="fill-height align-center justify-center">
        <v-card class="auth-card" elevation="10">
          <v-img class="mx-auto" max-width="100" src="/images/logo.png"></v-img>

          <div class="text-center mb-5 mt-3">
            <h3 class="text-h3">Welcome to Login</h3>
          </div>

          <v-label
            class="text-subtitle-1 font-weight-semibold pb-2 text-lightText"
          >
            Email Or Phone
          </v-label>
          <v-text-field
            placeholder="Enter Email Or Phone"
            v-model="user_name"
          ></v-text-field>

          <div class="d-flex justify-space-between pb-2">
            <v-label
              class="text-subtitle-1 font-weight-semibold text-lightText"
            >
              Password
            </v-label>
            <a class="text-primary" href="/forgot-password"
              >Forgot Password?
            </a>
          </div>

          <v-text-field
            :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
            :type="visible ? 'text' : 'password'"
            placeholder="Enter your password"
            prepend-inner-icon="mdi-lock-outline"
            @click:append-inner="visible = !visible"
            v-model="password"
          ></v-text-field>

          <v-btn
            :loading="loading_login"
            :disabled="loading_login"
            variant="flat"
            @click="loginUser()"
            color="primary"
            block
            size="large"
            >LOGIN</v-btn
          >

          <h6 class="text-h6 mt-6 font-weight-medium">
            New here?<a href="/register"
              ><v-btn variant="text" color="primary" class="pl-1"
                >Create an account</v-btn
              ></a
            >
          </h6>
        </v-card>
      </v-container>
      <br /><br />
      <br /><br /><br />
    </v-main>
    <Footer />
  </v-app>
</template>
<script>
import Cookies from "js-cookie";
import axios from "axios";
import Navbar from "../common/Navbar.vue";
import Footer from "../common/Footer.vue";

export default {
  components: {
    Navbar,
    Footer,
  },

  data: () => ({
    visible: false,
    loading_login: false,
    user_name: "",
    password: "",
  }),

  methods: {
    loginUser() {
      this.loading_login = true;
      axios
        .post("/api/login-user", {
          user_name: this.user_name,
          password: this.password,
        })
        .then((response) => {
          const info = response.data.data;
          const token = "Bearer " + info.access_token;

          // Store token and user information
          localStorage.setItem("access_token", token);
          Cookies.set("access_token", token);
          localStorage.setItem("user_login", JSON.stringify(info.user));

          // Set token as default Authorization header
          axios.defaults.headers.common["Authorization"] = token;

          // Dispatch permissions to Vuex store
          let permissions = info.permissions;
          this.$store.dispatch("login/setPermissions", permissions);

          // Redirect to dashboard
          window.location.href = "/home";
          this.loading_login = false;
        })
        .catch((error) => {
          this.loading_login = false;
          let message = error.response
            ? error.response.data.message
            : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
          if (
            error.response &&
            error.response.data.message === "CSRF token mismatch."
          ) {
            this.refresh_dialog = true;
          }
        });
    },
  },
};
</script>
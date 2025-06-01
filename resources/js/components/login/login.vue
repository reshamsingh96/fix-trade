 <template>
  <div class="auth-page">
    <v-container class="fill-height align-center justify-center">
      <v-card class="auth-card" elevation="10">
        <v-img class="mx-auto" max-width="100" src="/images/logo.jpg"></v-img>

        <div class="text-center mb-5 mt-3">
          <h3 class="text-h3">Welcome to Admin Panel</h3>
        </div>

        <v-label class="text-subtitle-1 font-weight-semibold pb-2 text-lightText">
          Email Or Phone
        </v-label>
        <v-text-field
          placeholder="Enter Email or Phone"
          v-model="user_name"
        />

        <div class="d-flex justify-space-between pb-2">
          <v-label class="text-subtitle-1 font-weight-semibold text-lightText">
            Password
          </v-label>
          <router-link class="text-primary" to="/forgot-password">
            Forgot Password?
          </router-link>
        </div>

        <v-text-field
          :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
          :type="visible ? 'text' : 'password'"
          placeholder="Enter your password"
          prepend-inner-icon="mdi-lock-outline"
          @click:append-inner="togglePasswordVisibility"
          v-model="password"
        />

        <v-btn
          :loading="loading_login"
          :disabled="loading_login"
          variant="flat"
          color="primary"
          block
          size="large"
          @click="loginUser"
        >
          LOGIN
        </v-btn>

        <h6 class="text-h6 mt-6 font-weight-medium">
          New here?
          <v-btn
            variant="text"
            color="primary"
            class="pl-1"
            @click="goToRegister"
          >
            Create an account
          </v-btn>
        </h6>
      </v-card>
    </v-container>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";
import Cookies from "js-cookie";
import axios from "axios";

// refs
const user_name = ref("");
const password = ref("");
const visible = ref(false);
const loading_login = ref(false);

// router & store
const router = useRouter();
const store = useStore();

// toggle password field visibility
const togglePasswordVisibility = () => {
  visible.value = !visible.value;
};

// login function
const loginUser = async () => {
  loading_login.value = true;
  try {
    const response = await axios.post("/api/login-user", {
      user_name: user_name.value,
      password: password.value,
    });

    const info = response.data.data;
    const token = "Bearer " + info.access_token;

    // Save token and user
    localStorage.setItem("access_token", token);
    Cookies.set("access_token", token);
    localStorage.setItem("user_login", JSON.stringify(info.user));
    axios.defaults.headers.common["Authorization"] = token;

    // Set permissions
    await store.dispatch("login/setPermissions", info.permissions);

    // Redirect
    router.replace({ name: "dashboard" });
  } catch (error) {
    const message = error.response?.data?.message || "Something went wrong!";
    store.dispatch("globalState/errorSnackBar", message);
  } finally {
    loading_login.value = false;
  }
};

// go to register page
const goToRegister = () => {
  router.push("/admin/register");
};
</script>

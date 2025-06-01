<template>
  <v-app>
    <Navbar />
    <v-main>
      <v-container class="fill-height align-center justify-center">
        <v-card class="auth-card register-auth-card" elevation="10">
          <v-img class="mx-auto" max-width="100" src="/images/logo.jpg"></v-img>

          <div class="text-center mb-5 mt-3">
            <h3 class="text-h3">Create New Account</h3>
          </div>

          <div class="d-flex align-center mt-3 mb-4">
            <v-img :src="image_url || '/images/profile_image.png'" max-width="100" rounded="lg">
            </v-img>

            <v-btn color="primary" variant="outlined" @click="chooseImage" class="ml-3">
              Choose Photo
            </v-btn>
            <v-btn class="ml-2" variant="text" icon size="small" color="error" @click="removeImage"
              v-if="form.profile_image">
              <TrashIcon />
            </v-btn>

            <input ref="imageInput" type="file" accept="image/*" class="d-none" @change="onImageSelected" />
          </div>

          <v-row>
            <v-col class="pb-0" cols="12" sm="6" md="6">
              <v-label class="text-subtitle-1 font-weight-semibold pb-2">
                Name
              </v-label>
              <v-text-field placeholder="Enter full name" v-model="form.name"
                :error-messages="v$.form.name.$errors.map((e) => e.$message)" @blur="v$.form.name.$touch()"
                :rules="[(v) => !!v || 'Name is required']">
              </v-text-field>
            </v-col>

            <v-col class="pb-0" cols="12" sm="6" md="6">
              <v-label class="text-subtitle-1 font-weight-semibold pb-2">
                Email
              </v-label>
              <v-text-field placeholder="Enter email" v-model="form.email"
                :error-messages="v$.form.email.$errors.map((e) => e.$message)" @blur="v$.form.email.$touch()"
                :rules="[(v) => !!v || 'Email is required']">
              </v-text-field>
            </v-col>

            <v-col class="pb-0 pt-1" cols="12" sm="6" md="6">
              <v-label class="text-subtitle-1 font-weight-semibold pb-2">
                Phone
              </v-label>
              <v-text-field placeholder="Enter phone" v-model="form.phone"
                :error-messages="v$.form.phone.$errors.map((e) => e.$message)" @blur="v$.form.phone.$touch()">
              </v-text-field>
            </v-col>

            <v-col class="pb-0 pt-1" cols="12" sm="6" md="6">
              <v-label class="text-subtitle-1 font-weight-semibold pb-2">
                Gender
              </v-label>
              <v-autocomplete v-model="form.gender" :items="genders" placeholder="Select gender"
                :error-messages="v$.form.gender.$errors.map((e) => e.$message)" @blur="v$.form.gender.$touch()">
              </v-autocomplete>
            </v-col>
            <v-col v-if="type === 'create_user'" class="pb-0 pt-1" cols="12" sm="6" md="6">
              <v-label class="text-subtitle-1 font-weight-semibold pb-2">
                Account Type
              </v-label>
              <v-autocomplete v-model="form.account_type" :items="account_type_list" placeholder="Select Account Type"
                :error-messages="v$.form.account_type.$errors.map((e) => e.$message)
                  " @blur="v$.form.account_type.$touch()" readonly>
              </v-autocomplete>
            </v-col>
            <v-col class="pb-0 pt-1" cols="12" sm="6" md="6">
              <v-label class="text-subtitle-1 font-weight-semibold pb-2">
                Is Type
              </v-label>
              <v-select label="Select Is Type" :items="is_type_list" v-model="form.is_type" variant="outlined"
                density="compact" :error-messages="v$.form.is_type.$errors.map((e) => e.$message)"
                @blur="v$.form.is_type.$touch()"></v-select>
            </v-col>
            <v-col class="pb-0 pt-1" cols="12" sm="6" md="6">
              <v-label class="text-subtitle-1 font-weight-semibold pb-2">
                Password
              </v-label>
              <v-text-field placeholder="Enter password" :type="passwordVisibility ? 'text' : 'password'"
                v-model="form.password" :error-messages="v$.form.password.$errors.map((e) => e.$message)
                  " @blur="v$.form.password.$touch()" :append-inner-icon="passwordVisibility ? 'mdi-eye-off' : 'mdi-eye'
                    " @click:append-inner="passwordVisibility = !passwordVisibility">
              </v-text-field>
            </v-col>
            <v-col class="pb-0 pt-1" cols="12" sm="6" md="6">
              <v-label class="text-subtitle-1 font-weight-semibold pb-2">
                Confirm Password
              </v-label>
              <v-text-field placeholder="Enter password" :append-inner-icon="confirmPasswordVisibility ? 'mdi-eye-off' : 'mdi-eye'
                " :type="confirmPasswordVisibility ? 'text' : 'password'" v-model="form.confirm_password"
                @click:append-inner="
                  confirmPasswordVisibility = !confirmPasswordVisibility
                  " :error-messages="v$.form.confirm_password.$errors.map((e) => e.$message)
                    " @blur="v$.form.confirm_password.$touch()">
              </v-text-field>
            </v-col>


            <v-col class="pb-0 pt-1" cols="12" sm="6" md="6">
              <v-label class="text-subtitle-1 font-weight-semibold pb-2">
                Pin code
              </v-label>
              <v-text-field placeholder="Enter pin code" v-model="form.pin_code" :error-messages="v$.form.pin_code.$errors.map((e) => e.$message)
                " @blur="v$.form.pin_code.$touch()">
              </v-text-field>
            </v-col>

            <v-col class="pb-0 pt-1" cols="12" sm="6" md="6">
              <v-label class="text-subtitle-1 font-weight-semibold pb-2">
                House Number
              </v-label>
              <v-text-field placeholder="Enter home no." variant="outlined" v-model="form.home_no">
              </v-text-field>
            </v-col>

            <v-col class="pb-0 pt-1" cols="12" sm="12" md="12">
              <v-label class="text-subtitle-1 font-weight-semibold pb-2">
                Full Address
              </v-label>
              <v-textarea rows="2" placeholder="Enter address" v-model="form.full_address">
              </v-textarea>
            </v-col>
            <!-- 
            <v-col class="pb-0 pt-1" cols="12" sm="6" md="6">
              <v-label class="text-subtitle-1 font-weight-semibold pb-2">
                State
              </v-label>
              <v-autocomplete
                v-model="form.state_id"
                :items="states"
                placeholder="Select state"
                item-title="name"
                item-value="id"
                :error-messages="
                  v$.form.state_id.$errors.map((e) => e.$message)
                "
                @blur="v$.form.state_id.$touch()"
              >
              </v-autocomplete>
            </v-col> -->
            <!-- <v-col class="pb-0 pt-1" cols="12" sm="6" md="6">
              <v-label class="text-subtitle-1 font-weight-semibold pb-2">
                City
              </v-label>
              <v-autocomplete
                v-model="form.city_id"
                :items="cities"
                placeholder="Select city"
                item-title="name"
                item-value="id"
                :error-messages="v$.form.city_id.$errors.map((e) => e.$message)"
                @blur="v$.form.city_id.$touch()"
              >
              </v-autocomplete>
            </v-col> -->

            <!-- <v-col cols="12" sm="12" md="12">
              <v-label class="text-subtitle-1 font-weight-semibold pb-2">
                Country
              </v-label>
              <v-autocomplete
                v-model="form.country_id"
                :items="countries"
                readonly
                item-title="name"
                item-value="id"
                :error-messages="
                  v$.form.country_id.$errors.map((e) => e.$message)
                "
                @blur="v$.form.country_id.$touch()"
              >
              </v-autocomplete>
            </v-col> -->
          </v-row>

          <v-btn color="primary" variant="flat" @click="submitForm()" size="large" block class="mt-2">
            Create User
          </v-btn>

          <div class="text-center mt-4">
            <a href="/login"><v-btn color="gray" variant="text"> Back to Login </v-btn>
            </a>
          </div>
        </v-card>
      </v-container>
      <br /><br />
      <br /><br /><br />
    </v-main>
    <Footer />
  </v-app>
</template>
<script setup>
import { ref, reactive, computed, watch, onMounted } from "vue";
import { useVuelidate } from "@vuelidate/core";
import { required, email, minLength, sameAs } from "@vuelidate/validators";
import axios from "axios";
import Cookies from "js-cookie";

// Components
import Navbar from "../common/Navbar.vue";
import Footer from "../common/Footer.vue";
import { TrashIcon } from "vue-tabler-icons";

// Reactive State
const passwordVisibility = ref(false);
const confirmPasswordVisibility = ref(false);
const imageUrl = ref(null);

const genders = ["Male", "Female", "Other"];
const accountTypeList = ["User", "Vendor", "Labor"];
const isTypeList = ["Buyer", "Seller", "Labor"];

const countries = ref([]);
const states = ref([]);
const cities = ref([]);

const form = reactive({
  name: "",
  email: "",
  gender: "",
  account_type: "User",
  phone_code: "91",
  phone: "",
  secondary_number: "",
  status: "Active",
  is_buyer: true,
  is_seller: false,
  is_labor: false,
  password: "",
  confirm_password: "",
  profile_image: "",
  country_id: "",
  state_id: "",
  city_id: "",
  pin_code: "",
  home_no: "",
  full_address: "",
  is_type: "Buyer",
});

// Vuelidate Rules
const rules = {
  form: {
    name: { required },
    email: { required, email },
    gender: { required },
    account_type: { required },
    phone: { required, minLength: minLength(10) },
    password: { required, minLength: minLength(6) },
    confirm_password: {
      required,
      sameAsPassword: sameAs(() => form.password),
    },
    pin_code: { required },
    is_type: { required },
  },
};

const v$ = useVuelidate(rules, { form });

// Functions
const togglePasswordVisibility = () => {
  passwordVisibility.value = !passwordVisibility.value;
};

const toggleConfirmPasswordVisibility = () => {
  confirmPasswordVisibility.value = !confirmPasswordVisibility.value;
};

const chooseImage = () => {
  document.getElementById("imageInput").click();
};

const onImageSelected = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.profile_image = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      imageUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const removeImage = () => {
  imageUrl.value = null;
  form.profile_image = null;
};

const fetchCountries = async () => {
  try {
    const response = await axios.post("/api/dropdown-country-list");
    countries.value = response.data.data;
    const india = countries.value.find(
      (country) => country.name.toLowerCase() === "india"
    );
    if (india) form.country_id = india.id;
  } catch (error) {
    const message = error.response?.data.message || "Something went wrong!";
    useError(message);
  }
};

const fetchStates = async () => {
  if (!form.country_id) return;
  try {
    const response = await axios.post("/api/dropdown-state-list", {
      country_id: form.country_id,
    });
    states.value = response.data.data;
  } catch (error) {
    const message = error.response?.data.message || "Something went wrong!";
    useError(message);
  }
};

const fetchCities = async () => {
  if (!form.state_id) return;
  try {
    const response = await axios.post("/api/dropdown-city-list", {
      country_id: form.country_id,
      state_id: form.state_id,
    });
    cities.value = response.data.data;
  } catch (error) {
    const message = error.response?.data.message || "Something went wrong!";
    useError(message);
  }
};

const submitForm = async () => {
  // Check postal code
  try {
    const address = await axios.post("/api/check-post-code", {
      post_code: form.pin_code,
    });
    form.state = address.data.data.State;
    form.city = address.data.data.District;
    useSuccess("Postal code is valid");
  } catch (error) {
    const message = error.response?.data.message || "Invalid postal code!";
    useError(message);
    form.pin_code = "";
    return;
  }

  // Set flags
  form.is_buyer = form.is_type === "Buyer";
  form.is_seller = form.is_type === "Seller";
  form.is_labor = form.is_type === "Labor";

  v$.value.$touch();
  if (v$.value.$invalid) {
    useError("Form is invalid!");
    return;
  }

  try {
    const formData = new FormData();
    Object.entries(form).forEach(([key, value]) => {
      formData.append(key, value);
    });

    const response = await axios.post("/api/register-user", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    useSuccess(response.data.message);
    window.location.href = "/login";
  } catch (error) {
    const message = error.response?.data.message || "Something went wrong!";
    useError(message);
  }
};

const loginUser = async () => {
  try {
    const response = await axios.post("/api/login-user", {
      user_name: form.email,
      password: form.password,
    });

    const info = response.data.data;
    const token = "Bearer " + info.access_token;

    localStorage.setItem("access_token", token);
    Cookies.set("access_token", token);
    localStorage.setItem("user_login", JSON.stringify(info.user));
    axios.defaults.headers.common["Authorization"] = token;

    const permissions = info.permissions;
    window.location.href = "/home";
  } catch (error) {
    const message = error.response?.data.message || "Something went wrong!";
    useError(message);
  }
};

// Snackbar Helpers (replace with actual Vuex dispatch if needed)
const useSuccess = (msg) => {
  // Replace with store dispatch
  console.log("✅ Success:", msg);
};

const useError = (msg) => {
  // Replace with store dispatch
  console.error("❌ Error:", msg);
};

// Lifecycle
onMounted(fetchCountries);

watch(() => form.country_id, (newVal) => {
  if (newVal) fetchStates();
});

watch(() => form.state_id, (newVal) => {
  if (newVal) fetchCities();
});
</script>
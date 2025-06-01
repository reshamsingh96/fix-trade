<template>
  <div class="auth-page">
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
              Type
            </v-label>
            <v-select label="Select Type" :items="account_type_list" v-model="form.account_type" variant="outlined"
              density="compact" :error-messages="v$.form.account_type.$errors.map((e) => e.$message)"
              @blur="v$.form.account_type.$touch()"></v-select>

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
              Gender
            </v-label>
            <v-autocomplete v-model="form.gender" :items="genders" placeholder="Select gender"
              :error-messages="v$.form.gender.$errors.map((e) => e.$message)" @blur="v$.form.gender.$touch()">
            </v-autocomplete>
          </v-col>
          <v-col class="pb-0 pt-1" cols="12" sm="6" md="6">
            <v-label class="text-subtitle-1 font-weight-semibold pb-2">
              Password
            </v-label>
            <v-text-field placeholder="Enter password" :type="passwordVisibility ? 'text' : 'password'"
              v-model="form.password" :error-messages="v$.form.password.$errors.map((e) => e.$message)"
              @blur="v$.form.password.$touch()" :append-inner-icon="passwordVisibility ? 'mdi-eye-off' : 'mdi-eye'
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
              Pin Code
            </v-label>
            <v-text-field placeholder="Enter pin code" v-model="form.pin_code"
              :error-messages="v$.form.pin_code.$errors.map((e) => e.$message)" @blur="v$.form.pin_code.$touch()">
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
              House Number
            </v-label>
            <v-text-field
              placeholder="Enter home no."
              variant="outlined"
              v-model="form.home_no"
              
            >
            </v-text-field>
          </v-col>

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
              :error-messages="v$.form.state_id.$errors.map((e) => e.$message)"
              @blur="v$.form.state_id.$touch()"
            >
            </v-autocomplete>
          </v-col>
          <v-col class="pb-0 pt-1" cols="12" sm="6" md="6">
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
          </v-col>

          <v-col cols="12" sm="12" md="12">
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
          <v-btn color="gray" variant="text" @click="$router.push('/login')">
            Back to Login
          </v-btn>
        </div>
      </v-card>
    </v-container>
  </div>
</template>

<script>
import useVuelidate from "@vuelidate/core";
import { required, email, minLength, sameAs } from "@vuelidate/validators";
import axios from "axios";
import { TrashIcon } from "vue-tabler-icons";

export default {
  setup() {
    const v$ = useVuelidate();
    return { v$ };
  },
  components: {
    TrashIcon,
  },
  data() {
    return {
      passwordVisibility: false,
      confirmPasswordVisibility: false,
      image_url: null,
      account_type_list: ['User', 'Vendor', 'Labor'],
      is_type_list: ['Buyer', 'Seller', 'Labor'],
      form: {
        name: "",
        email: "",
        gender: "",
        account_type: "User",
        phone_code: "91",
        phone: "",
        secondary_number: "",
        status: "Active",
        is_type: 'Buyer',
        is_buyer: true,
        is_seller: false,
        is_labor: false,
        password: "",
        confirm_password: "",
        profile_image: "",

        // country_id: "",
        state: "",
        city: "",
        pin_code: "",
        home_no: "",
        full_address: "",
      },

      genders: ["Male", "Female", "Other"],
      countries: [],
      states: [],
      cities: [],
    };
  },

  validations() {
    return {
      form: {
        name: { required },
        email: { required, email },
        gender: { required },
        account_type: { required },
        phone: { required, minLength: minLength(10) },
        password: { required, minLength: minLength(6) },
        confirm_password: {
          required,
          sameAsPassword: sameAs(this.form.password),
        },
        pin_code: { required },
        account_type: { required },
        is_type: { required },
        // city_id: { required },
      },
    };
  },

  methods: {
    togglePasswordVisibility() {
      this.passwordVisibility = !this.passwordVisibility;
    },

    toggleConfirmPasswordVisibility() {
      this.confirmPasswordVisibility = !this.confirmPasswordVisibility;
    },

    chooseImage() {
      this.$refs.imageInput.click();
    },

    onImageSelected(event) {
      const file = event.target.files[0];
      if (file) {
        this.form.profile_image = file;

        const reader = new FileReader();
        reader.onload = (e) => {
          this.image_url = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },

    removeImage() {
      this.image_url = null;
      this.form.profile_image = null; // Or set it back to a default placeholder image
    },

    async postCodeAddress() {
      await axios.post("/api/check-post-code", { post_code: this.form.pin_code }).then((response) => {
        this.form.state = response.data.data.State;
        this.form.city = response.data.data.District;
        this.$store.dispatch("globalState/successSnackBar", response.data.message);
      }).catch((error) => {
        let message = error.response ? error.response.data.message : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      });
    },

    async submitForm() {
      this.v$.$touch();
      if (this.v$.$invalid) {
        this.$store.dispatch("globalState/errorSnackBar", "Form is invalid!");
        return;
      }

      // Check postal code before proceeding
      try {
        const address = await axios.post("/api/check-post-code", { post_code: this.form.pin_code });
        this.form.state = address.data.data.State;
        this.form.city = address.data.data.District;
        this.$store.dispatch("globalState/successSnackBar", "Postal code is valid");
      } catch (error) {
        let message = error.response ? error.response.data.message : "Invalid postal code!";
        this.$store.dispatch("globalState/errorSnackBar", message);
        this.form.pin_code = '';
        return;  // Stop the flow if postal code is invalid
      }

      this.form.is_buyer = this.form.is_type == 'Buyer' ? true : false;
      this.form.is_seller = this.form.is_type == 'Seller' ? true : false;
      this.form.is_labor = this.form.is_type == 'Labor' ? true : false;
      try {
        const formData = new FormData();
        for (const key in this.form) {
          formData.append(key, this.form[key] instanceof File ? this.form[key] : this.form[key]);
        }

        const response = await axios.post("/api/register-user", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        });

        this.$store.dispatch("globalState/successSnackBar", response.data.message);
        this.$router.push({ name: 'adminLogin' });

      } catch (error) {
        let message = error.response ? error.response.data.message : "Something went wrong during registration!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
    },

    // async fetchCountries() {
    //   try {
    //     const response = await axios.post("/api/dropdown-country-list");
    //     this.countries = response.data.data;
    //     const india = this.countries.find(
    //       (country) => country.name.toLowerCase() === "india"
    //     );

    //     if (india) {
    //       this.form.country_id = india.id;
    //     }
    //   } catch (error) {
    //     let message = error.response
    //       ? error.response.data.message
    //       : "Something went wrong!";
    //     this.$store.dispatch("globalState/errorSnackBar", message);
    //   }
    // },

    // async fetchStates() {
    //   if (!this.form.country_id) return;

    //   try {
    //     const response = await axios.post("/api/dropdown-state-list", {
    //       country_id: this.form.country_id,
    //     });
    //     this.states = response.data.data;
    //   } catch (error) {
    //     let message = error.response
    //       ? error.response.data.message
    //       : "Something went wrong!";
    //     this.$store.dispatch("globalState/errorSnackBar", message);
    //   }
    // },

    // async fetchCities() {
    //   if (!this.form.state_id) return;

    //   try {
    //     const response = await axios.post("/api/dropdown-city-list", {
    //       country_id: this.form.country_id,
    //       state_id: this.form.state_id,
    //     });
    //     this.cities = response.data.data;
    //   } catch (error) {
    //     let message = error.response
    //       ? error.response.data.message
    //       : "Something went wrong!";
    //     this.$store.dispatch("globalState/errorSnackBar", message);
    //   }
    // },
  },

  mounted() {
    // this.fetchCountries();
  },

  watch: {
    //   "form.country_id"(newVal) {
    //     if (newVal) {
    //       this.fetchStates();
    //     }
    //   },

    //   "form.state_id"(newVal) {
    //     if (newVal) {
    //       this.fetchCities();
    //     }
    //   },
  },
};
</script>
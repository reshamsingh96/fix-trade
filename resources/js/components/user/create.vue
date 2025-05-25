<template>
  <v-card class="mx-auto" elevation="4">
    <!-- Card Title -->
    <v-card-title>
      <h4 class="text-h4">
        {{ type == "create_user" ? "Create User" : "Update Info" }}
      </h4>
    </v-card-title>

    <v-divider></v-divider>
    <v-card-text class="" style="overflow-y: auto">
      <div class="d-flex align-center mt-3 mb-4">
        <v-img
          :src="image_url || '/images/profile_image.png'"
          max-width="100"
          rounded="lg"
        >
        </v-img>

        <v-btn
          color="primary"
          variant="outlined"
          @click="chooseImage"
          class="ml-3"
        >
          Choose Photo
        </v-btn>
        <v-btn
          class="ml-2"
          variant="text"
          icon
          size="small"
          color="error"
          @click="removeImage"
          v-if="form.image"
        >
          <TrashIcon />
        </v-btn>

        <input
          ref="imageInput"
          type="file"
          accept="image/*"
          class="d-none"
          @change="onImageSelected"
        />
      </div>

      <v-row>
        <v-col class="pb-0" cols="12" sm="6" md="6">
          <v-label class="text-subtitle-1 font-weight-semibold pb-2">
            Gender
          </v-label>
          <v-autocomplete
            v-model="form.gender"
            :items="genders"
            placeholder="Select gender"
            :error-messages="v$.form.gender.$errors.map((e) => e.$message)"
            @blur="v$.form.gender.$touch()"
          >
          </v-autocomplete>
        </v-col>

        <v-col class="pb-0" cols="12" sm="6" md="6">
          <v-label class="text-subtitle-1 font-weight-semibold pb-2">
            Name
          </v-label>
          <v-text-field
            placeholder="Enter full name"
            v-model="form.name"
            :error-messages="v$.form.name.$errors.map((e) => e.$message)"
            @blur="v$.form.name.$touch()"
            :rules="[(v) => !!v || 'Name is required']"
          >
          </v-text-field>
        </v-col>

        <v-col class="pb-0" cols="12" sm="12" md="12">
          <v-label class="text-subtitle-1 font-weight-semibold pb-2">
            Email
          </v-label>
          <v-text-field
            placeholder="Enter email"
            v-model="form.email"
            :error-messages="v$.form.email.$errors.map((e) => e.$message)"
            @blur="v$.form.email.$touch()"
            :rules="[(v) => !!v || 'Email is required']"
          >
          </v-text-field>
        </v-col>
        <v-col class="pb-0 pt-1" cols="12" sm="6" md="6">
          <v-label class="text-subtitle-1 font-weight-semibold pb-2">
            Phone
          </v-label>
          <v-text-field
            placeholder="Enter phone"
            v-model="form.phone"
            :error-messages="v$.form.phone.$errors.map((e) => e.$message)"
            @blur="v$.form.phone.$touch()"
            type="tel"
            @input="form.phone = form.phone.replace(/\D/g, '')"
          >
          </v-text-field>
        </v-col>

        <v-col class="pb-0 pt-1" cols="12" sm="6" md="6">
          <v-label class="text-subtitle-1 font-weight-semibold pb-2">
            Phone (Optional)
          </v-label>
          <v-text-field
            placeholder="Enter phone "
            v-model="form.secondary_number"
            type="tel"
            @input="
              form.secondary_number = form.secondary_number.replace(/\D/g, '')
            "
          >
          </v-text-field>
        </v-col>

        <v-col
          v-if="type === 'create_user'"
          class="pb-0 pt-1"
          cols="12"
          sm="6"
          md="6"
        >
          <v-label class="text-subtitle-1 font-weight-semibold pb-2">
            Account Type
          </v-label>
          <v-autocomplete
            v-model="form.account_type"
            :items="account_type_list"
            placeholder="Select Account Type"
            :error-messages="
              v$.form.account_type.$errors.map((e) => e.$message)
            "
            @blur="v$.form.account_type.$touch()"
            readonly
          >
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
        <v-col
          v-if="type === 'create_user'"
          class="pb-0 pt-1"
          cols="12"
          sm="6"
          md="6"
        >
          <v-label class="text-subtitle-1 font-weight-semibold pb-2">
            Account Role
          </v-label>
          <v-autocomplete
            placeholder="Select Account Role"
            v-model="form.role_id"
            :items="roles"
            item-title="name"
            item-value="uuid"
            :error-messages="v$.form.role_id.$errors.map((e) => e.$message)"
            @blur="v$.form.role_id.$touch()"
            readonly
          >
          </v-autocomplete>
        </v-col>

        <v-col
          v-if="type === 'create_user'"
          class="pb-0 pt-1"
          cols="12"
          sm="6"
          md="6"
        >
          <v-label class="text-subtitle-1 font-weight-semibold pb-2">
            Password
          </v-label>
          <v-text-field
            placeholder="Enter password"
            :type="passwordVisibility ? 'text' : 'password'"
            v-model="form.password"
            :error-messages="v$.form.password.$errors.map((e) => e.$message)"
            @blur="v$.form.password.$touch()"
            :append-inner-icon="passwordVisibility ? 'mdi-eye-off' : 'mdi-eye'"
            @click:append-inner="passwordVisibility = !passwordVisibility"
          >
          </v-text-field>
        </v-col>

        <v-col
          v-if="type === 'create_user'"
          class="pb-0 pt-1"
          cols="12"
          sm="6"
          md="6"
        >
          <v-label class="text-subtitle-1 font-weight-semibold pb-2">
            Confirm Password
          </v-label>
          <v-text-field
            placeholder="Enter password"
            :append-inner-icon="
              confirmPasswordVisibility ? 'mdi-eye-off' : 'mdi-eye'
            "
            :type="confirmPasswordVisibility ? 'text' : 'password'"
            v-model="form.confirm_password"
            @click:append-inner="
              confirmPasswordVisibility = !confirmPasswordVisibility
            "
            :error-messages="
              v$.form.confirm_password.$errors.map((e) => e.$message)
            "
            @blur="v$.form.confirm_password.$touch()"
          >
          </v-text-field>
        </v-col>



        <v-col class="pb-0 pt-1" cols="12" sm="6" md="6">
          <v-label class="text-subtitle-1 font-weight-semibold pb-2">
            Pin code
          </v-label>
          <v-text-field
            placeholder="Enter pin code"
            v-model="form.pin_code"
            :error-messages="v$.form.pin_code.$errors.map((e) => e.$message)"
            @blur="v$.form.pin_code.$touch()"
          >
          </v-text-field>
        </v-col>

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

        <v-col class="pb-0 pt-1" cols="12" sm="12" md="12">
          <v-label class="text-subtitle-1 font-weight-semibold pb-2">
            Full Address
          </v-label>
          <v-textarea
            rows="2"
            placeholder="Enter address"
            v-model="form.full_address"
          >
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
            :error-messages="v$.form.state_id.$errors.map((e) => e.$message)"
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
            :error-messages="v$.form.country_id.$errors.map((e) => e.$message)"
            @blur="v$.form.country_id.$touch()"
          >
          </v-autocomplete>
        </v-col> -->
      </v-row>
    </v-card-text>

    <!-- Divider -->
    <v-divider class="my-4"></v-divider>

    <!-- Actions Section -->
    <v-card-actions class="d-flex justify-end">
      <v-btn v-if="close_btn_show" color="error" class="mx-2" @click="close"
        >Close
      </v-btn>
      <v-btn
        class="mx-2"
        :loading="loader"
        :disabled="loader"
        color="primary"
        variant="flat"
        @click="submitForm()"
      >
        {{ type === "create_user" ? "Save" : "Update" }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>
<script>
import useVuelidate from "@vuelidate/core";
import { required, email, minLength, sameAs } from "@vuelidate/validators";
import axios from "axios";
import { TrashIcon } from "vue-tabler-icons";

export default {
  props: {
    type: String,
    user_info: Object,
    user_id: String,
  },
  components: {
    TrashIcon,
  },
  setup() {
    const v$ = useVuelidate();
    return { v$ };
  },

  data() {
    return {
      close_btn_show: false,
      passwordVisibility: false,
      confirmPasswordVisibility: false,
      image_url: null,
      form: {
        user_id: null,
        name: "",
        email: "",
        gender: "",
        account_type: "User",
        phone_code: "91",
        phone: "",
        role_id: null,
        secondary_number: "",
        status: "Active",
        is_buyer: true,
        is_seller: false,
        is_labor: false,
        password: "",
        confirm_password: "",
        image: "",
        // country_id: "",
        state: "",
        city: "",
        pin_code: "",
        home_no: "",
        full_address: "",
        is_type:'Buyer'
      },

      genders: ["Male", "Female", "Other"],
      account_type_list: ['User', 'Vendor', 'Labor'],
      is_type_list: ['Buyer', 'Seller', 'Labor'],
      countries: [],
      states: [],
      cities: [],
      roles: [],
      loader: false,
      user_info: null,
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
        password:
          this.type === "create_user"
            ? { required, minLength: minLength(6) }
            : {},
        confirm_password:
          this.type === "create_user"
            ? { required, sameAsPassword: sameAs(this.form.password) }
            : {},
        pin_code: { required },
        role_id: { required },
        is_type: { required },
        // country_id: { required },
        // state: { required },
        // city: { required },
      },
    };
  },

  mounted() {
    const path = window.location.pathname;
    if (path.includes("/admin/profile") || path.includes("/profile")) {
      this.close_btn_show = false;
    } else {
      this.close_btn_show = true;
    }
    console.log("user_id ", this.user_id);
    this.fetchCountries();
    this.fetchRoles();
    if (this.user_id) this.singleUserInfo();
  },

  watch: {
    "form.country_id"(newVal) {
      if (newVal) {
        this.fetchStates();
      }
    },

    "form.state_id"(newVal) {
      if (newVal) {
        this.fetchCities();
      }
    },
    user_id(val) {
      console.log(val);
      this.singleUserInfo();
    },
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
        this.form.image = file;

        const reader = new FileReader();
        reader.onload = (e) => {
          this.image_url = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },

    removeImage() {
      this.image_url = null;
      this.form.image = null;
    },

    async singleUserInfo() {
      if (!this.user_id) return;
      try {
        const response = await axios.post("/api/single-user-info", {
          user_id: this.user_id,
        });
        this.user_info = response.data.data;
        this.setFieldDate(this.user_info);
      } catch (error) {
        this.loader = false;
        let message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
    },

    setFieldDate(user) {
      this.image_url = user.image_url;
      let user_address = user.user_address.length > 0 ? user.user_address[0] : null;
      let is_type = user.is_buyer ? 'Buyer' : user.is_seller ? "Seller" : 'Labor';
      this.form = {
        user_id: user.uuid,
        is_type: is_type,
        name: user.name,
        email: user.email,
        gender: user.gender,
        account_type: user.account_type,
        phone_code: user.phone_code,
        phone: user.phone,
        role_id: this.roles.length > 0 ? this.roles[0].uuid : null,
        secondary_number: user.secondary_number,
        status: user.secondary_number,
        is_buyer: user.is_buyer,
        is_seller: user.is_seller,
        is_labor: user.is_labor,
        password: "",
        confirm_password: "",
        image: null,
        address_id: user_address ? user_address.id : null,
        // country_id: user_address ? user_address.country_id : null,
        // state_id: user_address ? user_address.state_id : null,
        // city_id: user_address ? user_address.city_id : null,
        pin_code: user_address ? user_address.pin_code : null,
        home_no: user_address ? user_address.home_no : null,
        full_address: user_address ? user_address.full_address : null,
      };
      // this.fetchCities();
    },

    async submitForm() {
      this.v$.$touch();
      if (this.v$.$invalid) {
        this.$store.dispatch("globalState/errorSnackBar", "Form is invalid!");
        return;
      }
      this.loader = true;

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
      this.loader = false;
        return;  // Stop the flow if postal code is invalid
      }

      this.form.is_buyer = this.form.is_type == 'Buyer' ? true : false;
      this.form.is_seller = this.form.is_type == 'Seller' ? true : false;
      this.form.is_labor = this.form.is_type == 'Labor' ? true : false;

      this.loader = true;

      const formData = new FormData();
      for (const key in this.form) {
        if (this.form[key] instanceof File) {
          formData.append(key, this.form[key]);
        } else {
          formData.append(key, this.form[key]);
        }
      }

      if (this.type == "create_user") {
        await axios
          .post("/api/user-create", formData, {
            headers: { "Content-Type": "multipart/form-data" },
          })
          .then((response) => {
            this.$store.dispatch("globalState/successSnackBar",response.data.message);
            this.loader = false;
            this.close();
          })
          .catch((error) => {
            this.loader = false;
            let message = error.response
              ? error.response.data.message
              : "Something went wrong!";
            this.$store.dispatch("globalState/errorSnackBar", message);
          });
      } else {
        await axios
          .post("/api/user-update", formData, {
            headers: { "Content-Type": "multipart/form-data" },
          })
          .then((response) => {
            console.log(response.data.message);
            this.$store.dispatch(
              "globalState/successSnackBar",
              response.data.message
            );
            this.loader = false;
            this.close();
          })
          .catch((error) => {
            this.loader = false;
            let message = error.response
              ? error.response.data.message
              : "Something went wrong!";
            this.$store.dispatch("globalState/errorSnackBar", message);
          });
      }
    },

    close() {
      this.reset();
      if (this.type === "create_user") {
        this.$emit("closeCreateDialog");
      } else {
        this.$emit("closeEditDialog");
      }
    },

    reset() {
      this.v$.$reset();
      this.passwordVisibility = false;
      this.confirmPasswordVisibility = false;
      this.image_url = null;
      this.form = {
        user_id: null,
        name: "",
        email: "",
        gender: "",
        phone_code: "91",
        phone: "",
        secondary_number: "",
        status: "Active",
        is_buyer: true,
        is_seller: false,
        is_labor: false,
        password: "",
        confirm_password: "",
        image: "",
        country_id: "",
        state_id: "",
        city_id: "",
        pin_code: "",
        home_no: "",
        full_address: "",
      };
    },

    async fetchCountries() {
      try {
        const response = await axios.post("/api/dropdown-country-list");
        this.countries = response.data.data;
        const india = this.countries.find(
          (country) => country.name.toLowerCase() === "india"
        );

        if (india) {
          this.form.country_id = india.id;
        }
      } catch (error) {
        let message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
    },

    async fetchStates() {
      if (!this.form.country_id) return;

      try {
        const response = await axios.post("/api/dropdown-state-list", {
          country_id: this.form.country_id,
        });
        this.states = response.data.data;
      } catch (error) {
        let message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
    },

    async fetchCities() {
      if (!this.form.state_id) return;

      try {
        const response = await axios.post("/api/dropdown-city-list", {
          country_id: this.form.country_id,
          state_id: this.form.state_id,
        });
        this.cities = response.data.data;
      } catch (error) {
        let message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
    },

    async fetchRoles() {
      try {
        const response = await axios.post("/api/dropdown-role-list");
        this.roles = response.data.data;
        this.form.role_id = this.roles.length > 0 ? this.roles[0].uuid : null;
      } catch (error) {
        let message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
    },
  },
};
</script>
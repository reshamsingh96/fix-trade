<template>
  <v-progress-linear color="primary" indeterminate v-if="loader"></v-progress-linear>
  <v-container class="mt-3 my-2" fluid v-if="user_info">
    <!-- User Info Card -->
    <v-card elevation="2">
      <v-card-title>
        <v-row class="mx-1" align="center">
          <v-col cols="6" sm="6" md="4">
            <h4 class="text-h4">Profile Info</h4>
          </v-col>

          <v-col order="2" order-md="1" cols="12" sm="12" md="4">
          </v-col>

          <v-col order="1" order-md="2" class="text-end" cols="6" sm="6" md="4">
            <v-btn class="mx-2" color="primary" @click="viewProfileEdit">
              <v-icon>mdi-pencil</v-icon> {{ edit_profile ? 'Hide' : 'Edit' }}
            </v-btn>
          </v-col>
        </v-row>
      </v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="3" class="text-center">
            <v-avatar size="100">
              <v-img :src="user_info.image_url || '/images/profile_image.png'" alt="User Image"></v-img>
            </v-avatar>
          </v-col>
          <v-col cols="4">
            <div class="d-flex flex-column justify-center h-100">
              <h4 class="font-weight-bold mb-0">{{ user_info.name }}</h4>

              <div class="d-flex align-items-center mb-1">
                <v-icon class="mr-2">mdi-gender-male-female</v-icon>
                <span>{{ user_info.gender }}</span>
              </div>

              <div class="d-flex align-items-center mb-1">
                <v-icon class="mr-2">mdi-phone</v-icon>
                <span>+{{ user_info.phone_code }} {{ user_info.phone }}</span>
              </div>

              <div class="d-flex align-items-center mb-1" v-if="user_info.date_of_birth">
                <v-icon class="mr-2">mdi-calendar</v-icon>
                <span>{{ user_info.date_of_birth }}</span>
              </div>

              <div class="d-flex align-items-center mb-1" v-if="user_info.secondary_number">
                <v-icon class="mr-2">mdi-phone</v-icon>
                <span>+{{ user_info.phone_code }} {{ user_info.secondary_number }}</span>
              </div>

              <div class="d-flex align-items-center mb-1">
                <v-icon class="mr-2">mdi-account</v-icon>
                <span>{{ user_info.status }}</span>
              </div>

              <div class="d-flex align-items-center mb-1" v-if="is_seller">
                <v-icon class="mr-2">mdi-store</v-icon>
                <span>Is Seller</span>
              </div>

              <div class="d-flex align-items-center mb-1" v-if="is_buyer">
                <v-icon class="mr-2">mdi-cart</v-icon>
                <span>Is Buyer</span>
              </div>

              <div class="d-flex align-items-center mb-1" v-if="is_labor">
                <v-icon class="mr-2">mdi-hammer</v-icon>
                <span>Is Labor</span>
              </div>

              <div class="d-flex align-items-center mb-1">
                <v-icon class="mr-2">mdi-email</v-icon>
                <span>{{ user_info.email }}</span>
              </div>

              <div class="d-flex align-items-center mb-1">
                <v-icon class="mr-2">mdi-account-box</v-icon>
                <span>{{ user_info.account_type }}</span>
              </div>
            </div>
          </v-col>

          <v-col cols="4">
            <div class="d-flex flex-column justify-center h-100">
              <h4 class="font-weight-bold mb-0">Address Info</h4>

              <div v-for="address in user_info.user_address" :key="address.id">
                <div class="d-flex align-items-center mb-1">
                  <v-icon class="mr-2">mdi-earth</v-icon>
                  <span>{{ address.country.name }}</span>
                </div>
                <div class="d-flex align-items-center mb-1">
                  <v-icon class="mr-2">mdi-map-marker</v-icon>
                  <span>{{ address.state.name }}</span>
                </div>
                <div class="d-flex align-items-center mb-1">
                  <v-icon class="mr-2">mdi-city</v-icon>
                  <span>{{ address.city.name }}</span>
                </div>
                <div class="d-flex align-items-center mb-1">
                  <v-icon class="mr-2">mdi-home</v-icon>
                  <span>{{ address.pin_code }}</span>
                </div>

                <div class="d-flex align-items-center mb-1" v-if="address.home_no">
                  <v-icon class="mr-2">mdi-home-outline</v-icon>
                  <span>{{ address.home_no }}</span>
                </div>

                <div class="d-flex align-items-center mb-1" v-if="address.full_address">
                  <v-icon class="mr-2">mdi-map-marker-radius</v-icon>
                  <span>{{ address.full_address }}</span>
                </div>
              </div>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
    <br />
  <!-- Edit Profile -->
  <UpdateProfile :type="'update_user'" :user_info="user_info" :user_id="user_info ? user_info.uuid : null"
    @closeEditDialog="closeEditDialog" />
    <br />
    <!-- Password Change Form -->
    <v-card elevation="12">
      <!-- Card Title -->
      <v-card-title>
        <h4>Change Password</h4>
      </v-card-title>
      <v-card-text class="mt-2">
        <v-row>
          <!-- Password Field -->
          <v-col cols="6">
            <v-text-field label="Password" variant="outlined" :type="passwordVisibility ? 'text' : 'password'"
              density="compact" v-model="form.password" :error-messages="v$.form.password.$errors.map(e => e.$message)"
              @blur="v$.form.password.$touch()" :append-inner-icon="passwordVisibility ? 'mdi-eye-off' : 'mdi-eye'"
              @click:append-inner="passwordVisibility = !passwordVisibility">
            </v-text-field>
          </v-col>

          <!-- Confirm Password Field -->
          <v-col cols="6">
            <v-text-field label="Confirm Password" variant="outlined"
              :append-inner-icon="confirmPasswordVisibility ? 'mdi-eye-off' : 'mdi-eye'"
              :type="confirmPasswordVisibility ? 'text' : 'password'" v-model="form.confirm_password" density="compact"
              @click:append-inner="confirmPasswordVisibility = !confirmPasswordVisibility"
              :error-messages="v$.form.confirm_password.$errors.map(e => e.$message)"
              @blur="v$.form.confirm_password.$touch()">
            </v-text-field>
          </v-col> </v-row>
      </v-card-text>
      <!-- Actions Section -->
      <v-card-actions class="d-flex justify-end" style="margin-top: -28px !important;">
        <v-btn class="mx-2" color="primary" :loading="loader" :disabled="loader" @click="changePassword()">Update
          Password
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
  <br />
  <Labor />
  <br />
  <BookmarkList />
  <br />
  <ReviewList />
</template>

<script>
import axios from 'axios';
import useVuelidate from '@vuelidate/core';
import { required, email, minLength, sameAs } from '@vuelidate/validators';
import BookmarkList from '../product/BookmarkList.vue';
import ReviewList from '../product/ReviewList.vue';
import Labor from '../labor/index.vue';
import UpdateProfile from '../user/create.vue';

export default {
  components: { BookmarkList, ReviewList, Labor, UpdateProfile },
  setup() {
    const v$ = useVuelidate();
    return { v$ };
  },
  data() {
    return {
      loader: false,
      user_info: null,
      user_id: null,

      passwordVisibility: false,
      confirmPasswordVisibility: false,
      form: {
        password: '',
        confirm_password: ''
      },
    };
  },

  validations() {
    return {
      form: {
        password: { required, minLength: minLength(6) },
        confirm_password: { required, sameAsPassword: sameAs(this.form.password) },
      }
    };
  },

  async mounted() {
    if (this.$route.name === 'profile' && this.$route.params.uuid) {
      this.user_id = this.$route.params.uuid;
      await this.singleUserInfo();
    }
  },

  methods: {
    viewProfileEdit() {
      this.edit_profile = !this.edit_profile;
      console.log(this.edit_profile); // Check the value
    },

    closeEditDialog() {
      this.edit_profile = false;
      this.singleUserInfo();
    },

    async singleUserInfo() {
      this.loader = true;
      try {
        const response = await axios.post('/api/single-user-info', { user_id: this.user_id });
        this.user_info = response.data.data;
        localStorage.setItem("user_login", JSON.stringify(this.user_info));
      } catch (error) {
        const message = error.response ? error.response.data.message : 'Something went wrong!';
        this.$store.dispatch('globalState/errorSnackBar', message);
      } finally {
        this.loader = false;
      }
    },

    togglePasswordVisibility() {
      this.passwordVisibility = !this.passwordVisibility;
    },

    toggleConfirmPasswordVisibility() {
      this.confirmPasswordVisibility = !this.confirmPasswordVisibility;
    },

    async changePassword() {
      this.v$.$touch();
      if (this.v$.$invalid) {
        this.$store.dispatch("globalState/errorSnackBar", "Form is invalid!");
        return;
      }
      this.change_loader = true;
      try {
        const response = await axios.post('/api/change-password', {
          user_id: this.user_id,
          password: this.form.password,
          password_confirmation: this.form.confirm_password
        });
        this.$store.dispatch('globalState/successSnackBar', 'Password changed successfully');
      } catch (error) {
        const message = error.response ? error.response.data.message : 'Failed to change password';
        this.$store.dispatch('globalState/errorSnackBar', message);
      } finally {
        this.change_loader = false;
      }
    }
  }
};
</script>
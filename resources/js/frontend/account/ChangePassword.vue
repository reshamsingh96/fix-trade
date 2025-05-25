<template>
    <v-progress-linear color="primary" indeterminate v-if="loader"></v-progress-linear>
    <v-container class="mt-3 my-2" fluid v-if="user_info">
        <v-card elevation="2">
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
  </template>
  
  <script>
  import axios from 'axios';
  import useVuelidate from '@vuelidate/core';
  import { required, email, minLength, sameAs } from '@vuelidate/validators';
import { mapState } from "vuex";
  
  export default {
    components: { },
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

    computed: {
    ...mapState({
      user: (state) => state.login.user,
    }),
  },
  
    async mounted() {
      if (this.user) {
        this.user_id = this.user.uuid;
        await this.singleUserInfo();
      }
    },
  
    methods: {
      async singleUserInfo() {
        this.loader = true;
        try {
          const response = await axios.post('/api/single-user-info', { user_id: this.user.uuid });
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
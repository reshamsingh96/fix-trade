<template>
    <div class="auth-page">
        <Navbar />
        <v-container class="fill-height align-center justify-center">
            <v-card class="auth-card" elevation="10">
                <v-img class="mx-auto" max-width="100" src="/images/logo.png"></v-img>

                <div class="text-center mb-5 mt-3">
                    <h3 class="text-h3">Forgot Password</h3>
                </div>
                <v-text-field density="compact" placeholder="Email address" prepend-inner-icon="mdi-email-outline"
                    variant="outlined" v-model="email" :rules="emailRules"></v-text-field>

                <v-label v-if="message" class="text-subtitle-1 font-weight-semibold pb-2 primary--text">
                    {{ message }}
                </v-label>
                
                <v-btn block class="mb-1" size="large" color="primary" variant="flat" @click="forgotPassword"
                    :loading="loading_forget" :disabled="loading_forget">
                    Reset Password
                </v-btn>
                <h6 class="text-h6 mt-3 font-weight-medium">
                    <a href="/login"><v-btn variant="text" color="primary" class="pl-1"> Sign in Now </v-btn></a>
                </h6>
            </v-card>
        </v-container>
    </div>
</template>

<script>
import Cookies from "js-cookie";
import axios from "axios";
import Navbar from "../common/Navbar.vue";

export default {
    components: {
        Navbar,
    },
    data() {
        return {
            loading_forget: false,
            message: 'fdfbad dvdbae rbgaerb argbaer aer r ',
            email: '',
            emailRules: [
                v => !!v || 'Email is required',
                v => /.+@.+\..+/.test(v) || 'E-mail must be valid',
            ],
        };
    },
    methods: {
        async forgotPassword() {
            this.loading_forget = true;
            try {
                this.message = '';
                const response = await axios.post('/api/forgot-password', { email: this.email, type: 'direct' });
                this.message = response.data.message;
                this.$store.dispatch("globalState/successSnackBar", response.data.message);
            } catch (error) {
                this.message = '';
                let msg = error.response ? error.response.data.message : "Something went wrong!";
                this.$store.dispatch("globalState/errorSnackBar", msg);
            } finally {
                this.loading_forget = false;
            }
        }
    }
}
</script>

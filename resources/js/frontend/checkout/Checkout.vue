<template>
  <v-app>
    <Navbar />
    <v-main>
      <v-container class="my-container">
        <v-row>
          <v-col cols="12" md="8">
            <v-card elevation="10">
              <v-card-text>
                <h4 class="text-h4 mb-6">Billing details</h4>

                <v-row>
                  <v-col class="pb-0" cols="12" md="6">
                    <v-text-field label="First Name"> </v-text-field>
                  </v-col>
                  <v-col class="pb-0" cols="12" md="6">
                    <v-text-field label="Last Name"> </v-text-field>
                  </v-col>
                  <v-col class="pb-0" cols="12" md="12">
                    <v-text-field label="Company Name"> </v-text-field>
                  </v-col>

                  <v-col class="pb-0" cols="12" md="12">
                    <v-text-field label="House number and street name">
                    </v-text-field>
                  </v-col>

                  <v-col class="pb-0" cols="12" md="12">
                    <v-text-field
                      label="Apartment, suite, unit, etc. (optional)"
                    >
                    </v-text-field>
                  </v-col>

                  <v-col class="pb-0" cols="12" md="12">
                    <v-text-field label="Town / City"> </v-text-field>
                  </v-col>

                  <v-col class="pb-0" cols="12" md="6">
                    <v-text-field label="State"> </v-text-field>
                  </v-col>

                  <v-col class="pb-0" cols="12" md="6">
                    <v-text-field label="Zipcode"> </v-text-field>
                  </v-col>

                  <v-col class="pb-0" cols="12" md="6">
                    <v-text-field label="Phone"> </v-text-field>
                  </v-col>

                  <v-col class="pb-0" cols="12" md="6">
                    <v-text-field label="Email"> </v-text-field>
                  </v-col>
                </v-row>
                <br />

                <div class="text-end">
                  <v-btn variant="flat" color="primary" size="large"
                    >Place Order</v-btn
                  >
                </div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="12" md="4">
            <v-card elevation="10">
              <v-card-item>
                <h5 class="text-h5 mb-6">Your order</h5>

                <div class="d-flex align-center justify-space-between my-5">
                  <h6 class="font-weight-semibold text-h6">Product</h6>
                  <h6 class="font-weight-semibold text-h6">Price</h6>
                </div>

                <div
                  v-for="item in 3"
                  :key="item"
                  class="d-flex align-center justify-space-between my-5"
                >
                  <h6 class="text-h6 font-weight-regular">Red Sindhi Cow</h6>
                  <h6 class="font-weight-semibold text-h6">$15</h6>
                </div>

                <v-divider></v-divider>

                <div class="d-flex align-center justify-space-between my-5">
                  <h6 class="text-h6">Subtotal</h6>
                  <h6 class="font-weight-semibold text-h5">$15</h6>
                </div>

                <div class="d-flex align-center justify-space-between my-5">
                  <h6 class="text-h6">Total</h6>
                  <h6 class="font-weight-semibold text-h5">$15</h6>
                </div>
              </v-card-item>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
      <br /><br />
    </v-main>
    <Footer />
  </v-app>
</template>

<script>
import Footer from "../common/Footer.vue";
import Navbar from "../common/Navbar.vue";

export default {
  components: {
    Navbar,
    Footer,
  },
};
</script>

<style>
</style>
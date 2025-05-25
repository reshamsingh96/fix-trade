<template>
  <v-app>
    <Navbar />
    <v-main>
      <v-container class="my-container">
        <v-row>
          <v-col cols="12" md="8">
            <v-card elevation="10">
              <v-card-text>
                <h5 class="text-h5 mb-4">Cart Item (5)</h5>

                <v-table>
                  <thead>
                    <tr>
                      <th
                        class="text-left text-subtitle-1 font-weight-semibold"
                      >
                        Product
                      </th>
                      <th
                        class="text-left text-subtitle-1 font-weight-semibold"
                      >
                        Price
                      </th>
                      <th
                        class="text-left text-subtitle-1 font-weight-semibold"
                      >
                        Quantity
                      </th>
                      <th
                        class="text-left text-subtitle-1 font-weight-semibold"
                      >
                        Total
                      </th>
                      <th class="text-right"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in 3" :key="item">
                      <td>
                        <div class="d-flex align-center my-3 gap-2">
                          <div>
                            <v-img
                              alt="product"
                              class="rounded-md"
                              height="70"
                              width="80"
                              cover
                              src="https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8N3x8ZmFybWluZ3xlbnwwfHwwfHx8MA%3D%3D"
                            />
                          </div>
                          <div class="ma-2">
                            <h6 class="text-h6">Red Sindhi Cow</h6>
                            <span class="text-subtitle-2"
                              >size: 8 | Color: Dark Red</span
                            >
                          </div>
                        </div>
                      </td>
                      <td>
                        <h4 class="text-h5">$13</h4>
                        <p
                          class="text-decoration-line-through text-medium-emphasis"
                        >
                          $15
                        </p>
                      </td>
                      <td>
                        <v-btn-toggle
                          variant="outlined"
                          divided
                          color="success"
                        >
                          <v-btn size="x-small">
                            <MinusIcon size="18" />
                          </v-btn>

                          <v-btn size="x-small"> 1 </v-btn>

                          <v-btn size="x-small">
                            <PlusIcon size="18" />
                          </v-btn>
                        </v-btn-toggle>
                      </td>
                      <td class="text-h5">$15</td>
                      <td class="text-right">
                        <v-btn small icon flat elevation="0"
                          ><TrashIcon size="18" class="text-error"
                        /></v-btn>
                      </td>
                    </tr>
                  </tbody>
                </v-table>
                <v-btn
                  class="mt-8"
                  color="primary"
                  size="large"
                  variant="outlined"
                  >Continue shopping</v-btn
                >
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="12" md="4">
            <v-card elevation="10">
              <v-card-item>
                <h5 class="text-h5 mb-6">Order Summery</h5>

                <div class="d-flex align-center justify-space-between my-5">
                  <h6 class="text-h6 font-weight-regular">Sub Total</h6>
                  <h6 class="font-weight-semibold text-h6">$15</h6>
                </div>

                <div class="d-flex align-center justify-space-between my-5">
                  <h6 class="text-h6 font-weight-regular">Discount 5%</h6>
                  <h6 class="font-weight-semibold text-h6 text-error">- $5</h6>
                </div>

                <div class="d-flex align-center justify-space-between my-5">
                  <h6 class="text-h6 font-weight-regular">Shipping Charges</h6>
                  <h6 class="font-weight-semibold text-h6">-</h6>
                </div>

                <div class="d-flex align-center justify-space-between my-5">
                  <h6 class="text-h6">Total</h6>
                  <h6 class="font-weight-semibold text-h5">$15</h6>
                </div>

                <v-btn
                  href="/checkout"
                  class="mt-8"
                  color="primary"
                  block
                  size="large"
                  variant="flat"
                  >Checkout</v-btn
                >
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
import { MinusIcon, PlusIcon, TrashIcon } from "vue-tabler-icons";
import Navbar from "../common/Navbar.vue";
import Footer from "../common/Footer.vue";

export default {
  components: {
    Navbar,
    MinusIcon,
    PlusIcon,
    TrashIcon,
    Footer,
  },
};
</script>

<style>
</style>
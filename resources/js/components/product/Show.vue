<template>
  <v-progress-linear color="primary" indeterminate v-if="loader"></v-progress-linear>
  <v-container class="mt-3 my-2" fluid  v-if="product_info">
    <v-card elevation="2">
      <v-card-title>
        <h4>Product Info</h4>
      </v-card-title>
      <v-card-text>
        <v-row v-if="product_info">
          <v-col cols="3" class="text-center">
            <v-avatar size="100">
              <v-img :src="product_info.image ? product_info.image.image_url : '/images/profile_image.png'"
                alt="Product Image"></v-img>
            </v-avatar>
          </v-col>
          <v-col cols="4">
            <div class="d-flex flex-column justify-center h-100">
              <h4 class="font-weight-bold mb-0">{{ product_info.name }}</h4>
              <div class="d-flex align-items-center mb-1">
                <v-icon class="mr-2">mdi-tag-outline</v-icon>
                <span>
                  <v-avatar v-if="product_info.category && product_info.category.category_url" color="blue" size="36"
                    class="mr-2">
                    <v-img :src="product_info.category.category_url"></v-img>
                  </v-avatar>
                  {{ product_info.category ? product_info.category.name : '' }}
                </span>
              </div>

              <div class="d-flex align-items-center mb-1">
                <v-icon class="mr-2">mdi-tag-multiple-outline</v-icon>
                <span>
                  <v-avatar v-if="product_info.sub_category && product_info.sub_category.sub_category_url" color="blue"
                    size="36" class="mr-2">
                    <v-img :src="product_info.sub_category.sub_category_url"></v-img>
                  </v-avatar>
                  {{ product_info.sub_category ? product_info.sub_category.name : '' }}
                </span>
              </div>
            </div>
          </v-col>

          <v-col cols="4">
            <div class="d-flex flex-column justify-center h-100">
              <!-- Seller, Buyer, Rent -->
              <div class="d-flex align-items-center mb-1" v-if="product_info.type">
                <v-icon class="mr-2">mdi-storefront-outline</v-icon>
                <span>{{ product_info.type }}</span>
              </div>

              <!-- Tax Type (Inclusive, Exclusive) -->
              <!-- <div class="d-flex align-items-center mb-1">
            <v-icon class="mr-2">mdi-cash-register</v-icon>
            <span>{{ product_info.tax_type }}</span>
          </div> -->

              <div class="d-flex align-items-center mb-1">
                <v-icon class="mr-2">mdi-comment-outline</v-icon>
                <span>{{ product_info.description }}</span>
              </div>
            </div>
          </v-col>
          <v-col cols="12" v-if="product_info">
            <v-row>
              <!-- Quantity -->
              <v-col cols="2" class="d-flex align-items-center mb-1">
                <strong class="mr-2">Quantity :</strong>
                <span>{{ product_info.quantity }}</span>
              </v-col>

              <!-- Price -->
              <v-col cols="2" class="d-flex align-items-center mb-1">
                <strong class="mr-2">Price :</strong>
                <span>{{ product_info.unit_price }}</span>
              </v-col>

              <!-- Discount (only if > 0) -->
              <v-col cols="2" class="d-flex align-items-center mb-1" v-if="product_info.discount > 0">
                <strong class="mr-2">Discount :</strong>
                <span>{{ product_info.discount }}</span>
              </v-col>

              <!-- Discount Price (only if > 0) -->
              <v-col cols="2" class="d-flex align-items-center mb-1" v-if="product_info.discount_unit_price > 0">
                <strong class="mr-2">Discount Price :</strong>
                <span>{{ product_info.discount_unit_price }}</span>
              </v-col>

              <!-- Tax Amount (only if > 0) -->
              <v-col cols="2" class="d-flex align-items-center mb-1" v-if="product_info.tax_amount > 0">
                <strong class="mr-2">With Tax Price :</strong>
                <span>{{ product_info.tax_amount }}</span>
              </v-col>

              <!-- Images (iterate if present) -->
              <v-col cols="1" v-for="(img, imgIndex) in product_info.product_images" :key="img.id" class="image-container mb-2">
                <v-img :src="img.image_url || '/images/default.png'" max-width="100"></v-img>
              </v-col>
            </v-row>
          </v-col>

        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
  <v-container class="mt-3 my-2" fluid v-if="user_info">
    <v-card elevation="2">
      <v-card-title>
        <h4>Created User Info</h4>
      </v-card-title>
      <v-card-text>
        <v-row v-if="user_info">
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
  </v-container>

    <br />
    <BookmarkList />
    <br />
    <ReviewList />
</template>

<script>
import axios from 'axios';
import BookmarkList from '../product/BookmarkList.vue';
import ReviewList from '../product/ReviewList.vue';

export default {
  components: { BookmarkList, ReviewList },
  data() {
    return {
      loader: false,
      product_info: null,
      product_id: null,
      user_info: null,
    };
  },

  async mounted() {
    if (this.$route.name == 'product.view' && this.$route.params.id) {
      this.product_id = this.$route.params.id;
      await this.singleProductInfo();
    }
  },

  methods: {
    async singleProductInfo() {
      this.loader = true;
      if (!this.product_id) return;
      try {
        const response = await axios.post('/api/single-product-info', { id: this.product_id });
        this.product_info = response.data.data;
        this.user_info = this.product_info.user;
        this.loader = false;
      } catch (error) {
        this.loader = false;
        let message = error.response ? error.response.data.message : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
    },
  },
};
</script>
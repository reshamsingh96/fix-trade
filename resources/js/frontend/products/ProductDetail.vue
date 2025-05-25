<template>
  <v-app>
    <Navbar />
    <v-main>
      <v-container class="my-container">
        <v-card elevation="10">
          <v-card-text>
            <v-row>
              <v-col lg="6" sm="12">
                <ProductCarousel :slideShow="image_list" />
              </v-col>

              <v-col lg="6" sm="12" class="pl-lg-5">
                <div>
                  <div class="d-flex align-center gap-2">
                    <v-chip
                      color="success"
                      variant="elevated"
                      size="small"
                      elevation="0"
                      v-if="product_info && var_quantity > 0"
                    >
                      In Stock
                    </v-chip>
                    <v-chip color="error" v-else> Out of Stock </v-chip>
                    <span class="text-subtitle-2">{{
                      product_info && product_info.category
                        ? product_info.category.name
                        : ""
                    }}</span>
                  </div>

                  <h3 class="text-h3 my-2 mb-3">
                    {{ product_info ? product_info.name + " " : "" }}({{
                      product_info && product_info.sub_category
                        ? product_info.sub_category.name
                        : ""
                    }})
                  </h3>

                  <p class="v-col-lg-10 px-0">
                    {{ product_info ? product_info.description : "" }}
                  </p>
                  <div class="d-flex align-center gap-2">
                    <p class="text-decoration-line-through text-h6" v-if="discount_unit_price != unit_price" >
                      ₹{{ unit_price }}
                    </p>
                    <h4 class="text-h4">₹{{ discount_unit_price }}</h4>
                  </div>

                  <div class="text-medium-emphasis align-center d-flex mt-3 gap-2" v-if="product_review_info" >
                    <v-rating
                      v-model="product_review_info.rating"
                      color="warning"
                      half-increments
                      size="small"
                      readonly
                      density="compact"
                    ></v-rating>
                    <span >({{ product_review_info ? product_review_info.rating_count : 0 }}reviews)</span>
                  </div>
                  <v-divider class="my-5"></v-divider>
                  <v-row>
                    <v-col cols="6" sm="2" class="d-flex align-center">
                      <h6 class="text-h6">Quantity</h6>
                    </v-col>
                    <v-col cols="6" sm="10">
                      <v-btn-toggle divided variant="outlined">
                        <v-btn
                          size="x-small"
                          color="secondary"
                          @click="decreaseQuantity"
                          :disabled="var_quantity <= 1"
                        >
                          <MinusIcon size="18" />
                        </v-btn>

                        <v-btn size="x-small" color="secondary">
                          {{ quantity }}
                        </v-btn>

                        <v-btn
                          size="x-small"
                          color="secondary"
                          @click="increaseQuantity"
                          :disabled="quantity >= var_quantity"
                        >
                          <PlusIcon size="18" />
                        </v-btn>
                      </v-btn-toggle>
                    </v-col>
                  </v-row>
                  <v-divider class="my-5"></v-divider>
                  <v-row class="mt-6">
                    <v-col cols="12" sm="4">
                      <!-- TODO: upcoming-->
                      <!-- <v-btn block size="large" color="primary" to="/ecommerce/checkout" variant="flat" >Buy Now</v-btn> -->
                      <v-btn
                        block
                        size="large"
                        color="primary"
                        variant="flat"
                        @click="order_dialog = true"
                        >Order</v-btn
                      >
                    </v-col>
                    <v-col cols="12" sm="4">
                      <v-btn
                        block
                        size="large"
                        variant="tonal"
                        color="secondary"
                        @click="info_dialog = true"
                        >User Info</v-btn
                      >
                      <!-- TODO: upcoming-->
                      <!-- <v-btn block size="large" variant="tonal" color="secondary" @click="addToCart">Add To Cart</v-btn> -->
                    </v-col>
                  </v-row>
                  <!-- <div class="mt-8">
                <h6 class="text-subtitle-1">Dispatched in 2-3 weeks</h6>
                <router-link to="/" class="text-subtitle-1 text-decoration-none">Why the longer time for
                  delivery?</router-link>
              </div> -->
                </div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
        <br />
        <v-card elevation="10">
          <v-card-text>
            <div class="d-flex justify-space-between mb-3">
              <h4 class="text-h4 mb-5">Reviews</h4>

              <v-btn v-if="product_info && !product_info.user_rating"
                @click="review_add = true"
                size="large"
                color="primary"
                variant="flat"
                >Write a Review</v-btn
              >
            </div>

            <v-row>
              <v-col cols="12" v-if="review_add">
                <v-rating
                  v-model="user_rating"
                  color="warning"
                  half-increments
                ></v-rating>
              </v-col>
              <v-col cols="12" v-if="review_add">
                <v-textarea
                  label="Description"
                  variant="outlined"
                  v-model="new_review"
                  hide-details
                  auto-grow
                  rows="2"
                  density="compact"
                >
                </v-textarea>
                <v-btn
                  @click="createReview()"
                  class="mt-2"
                  size="large"
                  color="primary"
                  variant="flat"
                  >Submit</v-btn
                >
              </v-col>
              <v-col cols="12">
                <div
                  v-for="review in review_list"
                  :key="review.id"
                  class="py-4 d-flex"
                >
                  <v-avatar color="primary" size="36">
                    <v-img
                      v-if="review.user && review.user.image_url"
                      :src="review.user.image_url"
                    ></v-img>
                    <span v-else class="white--text">{{
                      review.user ? nameInitial(review.user.name) : ""
                    }}</span>
                  </v-avatar>

                  <div class="ml-3 w-100">
                    <div class="d-flex justify-space-between">
                      <h6 class="text-h6">
                        {{ review.user ? review.user.name : "Anonymous" }}
                      </h6>
                      <span class="grey--text text--darken-2">{{
                        formatTimeAgo(review.created_at)
                      }}</span>
                    </div>

                    <v-rating
                      v-model="review.rating"
                      color="warning"
                      half-increments
                      size="small"
                      readonly
                      density="compact"
                    ></v-rating>
                    <p class="text-subtitle-1">{{ review.review }}</p>
                  </div>
                </div>
              </v-col>
            </v-row>

            <v-btn v-if="page < last_page" @click="loadMore"
              >Load More Reviews</v-btn
            >
          </v-card-text>
        </v-card>

        <!-- User Info Modal -->
        <v-dialog v-model="order_dialog" max-width="600px">
          <v-card>
            <v-card-title>Order Place</v-card-title>
            <v-card-text>
              <v-row>
                <!-- Price -->
                <v-col cols="6" sm="6" class="d-flex align-center">
                  <h6 class="text-h6 d-flex align-center">
                    Price
                    <p
                      v-if="
                        product_info && product_info.tax_type == 'Inclusive'
                      "
                    >
                      (Inclusive)
                    </p>
                    :
                  </h6>
                </v-col>
                <v-col cols="6" sm="6">
                  <h6 class="text-h6">{{ newUnitPrice }}</h6>
                </v-col>

                <!-- Quantity -->
                <v-col cols="6" sm="6" class="d-flex align-center">
                  <h6 class="text-h6">Quantity :</h6>
                </v-col>
                <v-col cols="6" sm="6">
                  <h6 class="text-h6">{{ quantity ? quantity : 0 }}</h6>
                </v-col>

                <!-- Amount -->
                <v-col cols="6" sm="6" class="d-flex align-center">
                  <h6 class="text-h6">Amount :</h6>
                </v-col>
                <v-col cols="6" sm="6">
                  <h6 class="text-h6">{{ amount }}</h6>
                </v-col>

                <!-- Tax Type -->
                <v-col cols="6" sm="6" class="d-flex align-center">
                  <h6 class="text-h6">Tax Type :</h6>
                </v-col>
                <v-col cols="6" sm="6">
                  <h6 class="text-h6">
                    {{ product_info ? product_info.tax_type : "Exclusive" }}
                  </h6>
                </v-col>

                <!-- Tax Info -->
                <v-col
                  cols="6"
                  sm="6"
                  class="d-flex align-center"
                  v-if="product_info && product_info.tax"
                >
                  <h6 class="text-h6">{{ product_info.tax.tax_name }} :</h6>
                </v-col>
                <v-col cols="6" sm="6" v-if="product_info && product_info.tax">
                  <h6 class="text-h6">
                    {{ product_info.tax.tax_percentage }}%
                  </h6>
                </v-col>

                <!-- GST Amount -->
                <v-col
                  cols="6"
                  sm="6"
                  class="d-flex align-center"
                  v-if="product_info && product_info.tax"
                >
                  <h6 class="text-h6">GST Amount :</h6>
                </v-col>
                <v-col cols="6" sm="6" v-if="product_info && product_info.tax">
                  <h6 class="text-h6">{{ gstAmount }}</h6>
                </v-col>

                <!-- Net Pay Amount -->
                <v-col cols="6" sm="6" class="d-flex align-center">
                  <h6 class="text-h6">Net Pay Amount :</h6>
                </v-col>
                <v-col cols="6" sm="6">
                  <h6 class="text-h6">{{ netPayableAmount }}</h6>
                </v-col>
              </v-row>
            </v-card-text>
            <v-card-actions class="d-flex justify-end">
              <v-btn
                color="error"
                class="mx-2"
                variant="flat"
                @click="order_dialog = false"
                >Cancel</v-btn
              >
              <v-btn
                class="mx-2"
                color="primary"
                variant="flat"
                @click="orderCreate()"
                >Place Order</v-btn
              >
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- User Info Modal -->
        <v-dialog v-model="info_dialog" max-width="600px">
          <v-card>
            <v-card-title>Product Owner User Info</v-card-title>
            <v-card-text>
              <v-row>
                <!-- User Name -->
                <v-col cols="6" sm="3" class="d-flex align-center">
                  <h6 class="text-h6">User Name :</h6>
                </v-col>
                <v-col cols="6" sm="9">
                  <h6 class="text-h6">
                    {{ product_info.user ? product_info.user.name : "" }}
                  </h6>
                </v-col>

                <!-- Phone No -->
                <v-col cols="6" sm="3" class="d-flex align-center">
                  <h6 class="text-h6">Phone No :</h6>
                </v-col>
                <v-col cols="6" sm="9">
                  <h6
                    class="text-h6"
                    @click="
                      copyToClipboard(
                        product_info.user
                          ? product_info.user.phone_code
                            ? '+' +
                              product_info.user.phone_code +
                              ' ' +
                              product_info.user.phone
                            : ''
                          : ''
                      )
                    "
                    style="cursor: pointer"
                  >
                    {{
                      product_info.user
                        ? product_info.user.phone_code
                          ? "+" +
                            product_info.user.phone_code +
                            " " +
                            product_info.user.phone
                          : ""
                        : ""
                    }}
                  </h6>
                </v-col>

                <!-- Secondary Number -->
                <v-col
                  v-if="product_info.user && product_info.user.secondary_number"
                  cols="6"
                  sm="3"
                  class="d-flex align-center"
                >
                  <h6 class="text-h6">Secondary Number :</h6>
                </v-col>
                <v-col
                  v-if="product_info.user && product_info.user.secondary_number"
                  cols="6"
                  sm="9"
                >
                  <h6
                    class="text-h6"
                    @click="
                      copyToClipboard(
                        product_info.user
                          ? product_info.user.phone_code
                            ? '+' +
                              product_info.user.phone_code +
                              ' ' +
                              product_info.user.secondary_number
                            : ''
                          : ''
                      )
                    "
                    style="cursor: pointer"
                  >
                    {{
                      product_info.user
                        ? product_info.user.phone_code
                          ? "+" +
                            product_info.user.phone_code +
                            " " +
                            product_info.user.secondary_number
                          : ""
                        : ""
                    }}
                  </h6>
                </v-col>

                <!-- Email -->
                <v-col cols="6" sm="3" class="d-flex align-center">
                  <h6 class="text-h6">Email :</h6>
                </v-col>
                <v-col cols="6" sm="9">
                  <h6
                    class="text-h6"
                    @click="
                      copyToClipboard(
                        product_info.user ? product_info.user.email : ''
                      )
                    "
                    style="cursor: pointer"
                  >
                    {{ product_info.user ? product_info.user.email : "" }}
                  </h6>
                </v-col>

                <!-- Addresses -->
                <div
                  v-for="address in product_info.user &&
                  product_info.user.user_address.length > 0
                    ? product_info.user.user_address
                    : []"
                  :key="address.id"
                >
                  <!-- <v-tooltip bottom> -->
                  <v-col cols="6" sm="3" class="d-flex align-center">
                    <h6 class="text-h6">Address :</h6>
                  </v-col>
                  <v-col cols="6" sm="9">
                    <!-- <template v-slot:activator="{ on, attrs }"> -->
                    <!-- <h6 class="text-h6" v-bind="attrs" v-on="on"> -->
                    <h6 class="text-h6">
                      {{ address.home_no ? address.home_no + "," : "" }}
                      {{ address.city ? address.city.name + "," : "" }}
                      {{ address.state ? address.state.name + "," : "" }}
                      {{ address.pin_code ? address.pin_code + "," : "" }}
                      {{ address.country ? address.country.name : "" }}
                    </h6>
                    <!-- </template> -->
                    <!-- <span>{{ address.full_address }}</span> -->
                  </v-col>
                  <!-- </v-tooltip> -->
                </div>
              </v-row>
            </v-card-text>
            <v-card-actions>
              <v-btn @click="info_dialog = false">close</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-container>
      <br /><br />
    </v-main>
    <Footer />
  </v-app>
</template>

<script>
import { ref } from "vue";
import Navbar from "../common/Navbar.vue";
import Footer from "../common/Footer.vue";
import ProductCarousel from "./components/ProductCarousel.vue";
import { CheckIcon, MinusIcon, PlusIcon } from "vue-tabler-icons";
import axios from "axios";
import { ability } from "../../ability.js";
import { mapState } from "vuex";

export default {
  components: {
    Navbar,
    ProductCarousel,
    CheckIcon,
    PlusIcon,
    MinusIcon,
    Footer,
  },
  setup() {},
  data() {
    return {
      order_dialog: false,
      ability: ability,
      review_add: false,
      new_review: "",
      user_info: null,
      slug: null,
      perPage: 10,
      page: 1,
      product_info: null,
      discount_unit_price: 0,
      unit_price: 0,
      quantity: 0,
      duration: 0,
      status: "Active",
      var_quantity: 0,
      image_list: [],

      review_list: [],
      review_info: null,
      last_page: 1,
      page: 1,

      info_dialog: false,
      user_rating: 1,

      product_review_info: null,
      customer_notes: "",
    };
  },

  computed: {
    ...mapState({
      user: (state) => state.login.user,
    }),

    newUnitPrice() {
      if (!this.product_info || !this.product_info.tax) return 0;
      const taxPercentage = this.product_info.tax.tax_percentage;

      if (this.product_info.tax_type === "Exclusive") {
        return this.discount_unit_price ?? 0;
      } else {
        return Math.round(
          (this.discount_unit_price ?? 0) / (taxPercentage / 100 + 1)
        );
      }
    },

    // Calculate the total amount based on quantity and discount price
    amount() {
      return this.quantity * this.newUnitPrice;
    },

    // Calculate GST amount based on the tax type and percentage
    gstAmount() {
      if (!this.product_info || !this.product_info.tax) return 0;
      const taxPercentage = this.product_info.tax.tax_percentage;

      if (this.product_info.tax_type === "Exclusive") {
        return Math.round(
          this.amount * (taxPercentage / 100 + 1) - this.amount
        );
      } else {
        let total = this.quantity * (this.discount_unit_price ?? 0);
        return Math.round(
          this.amount * (taxPercentage / 100 + 1) - this.amount
        );
      }
    },

    // Calculate the final net pay amount
    netPayableAmount() {
      if (this.product_info.tax_type === "Exclusive") {
        return Math.round(this.amount + this.gstAmount);
      } else {
        return Math.round(this.amount + this.gstAmount);
      }
    },
  },

  async mounted() {
    const path = window.location.pathname;
    this.slug = path.split("/product/")[1];
    console.log("Product slug:", this.slug);

    this.getProductDetail();
    this.getProductRating();
    this.getProductReviewList();
  },

  watch: {
    user(val) {
      if (!val) {
        this.user_info = this.checkLocalStorage();
      } else {
        this.user_info = val ?? localStorage.getItem("user_login") ?? null;
      }
    },
  },

  methods: {
    nameInitial(name) {
      const nameParts = name.trim().split(" ");
      const firstInitial = nameParts[0] ? nameParts[0][0].toUpperCase() : "";
      const secondInitial = nameParts[1] ? nameParts[1][0].toUpperCase() : "";
      return `${firstInitial}${secondInitial}`;
    },

    formatTimeAgo(time) {
      const date = new Date(time);
      const now = new Date();
      const diffInSeconds = (now - date) / 1000;
      const days = Math.floor(diffInSeconds / (60 * 60 * 24));

      if (days === 0) {
        return "Today";
      } else if (days === 1) {
        return "1 day ago";
      } else {
        return `${days} days ago`;
      }
    },
    writeReview() {},

    copyToClipboard(text) {
      if (!text) {
        console.log("No text to copy");
        this.$store.dispatch("globalState/errorSnackBar", "No text to copy");
        return;
      }
      if (navigator.clipboard) {
        console.log("Using navigator.clipboard API");
        navigator.clipboard
          .writeText(text)
          .then(() => {
            console.log("Text copied: ", text);
            this.$store.dispatch(
              "globalState/successSnackBar",
              "Copied to clipboard"
            );
          })
          .catch((error) => {
            this.$store.dispatch("globalState/errorSnackBar", "Failed to copy");
          });
      }
    },

    checkLocalStorage() {
      let info = localStorage.getItem("user_login") ?? null;
      if (info) {
        this.$store.dispatch("login/setUser", JSON.parse(info));
      }
      return info ? JSON.parse(info) : null;
    },

    selectColor(item) {
      this.discount_unit_price = item.discount_unit_price > 0 ? item.discount_unit_price : item.unit_price;
      this.unit_price = item.unit_price;
      this.var_quantity = item.quantity;
      this.duration = item.duration;
      this.status = item.status;
      this.quantity = item.quantity > 0 ? 1 : 0;
    },

    async getProductDetail() {
      await axios.post("/api/product-detail", { slug: this.slug }).then((response) => {
          this.product_info = response.data.data;
          this.image_list = [];
          if (this.product_info.product_images.length > 0) {
            this.product_info.product_images.forEach((image) => { this.image_list.push({ id: image.id, image: image.image_url, }); });
          }
          this.selectColor(this.product_info);
        }).catch((error) => {
          let message = error.response
            ? error.response.data.message
            : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },

    async getProductRating() {
      await axios
        .post("/api/web-product-rating", { slug: this.slug })
        .then((response) => {
          this.product_review_info = response.data.data;
        })
        .catch((error) => {
          let message = error.response
            ? error.response.data.message
            : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },

    load_more() {
      this.page = this.page + 1;
      this.getProductReviewList();
    },

    async getProductReviewList() {
      await axios
        .post("/api/product-review-list", { slug: this.slug, page: this.page })
        .then((response) => {
          this.review_list = response.data.data.data;
          if (this.page == 1) {
            this.review_list = this.review_list;
          } else {
            response.data.data.data.forEach((item) => {
              this.review_list.push(item);
            });
          }
          this.last_page = response.data.data.last_page;
        })
        .catch((error) => {
          let message = error.response
            ? error.response.data.message
            : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },

    decreaseQuantity() {
      if (this.quantity > 1) this.quantity--;
    },

    increaseQuantity() {
      if (this.quantity < this.var_quantity) this.quantity++;
    },

    addToCart() {
      alert("addToCart");
      return;
      axios
        .post("/api/add-to-cart", {
          product_id: this.product_info.id,
          quantity: this.quantity,
        })
        .then((response) => {
          this.$store.dispatch("globalState/successSnackBar", "Added to cart");
        })
        .catch((error) => {
          let message = error.response
            ? error.response.data.message
            : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },

    orderCreate() {
      if (!this.user) {
        this.$store.dispatch(
          "globalState/errorSnackBar",
          "Please First Login!"
        );
        return;
      }
      axios
        .post("/api/order-create", {
          user_id: this.user ? this.user.uuid : null,
          guest_user_id: this.guest_user ? this.guest_user.id : null,
          product_id: this.product_info.id,
          quantity: this.quantity,
          sale_price: this.newUnitPrice,
          duration: this.duration,
          status: "Pending",
          payment_type: "Cash On Delivery",
          payment_status: "Pending",
          coupon_id: null,
          coupon_code: null,
          coupon_discount_amount: 0,
          gst_amount: this.gstAmount,
          net_payable_amount: this.netPayableAmount,
          customer_notes: this.customer_notes,
        })
        .then((response) => {
          this.order_dialog = false;
          this.getProductDetail();
          this.getProductRating();
          this.getProductReviewList();
          this.$store.dispatch("globalState/successSnackBar",response.data.message);
        })
        .catch((error) => {
          this.getProductDetail();
          let message = error.response? error.response.data.message: "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },
 
    async createReview() {
      if (!this.user) {
        this.$store.dispatch("globalState/errorSnackBar","Please First Login!");
        return;
      }

      let url = "/api/product-review-create"; // ?? this.user ? "api/product-review-update" : "/api/product-review-create";
      let obj = {
        product_id: this.product_info ? this.product_info.id : null,
        user_id: this.user ? this.user.uuid : null,
        review: this.new_review,
        rating: this.user_rating,
      };

      await axios.post(url, obj).then((response) => {
          let info = response.data.data;
          this.review_add = false;
          this.new_review = "";
          this.user_rating = 1;
          this.$store.dispatch( "globalState/successSnackBar", response.data.message );
          this.getProductDetail();
          this.getProductRating();
          this.getProductReviewList();
        }).catch((error) => {
          let message = error.response? error.response.data.message: "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },
  },
};
</script>
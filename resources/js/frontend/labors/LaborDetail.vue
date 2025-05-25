<template>
  <v-app>
    <Navbar />
    <v-main>
      <v-container class="my-container">
        <v-card elevation="10">
          <v-card-text>
            <v-row>
              <v-col lg="6" sm="12">
                <LaborCarousel :slideShow="labor_image_list" />
              </v-col>

              <v-col lg="6" sm="12" class="pl-lg-5">
                <div>
                  <div class="d-flex align-center gap-2">
                    <v-chip color="success" variant="elevated" size="small" elevation="0"
                      v-if="labor_info && labor_info.status">
                      {{ labor_info.status }}
                    </v-chip>
                    <span class="text-subtitle-2">{{ labor_info && labor_info.work_title ? labor_info.work_title :
                      ""}}</span>
                  </div>

                  <h3 class="text-h3 my-2 mb-3">
                    {{ labor_info ? labor_info.labor_name + " " : "" }}{{ labor_info && labor_info.phone ?
                      '(' + labor_info.phone+')' : "" }}
                  </h3>

                  <p class="v-col-lg-10 px-0">{{ labor_info ? labor_info.description : "" }}</p>
                  <!-- <div class="d-flex align-center gap-2">
                    <p class="text-decoration-line-through text-h6" v-if="discount_unit_price != unit_price">₹{{ unit_price }}</p>
                    <h4 class="text-h4">₹{{ discount_unit_price }}</h4>
                  </div> -->
                  <v-divider class="my-5"></v-divider>
                  <v-row v-for="(work, i) in labor_work_list" :key="work.id">
                    <!-- Day Name -->
                    <v-col cols="12" md="2">
                      <strong>Day:</strong> {{ work.day_name }}
                    </v-col>

                    <!-- Start Time -->
                    <v-col cols="12" md="2">
                      <strong>Start Time:</strong> {{ work.start_time }}
                    </v-col>

                    <!-- End Time -->
                    <v-col cols="12" md="2">
                      <strong>End Time:</strong> {{ work.end_time }}
                    </v-col>

                    <!-- Break Minute -->
                    <v-col cols="12" md="2">
                      <strong>Break Minutes:</strong> {{ work.break_minute }}
                    </v-col>

                    <!-- Working Hour -->
                    <v-col cols="12" md="2">
                      <strong>Working Hours:</strong> {{ work.working_hour }}
                    </v-col>

                    <!-- Per Hour Amount -->
                    <v-col cols="12" md="1">
                      <strong>Per Hour:</strong> ₹{{ work.per_hour_amount }}
                    </v-col>

                    <!-- Day Amount -->
                    <v-col cols="12" md="1">
                      <strong>Day Amount:</strong> ₹{{ work.day_amount }}
                    </v-col>
                  </v-row>
                  <v-divider class="my-5"></v-divider>
                  <v-row class="mt-6">
                    <v-col cols="12" sm="4">
                      <v-btn block size="large" variant="tonal" color="secondary" @click="info_dialog = true">User
                        Info</v-btn>
                    </v-col>
                  </v-row>
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

              <v-btn v-if="labor_info && !labor_info.user_rating"
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
        <v-dialog v-model="info_dialog" max-width="600px">
          <v-card>
            <v-card-title>Labor Owner User Info</v-card-title>
            <v-card-text>
              <v-row>
                <!-- User Name -->
                <v-col cols="6" sm="3" class="d-flex align-center">
                  <h6 class="text-h6">User Name :</h6>
                </v-col>
                <v-col cols="6" sm="9">
                  <h6 class="text-h6">
                    {{ labor_info.user ? labor_info.user.name : "" }}
                  </h6>
                </v-col>

                <!-- Phone No -->
                <v-col cols="6" sm="3" class="d-flex align-center">
                  <h6 class="text-h6">Phone No :</h6>
                </v-col>
                <v-col cols="6" sm="9">
                  <h6 class="text-h6" @click="
                    copyToClipboard(
                      labor_info.user
                        ? labor_info.user.phone_code
                          ? '+' +
                          labor_info.user.phone_code +
                          ' ' +
                          labor_info.user.phone
                          : ''
                        : ''
                    )
                    " style="cursor: pointer">
                    {{
                      labor_info.user
                        ? labor_info.user.phone_code
                          ? "+" +
                          labor_info.user.phone_code +
                          " " +
                          labor_info.user.phone
                          : ""
                        : ""
                    }}
                  </h6>
                </v-col>

                <!-- Secondary Number -->
                <v-col v-if="labor_info.user && labor_info.user.secondary_number" cols="6" sm="3"
                  class="d-flex align-center">
                  <h6 class="text-h6">Secondary Number :</h6>
                </v-col>
                <v-col v-if="labor_info.user && labor_info.user.secondary_number" cols="6" sm="9">
                  <h6 class="text-h6" @click="
                    copyToClipboard(
                      labor_info.user
                        ? labor_info.user.phone_code
                          ? '+' +
                          labor_info.user.phone_code +
                          ' ' +
                          labor_info.user.secondary_number
                          : ''
                        : ''
                    )
                    " style="cursor: pointer">
                    {{
                      labor_info.user
                        ? labor_info.user.phone_code
                          ? "+" +
                          labor_info.user.phone_code +
                          " " +
                          labor_info.user.secondary_number
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
                  <h6 class="text-h6" @click="
                    copyToClipboard(
                      labor_info.user ? labor_info.user.email : ''
                    )
                    " style="cursor: pointer">
                    {{ labor_info.user ? labor_info.user.email : "" }}
                  </h6>
                </v-col>

                <!-- Addresses -->
                <div v-for="address in labor_info.user &&
                  labor_info.user.user_address.length > 0
                  ? labor_info.user.user_address
                  : []" :key="address.id">
                  <v-col cols="6" sm="3" class="d-flex align-center">
                    <h6 class="text-h6">Address :</h6>
                  </v-col>
                  <v-col cols="6" sm="9">
                    <h6 class="text-h6">
                      {{ address.home_no ? address.home_no + "," : "" }}
                      {{ address.city ? address.city.name + "," : "" }}
                      {{ address.state ? address.state.name + "," : "" }}
                      {{ address.pin_code ? address.pin_code + "," : "" }}
                      {{ address.country ? address.country.name : "" }}
                    </h6>
                  </v-col>
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
import LaborCarousel from "./components/LaborCarousel.vue";
import { CheckIcon, MinusIcon, PlusIcon } from "vue-tabler-icons";
import axios from "axios";
import { ability } from "../../ability.js";
import { mapState } from "vuex";

export default {
  components: {
    Navbar,
    LaborCarousel,
    CheckIcon,
    PlusIcon,
    MinusIcon,
    Footer,
  },

  setup() { },

  data() {
    return {
      ability: ability,
      user_info: null,
      slug: null,
      perPage: 10,
      page: 1,
      labor_info: null,
      labor_work_list: [],
      discount_unit_price: 0,
      unit_price: 0,
      quantity: 0,
      duration: 0,
      status: "Active",
      var_quantity: 0,
      labor_image_list: [],

      review_list: [],
      review_info: null,
      last_page: 1,
      page: 1,

      info_dialog: false,
      review_add:false,
      user_rating: 1,
      product_review_info: null,
    };
  },

  computed: {
    ...mapState({
      user: (state) => state.login.user,
    }),
  },

  async mounted() {
    const path = window.location.pathname;
    this.slug = path.split("/labor/")[1];
    console.log("labor slug:", this.slug);
    this.getLaborDetail();
    this.getLabourRating();
    this.getLabourReviewList();
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
    nameInitial(name) {
      const nameParts = name.trim().split(" ");
      const firstInitial = nameParts[0] ? nameParts[0][0].toUpperCase() : "";
      const secondInitial = nameParts[1] ? nameParts[1][0].toUpperCase() : "";
      return `${firstInitial}${secondInitial}`;
    },
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
    },

    async getLaborDetail() {
      await axios
        .post("/api/labor-detail", { id: this.slug })
        .then((response) => {
          this.labor_info = response.data.data;
          this.labor_work_list = this.labor_info ? this.labor_info.working_day : [];
          this.labor_image_list = [];
          if (this.labor_info && this.labor_info.work_images) {
            this.labor_info.work_images.forEach((image) => {
              this.labor_image_list.push({
                id: image.id,
                image: image.image_url,
              });
            });
          }

          if (this.labor_info && this.labor_info.image_url) {
            this.labor_image_list.push({
              id: this.labor_info.image_public_id,
              image: this.labor_info.image_url,
            });
          }
        })
        .catch((error) => {
          let message = error.response ? error.response.data.message : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },

    load_more() {
      this.page = this.page + 1;
      this.getLabourReviewList();
    },

    async getLabourReviewList() {
      await axios
        .post("/api/labour-review-list", { labour_id: this.slug, page: this.page })
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
          let message = error.response? error.response.data.message: "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },

    async getLabourRating() {
      await axios
        .post("/api/web-labour-rating", { labour_id: this.slug })
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
     
    async createReview() {
      if (!this.user) {
        this.$store.dispatch("globalState/errorSnackBar","Please First Login!");
        return;
      }
      if (this.user_rating == 0 || this.user_rating == '' || this.user_rating == null) {
        this.$store.dispatch("globalState/errorSnackBar","Rating is required!");
        return;
      }

      let url = "/api/labour-review-create";
      let obj = {
        labour_id: this.labor_info ? this.labor_info.id : null,
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
          this.getLaborDetail();
          this.getLabourRating();
          this.getLabourReviewList();
        }).catch((error) => {
          let message = error.response? error.response.data.message: "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },
  },
};
</script>
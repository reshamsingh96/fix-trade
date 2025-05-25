<template>
  <v-app>
    <Navbar />
    <v-main>
      <section class="products-page">
        <v-container class="my-container">
          <v-card elevation="10">
            <v-card-text>
              <v-row>
                <v-col cols="12" sm="4" md="3" lg="3">
                  <v-text-field v-model="search" label="Search" hide-details density="compact" clearable
                    variant="outlined" @click:clear="OnClear" @keyup="getProductList"></v-text-field>
                </v-col>

                <v-col cols="12" sm="4" md="3" lg="3">
                  <v-autocomplete class="mr-2" v-model="category_id" :items="category_list" hide-details
                    label="Category" variant="outlined" density="compact" item-title="name" item-value="id">
                  </v-autocomplete>
                </v-col>

                <v-col cols="6" sm="4" md="3" lg="3">
                  <v-autocomplete class="mr-2" v-model="sub_category_id" :items="sub_category_list" hide-details
                    label="Sub Category" variant="outlined" density="compact" item-title="name" item-value="id">
                  </v-autocomplete>
                </v-col>
                <v-col cols="6" sm="4" md="3" lg="2">
                  <v-select label="Select Type" :items="type_list" v-model="type" hide-details variant="outlined"
                    density="compact" item-title="text" item-value="value"></v-select>
                </v-col>

                <v-col cols="6" sm="4" md="3" lg="1">
                  <v-btn variant="text" color="primary" @click="Refresh" size="small" icon>
                    <RefreshIcon />
                  </v-btn>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <v-row class="mt-4">
            <v-col v-for="item in product_list" :key="item" cols="12" sm="6" md="4">
              <a :href="`/product/${item.slug}`" class="product-card-link">
                <div class="product-card">
                  <figure class="card-banner">
                    <img v-if="item.image" :src="item.image.image_url" />
                    <img v-else :src="'/images/no_product_image.png'" />
                    <div class="card-badge golden">
                      {{ item.category ? item.category.name : "" }}
                    </div>
                  </figure>

                  <div class="card-content">
                    <div class="d-flex justify-space-between align-center">
                      <!-- Card Price -->
                      <div class="card-price">
                        <div class="d-flex align-center gap-2">
                          <p v-if="item.unit_price != item.discount_unit_price"
                            class="text-decoration-line-through text-h6">
                            ₹{{ item.unit_price }}
                          </p>
                          <h4 class="text-h4">
                            ₹{{ item.discount_unit_price }}
                          </h4>
                        </div>
                      </div>

                      <div class="icon-actions">
                        <!-- Bookmark -->
                        <v-icon class="cursor-pointer mr-2" @click="bookmarkProduct(item, $event)"
                          :color="item.bookmark_info && item.bookmark_info.is_bookmark ? 'primary' : ''">
                          {{ item.bookmark_info && item.bookmark_info.is_bookmark ? "mdi-bookmark" :
                          "mdi-bookmark-outline" }}
                        </v-icon>
                      </div>
                    </div>

                    <h3 class="h3 card-title">
                      <a>{{ item.name + " " }}({{
                        item.sub_category ? item.sub_category.name : ""
                      }})</a>
                    </h3>

                    <p class="card-text">
                      {{ item.description }}
                    </p>
                  </div>
                </div>
              </a>
            </v-col>
          </v-row>
        </v-container>
      </section>
      <br /><br />
    </v-main>
    <Footer />
  </v-app>
</template>
<script>
import axios from "axios";
import { ability } from "../../ability.js";
import { EyeIcon, EditIcon, TrashIcon, RefreshIcon } from "vue-tabler-icons";
import Navbar from "../common/Navbar.vue";
import Footer from "../common/Footer.vue";
import { mapState } from "vuex";

export default {
  components: { EyeIcon, EditIcon, TrashIcon, Navbar, Footer, RefreshIcon },

  data() {
    return {
      ability: ability,
      product_list: [],
      last_page: 1,
      page: 1,
      per_row: 25,
      type: "All",
      type_list: [
        { value: "All", text: "All" },
        // { value: 'Buyer', text: 'Buy' },
        // { value: 'Seller', text: 'Sale' },
        { value: "Rent", text: "Rent" },
      ],
      category_id: "",
      category_list: [],
      sub_category_id: "",
      sub_category_list: [],
      user_info: null,
    };
  },

  computed: {
    ...mapState({
      user: (state) => state.login.user,
    }),
  },

  async mounted() {
    this.getCategoryList();
    this.getProductList();
  },

  watch: {
    page(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.getProductList();
      }
    },
    per_row(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.getProductList();
      }
    },
    category_id(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.sub_category_list = [];
        this.subCategoryList();
        this.getProductList();
      }
    },
    sub_category_id(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.getProductList();
      }
    },
    type(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.subCategoryList();
      }
    },

    user(val) {
      if (!val) {
        this.user_info = this.checkLocalStorage();
      } else {
        this.user_info = val ?? localStorage.getItem("user_login") ?? null;
      }
    },
  },

  methods: {
    checkLocalStorage() {
      let info = localStorage.getItem("user_login") ?? null;
      if (info) {
        this.$store.dispatch("login/setUser", JSON.parse(info));
      }
      return info ? JSON.parse(info) : null;
    },

    async getCategoryList() {
      await axios
        .post("/api/dropdown-category-list")
        .then((response) => {
          this.category_list = response.data.data;
          this.category_list = [
            { id: "", name: "Select All" },
            ...this.category_list,
          ];
        })
        .catch((error) => {
          let message = error.response
            ? error.response.data.message
            : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },

    async subCategoryList() {
      await axios
        .post("/api/dropdown-sub-category-list", {
          category_id: this.category_id,
        })
        .then((response) => {
          this.sub_category_list = response.data.data;
          this.sub_category_list = [
            { id: "", name: "Select All" },
            ...this.sub_category_list,
          ];
        })
        .catch((error) => {
          this.sub_category_list = [];
          let message = error.response
            ? error.response.data.message
            : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },

    Refresh() {
      this.page = 1;
      this.per_row = 25;
      this.type = "All";
      this.OnClear();
    },

    OnClear() {
      this.search = "";
      this.getProductList();
    },

    async getProductList() {
      this.loader = true;
      const params = {
        search: this.search,
        page: this.page,
        perPage: this.per_row,
        type: this.type,
        category_id: this.category_id,
        sub_category_id: this.sub_category_id,
        user_id: this.user ? this.user.uuid : null,
      };

      await axios.post("/api/web-product-list", params).then((response) => {
        this.product_list = response.data.data.data;
        this.last_page = response.data.data.last_page;
        this.loader = false;
      }).catch((error) => {
        this.loader = false;
        let message = error.response ? error.response.data.message : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      });
    },

    async bookmarkProducffft(item, event) {
      event.preventDefault();
      if (!this.user) {
        this.$store.dispatch("globalState/errorSnackBar", "Please First Login!");
        return;
      }
      let obj = {
        product_id: item.id,
        user_id: item.bookmark_info ? item.bookmark_info.user_id : null,
        is_bookmark: item.bookmark_info && item.bookmark_info.is_bookmark ? item.bookmark_info.is_bookmark == false ? true : false : true,
        type: "web_list",
      };

      if (item.bookmark_info) {
        obj.id = item.bookmark_info ? item.bookmark_info.id : null;
      }

      let url = item.bookmark_info ? "api/product-bookmark-update" : "/api/product-bookmark-create";
      await axios.post(url, obj).then((response) => {
        let info = response.data.data;
        item.bookmark_info.id = info.id;
        item.bookmark_info.is_bookmark = info.is_bookmark;
        item.bookmark_info.user_id = info.user_id;
        item.bookmark_info.product_id = info.product_id;

        this.$store.dispatch("globalState/successSnackBar", response.data.message);
      })
        .catch((error) => {
          let message = error.response ? error.response.data.message : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },
    async bookmarkProduct(item, event) {
      event.preventDefault();

      if (!this.user) {
        this.$store.dispatch("globalState/errorSnackBar", "Please First Login!");
        return;
      }

      let isBookmark = item.bookmark_info && item.bookmark_info.is_bookmark ? false : true;

      let obj = {
        product_id: item.id,
        user_id: this.user.uuid,
        is_bookmark: isBookmark,
      };

      if (item.bookmark_info) {
        obj.id = item.bookmark_info.id;
      }

      let url = item.bookmark_info ? "/api/product-bookmark-update" : "/api/product-bookmark-create";

      try {
        const response = await axios.post(url, obj);
        let info = response.data.data;

        // Update bookmark_info directly on the item
        item.bookmark_info = {
          ...info,
          is_bookmark: info.is_bookmark,
        };

        this.$store.dispatch("globalState/successSnackBar", response.data.message);
      } catch (error) {
        let message = error.response ? error.response.data.message : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
    }

  },
};
</script>

<style></style>
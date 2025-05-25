<template>
  <v-app>
    <Navbar />
    <v-main>
      <section class="labor-page">
        <v-container class="my-container">
          <v-row class="mx-1 mb-1">
            <v-col cols="12" sm="4" md="3" lg="2">
              <v-text-field
                v-model="search"
                label="Search"
                hide-details
                density="compact"
                clearable
                variant="outlined"
                @click:clear="OnClear"
                @keyup="getLaborList"
              ></v-text-field>
            </v-col>
            <v-col cols="6" sm="4" md="3" lg="2">
              <v-icon @click="Refresh" class="cursor-pointer">mdi-refresh</v-icon>
            </v-col>
          </v-row>

          <v-row>
            <v-col
              v-for="item in labor_list"
              :key="item"
              cols="12"
              sm="6"
              md="4"
            >
              <a :href="`/labor/${item.id}`" class="product-card-link">
                <div class="product-card">
                  <figure class="card-banner">
                    <img v-if="item.image_url" :src="item.image_url" />
                    <img v-else :src="'/images/labour.png'" />
                    <div v-if="item.phone" class="card-badge golden">
                      {{ item.phone ? item.phone : "" }}
                    </div>
                  </figure>

                  <div class="card-content">
                    <div class="d-flex justify-space-between align-center">
                      <!-- Card Price -->
                      <div class="card-price">
                        <div class="d-flex align-center gap-2">
                          <h4 class="text-h4">
                            ₹{{ item.min_amount }}
                          </h4>
                        </div>
                      </div>

                      <div class="icon-actions">
                      </div>
                    </div>

                    <h3 class="h3 card-title">
                      <a>{{ item.work_title + " " }}{{item.labor_name ? '('+item.labor_name+')' : ""}}</a>
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
import { EyeIcon, EditIcon, TrashIcon } from "vue-tabler-icons";
import Navbar from "../common/Navbar.vue";
import Footer from "../common/Footer.vue";
import { mapState } from "vuex";

export default {
  components: { EyeIcon, EditIcon, TrashIcon, Navbar, Footer },

  data() {
    return {
      ability: ability,
      labor_list: [],
      last_page: 1,
      page: 1,
      per_row: 25,
      type: "All",
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
    this.getLaborList();
  },

  watch: {
    page(newVal, oldVal) {
    if (newVal !== oldVal) {
      this.getLaborList();
    }
  },

  per_row(newVal, oldVal) {
    if (newVal !== oldVal) {
      this.getLaborList();
    }
  },

    category_id(newVal, oldVal) {
      if (newVal !== oldVal) { this.sub_category_list = [];
      this.subCategoryList();
      this.getLaborList(); }
    },

    sub_category_id(newVal, oldVal) {
      if (newVal !== oldVal) {this.getLaborList();}
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
      this.OnClear();
    },

    OnClear() {
      this.search = "";
      this.getLaborList();
    },

    async getLaborList() {
      this.loader = true;
      const params = {
        search: this.search,
        page: this.page,
        perPage: this.per_row,
      };

      await axios.post("/api/web-labor-list", params)
        .then((response) => {
          this.labor_list = response.data.data.data;
          this.last_page = response.data.data.last_page;
          this.loader = false;
        })
        .catch((error) => {
          this.loader = false;
          let message = error.response ? error.response.data.message : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },
  },
};
</script>

<style></style>
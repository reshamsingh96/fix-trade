<template>
  <div class="dashboard-container">
    <v-container fluid>
      <v-progress-linear
        color="primary"
        indeterminate
        v-if="loader"
      ></v-progress-linear>
      <v-row v-else>
        <!-- Dashboard Cards -->
        <v-col v-for="(item, index) in cards" :key="index" cols="12" md="3">
          <v-card
            elevation="10"
            :color="item.color"
            class="dash-card"
            @click="goToRoute(item.route)"
          >
            <div class="dash-card-detail">
              <div class="dash-info">
                <p>{{ item.title }}</p>
                <h1 class="text-h3">{{ item.count }}</h1>
              </div>

              <div class="dash-ic">
                <Icon :item="item.icon" size="30" />
              </div>
            </div>

            <v-sparkline
              :model-value="graphValue"
              color="rgba(255, 255, 255, .7)"
              height="100"
              padding="20"
              stroke-linecap="round"
              smooth
            >
            </v-sparkline>
          </v-card>
        </v-col>
      </v-row>
      <br />
      <v-row class="mt-2 mb-3">
        <v-col cols="12" md="6">
          <v-progress-linear
            color="primary"
            indeterminate
            v-if="product_loader"
          ></v-progress-linear>

          <v-card elevation="10">
            <v-row class="mx-1" align="center">
              <v-col cols="6" sm="6" md="4">
                <h4 class="text-h4">Latest Products</h4>
              </v-col>

              <v-col order="2" order-md="1" cols="12" sm="12" md="4"> </v-col>

              <v-col
                order="1"
                order-md="2"
                class="text-end"
                cols="6"
                sm="6"
                md="4"
              >
                <router-link to="/admin/products">
                  <v-btn color="primary" outlined> View </v-btn>
                </router-link>
              </v-col>
            </v-row>

            <v-data-table-server
              :headers="product_header"
              :items="product_list"
              item-value="name"
              hide-default-footer
            >
              <template v-slot:item.name="{ item }">
                <v-avatar
                  color="primary"
                  size="36"
                  class="mr-2 avatar-img-cover"
                >
                  <v-img v-if="item.image" :src="item.image.image_url"></v-img>
                  <span v-else class="white--text">{{
                    nameInitial(item.name)
                  }}</span>
                </v-avatar>
                {{ item.name }}
              </template>
            </v-data-table-server>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-progress-linear
            color="primary"
            indeterminate
            v-if="order_loader"
          ></v-progress-linear>

          <v-card elevation="10">
            <v-row class="mx-1" align="center">
              <v-col cols="6" sm="6" md="4">
                <h4 class="text-h4">Latest Orders</h4>
              </v-col>

              <v-col order="2" order-md="1" cols="12" sm="12" md="4"> </v-col>

              <v-col
                order="1"
                order-md="2"
                class="text-end"
                cols="6"
                sm="6"
                md="4"
              >
                <router-link to="/admin/orders">
                  <v-btn color="primary" outlined> View </v-btn>
                </router-link>
              </v-col>
            </v-row>

            <v-data-table-server
              :headers="order_header"
              :items="order_list"
              hide-default-footer
              item-value="name"
            >
              <template v-slot:item.name="{ item }">
                <v-avatar
                  color="primary"
                  size="36"
                  class="mr-2 avatar-img-cover"
                >
                  <v-img v-if="item.image" :src="item.image.image_url"></v-img>
                  <span v-else class="white--text">{{
                    nameInitial(item.name)
                  }}</span>
                </v-avatar>
                {{ item.name }}
              </template>
            </v-data-table-server>
          </v-card>
        </v-col>
      </v-row>

      <!-- Category and Sub-Category Counts -->
      <div v-if="category_list.length > 0">
        <h4 class="text-h4 mt-5 mb-4">Category by Sub-Category</h4>
        <v-row>
          <v-col
            cols="12"
            md="3"
            v-for="(item, index) in category_list"
            :key="index"
          >
            <v-card elevation="10" class="dash-card2">
              <div class="dash-ic">
                <v-avatar
                  color="primary"
                  variant="tonal"
                  size="36"
                  class="mr-2 avatar-img-cover"
                >
                  <v-img
                    v-if="item.category_url"
                    :src="item.category_url"
                  ></v-img>
                  <span v-else class="white--text">{{
                    nameInitial(item.name)
                  }}</span>
                </v-avatar>
              </div>
              <div class="dash-info">
                <p>{{ item.name }}</p>
                <h1 class="text-h3">{{ item.sub_category_count }}</h1>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </div>
      <!-- Product and Sub-Category Counts -->
      <div v-if="category_product_count_list.length > 0">
        <h4 class="text-h4 mt-5 mb-4">Category By Product</h4>

        <v-row>
          <v-col
            cols="12"
            md="3"
            v-for="(item, index) in category_product_count_list"
            :key="index"
          >
            <v-card elevation="10" class="dash-card2">
              <div class="dash-ic">
                <v-avatar
                  color="primary"
                  size="36"
                  variant="tonal"
                  class="mr-2 avatar-img-cover"
                >
                  <v-img
                    v-if="item.category_url"
                    :src="item.category_url"
                  ></v-img>
                  <span v-else class="white--text">{{
                    nameInitial(item.name)
                  }}</span>
                </v-avatar>
              </div>
              <div class="dash-info">
                <p>{{ item.name }}</p>
                <h1 class="text-h3">{{ item.product_count }}</h1>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </div>

      <!-- Sub-Category By Product Count -->
      <div v-if="sub_category_product_count_list.length > 0">
        <h4 class="text-h4 mt-5 mb-4">Sub-Category By Product</h4>
        <v-row
          v-for="(category, index) in sub_category_product_count_list"
          :key="index"
        >
          <v-col class="d-flex align-center" cols="12">
            <v-avatar
              color="primary"
              size="36"
              variant="tonal"
              class="mr-2 avatar-img-cover"
            >
              <v-img
                v-if="category.category_url"
                :src="category.category_url"
              ></v-img>
              <span v-else class="white--text">{{
                nameInitial(category.name)
              }}</span>
            </v-avatar>
            <h5 class="text-h5">
              {{ category.name }}
            </h5>
          </v-col>
          <!-- Loop through sub-categories of the category -->
          <v-col
            cols="12"
            md="3"
            v-for="(sub_category, i) in category.sub_category"
            :key="i"
          >
            <v-card elevation="10" class="dash-card2">
              <div class="dash-ic">
                <!-- Sub-Category Avatar -->
                <v-avatar
                  color="primary"
                  size="36"
                  variant="tonal"
                  class="mr-2 avatar-img-cover"
                >
                  <v-img
                    v-if="sub_category.sub_category_url"
                    :src="sub_category.sub_category_url"
                  ></v-img>
                  <span v-else class="white--text">{{
                    nameInitial(sub_category.name)
                  }}</span>
                </v-avatar>
              </div>

              <!-- Sub-Category Info -->
              <div class="dash-info">
                <p>{{ sub_category.name }}</p>
                <h1 class="text-h3">{{ sub_category.product_count }}</h1>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </div>
    </v-container>
  </div>
</template>
<script>
import axios from "axios";
import {
  CheckupListIcon,
  PackageIcon,
  ReportIcon,
  UserIcon,
  UsersGroupIcon,
} from "vue-tabler-icons";
import Icon from "../../common/Icon.vue";
import { mapState } from "vuex";

export default {
  components: {
    Icon,
    UserIcon,
    PackageIcon,
    CheckupListIcon,
    UsersGroupIcon,
    ReportIcon,
  },
  data() {
    return {
      loader: false,
      cards: [
        {
          title: "Users",
          count: 0,
          icon: UserIcon,
          route: "/admin/users",
          color: "primary",
        },
        {
          title: "Products",
          count: 0,
          icon: PackageIcon,
          route: "/admin/products",
          color: "#fd8b12",
        },
        {
          title: "Orders",
          count: 0,
          icon: CheckupListIcon,
          route: "/admin/orders",
          color: "secondary",
        },
        {
          title: "Labor",
          count: 0,
          icon: UsersGroupIcon,
          route: "/admin/labors",
          color: "#007c7c",
        },
      ],

      product_loader: false,
      product_header: [
        { title: "Name", key: "name" },
        { title: "Quantity", key: "quantity" },
        { title: "Price", key: "discount_unit_price" },
      ],
      product_list: [],

      order_loader: false,
      order_header: [
        { title: "Name", key: "name" },
        { title: "date", key: "date" },
        { title: "Amount", key: "amount" },
      ],
      order_list: [],

      category_loader: false,
      category_product_count_list: [],

      sub_loader: false,
      sub_category_product_count_list: [],

      cate_sub_loader: false,
      category_list: [],
      graphValue: [423, 446, 675, 510, 590, 610, 760],
    };
  },

  computed: {
    ...mapState({
      user: (state) => state.login.user,
    }),
  },
  async mounted() {
    await this.getDashboardCounts();
    await this.getCategorySubCategoryCount();
    await this.getCategoryProductCount();
    await this.getSubCategoryProductCount();
    await this.getLatestFiveProducts();
    await this.getLatestFiveOrders();
  },
  methods: {
    nameInitial(name) {
      if (!name) return "";
      const nameParts = name.trim().split(" ");
      const firstInitial = nameParts[0] ? nameParts[0][0].toUpperCase() : "";
      const secondInitial = nameParts[1] ? nameParts[1][0].toUpperCase() : "";
      return `${firstInitial}${secondInitial}`;
    },

    async getDashboardCounts() {
      this.loader = true;
      try {
        const response = await axios.get("/api/card-dashboard-count");
        let info = response.data.data;
        this.cards[0].count = info.users_count;
        this.cards[1].count = info.products_count;
        this.cards[2].count = info.orders_count;
        // this.cards[3].count = info.labors_count;
      } catch (error) {
        let message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
      this.loader = false;
    },

    async getLatestFiveProducts() {
      this.product_loader = true;
      try {
        const response = await axios.get("/api/latest-five-product");
        this.product_list = response.data.data;
      } catch (error) {
        let message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
      this.product_loader = false;
    },

    async getLatestFiveOrders() {
      this.order_loader = true;
      try {
        const response = await axios.get("/api/latest-five-order");
        this.order_list = response.data.data;
      } catch (error) {
        let message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
      this.order_loader = false;
    },

    async getCategoryProductCount() {
      this.category_loader = true;

      try {
        const response = await axios.get("/api/category-product-count");
        this.category_product_count_list = response.data.data;
      } catch (error) {
        let message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
      this.category_loader = false;
    },

    async getSubCategoryProductCount() {
      this.sub_loader = true;

      try {
        const response = await axios.get("/api/sub-category-product-count");
        this.sub_category_product_count_list = response.data.data;
      } catch (error) {
        let message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
      this.sub_loader = false;
    },

    async getCategorySubCategoryCount() {
      this.cate_sub_loader = true;

      try {
        const response = await axios.get("/api/category-sub-category-count");
        this.category_list = response.data.data;
      } catch (error) {
        let message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
      this.cate_sub_loader = false;
    },

    goToRoute(route) {
      this.$router.push(route);
    },
  },
};
</script>

<style scoped>
.dashboard-container {
  padding: 20px;
}

.dashboard-card {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
  /* Added cursor pointer for better UX */
}

.hovered-card {
  transform: translateY(-5px);
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
}
</style>

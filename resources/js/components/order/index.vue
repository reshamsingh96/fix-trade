<template>
  <v-progress-linear color="primary" indeterminate v-if="loader"></v-progress-linear>
  <v-container class="mt-3 my-2" fluid>
    <!-- Order List -->
    <v-card elevation="10">
      <v-row class="mx-1" align="center">
        <v-col cols="6" sm="6" md="6">
          <h4 class="text-h4">Orders</h4>
        </v-col>

        <v-col class="text-end" cols="6" sm="6" md="6">
          <v-icon @click="Refresh" class="cursor-pointer">mdi-refresh</v-icon>
        </v-col>
      </v-row>

      <v-row class="mx-1 mb-1">
        <v-col cols="12" sm="4" md="3" lg="2">
          <v-text-field v-model="search" label="Search" hide-details density="compact" clearable variant="outlined"
            @click:clear="onClear" @keyup="orderList"></v-text-field>
        </v-col>

        <v-col cols="12" sm="4" md="3" lg="2">
          <v-autocomplete class="mr-2" v-model="category_id" :items="category_list" hide-details label="Category"
            variant="outlined" density="compact" item-title="name" item-value="id">
          </v-autocomplete>
        </v-col>

        <v-col cols="6" sm="4" md="3" lg="2">
          <v-autocomplete class="mr-2" v-model="sub_category_id" :items="sub_category_list" hide-details
            label="Sub Category" variant="outlined" density="compact" item-title="name" item-value="id">
          </v-autocomplete>
        </v-col>

        <v-col cols="6" sm="4" md="3" lg="2" v-if="!url_product_id">
          <v-autocomplete class="mr-2" v-model="product_id" :items="product_list" hide-details label="Product"
            variant="outlined" density="compact" item-title="name" item-value="id">
          </v-autocomplete>
        </v-col>
        <v-col cols="6" sm="4" md="3" lg="2">
          <v-select label="Select Status" :items="status_list" v-model="status" variant="outlined"
            density="compact"></v-select>
        </v-col>
      </v-row>

      <v-data-table-server v-model:items-per-page="per_row" :headers="headers" :items="order_list"
        :items-length="order_total" item-value="name" @update:options="orderList">
        <!-- Sr No Slot -->
        <template v-slot:item.sr_no="{ item, index }">
          {{ (page - 1) * per_row + index + 1 }}
        </template>

        <!-- User Name Slot -->
        <template v-slot:item.user_name="{ item }">
          <v-avatar color="blue" size="36" class="mr-2">
            <v-img v-if="item.user && item.user.image_url" :src="item.user.image_url"></v-img>
            <span v-else class="white--text">{{
              nameInitial(item.user.name)
            }}</span>
          </v-avatar>
          {{ item.user.name }}
        </template>

        <!-- slug -->
        <template v-slot:item.slug="{ item }">
          {{ item.slug }}
        </template>

        <!-- status -->
        <template v-slot:item.status="{ item }">
          {{ item.status }}
        </template>

        <!-- Payment Type -->
        <template v-slot:item.payment_type="{ item }">
          {{ item.payment_type }}
        </template>

        <!-- Payment Status -->
        <template v-slot:item.payment_status="{ item }">
          {{ item.payment_status }}
        </template>

        <!-- total -->
        <template v-slot:item.total_amount="{ item }">
          {{ item.total_amount }}
        </template>

        <!-- Date -->
        <template v-slot:item.created_at="{ item }">
          {{ formatDate(item.created_at) }}
        </template>

        <!-- Actions Slot -->
        <template v-slot:item.actions="{ item }">
          <v-btn v-if="route_name.includes('/admin/')" @click="statusDialog(item)" size="small" variant="text" color="primary" icon>
            <EditIcon />
          </v-btn>
          <v-btn v-if="user && user.account_type == 'Super Admin'" @click="confirmDelete(item)" size="small" variant="text" color="error" icon>
            <TrashIcon />
          </v-btn>
        </template>
      </v-data-table-server>
    </v-card>
  </v-container>
  <div class="ml-5">
    <!-- Status Dialog -->
    <v-dialog v-model="status_dialog" max-width="500" persistent scrollable>
      <v-card>
        <v-card-title>Order Status Update</v-card-title>
        <v-card-text>
          <v-row>
            <v-col>
              <v-select label="Status" :items="order_status_list" v-model="order_status"
                variant="outlined" density="compact"></v-select>
            </v-col>
            <v-col>
              <v-select label="Payment Status" :items="payment_status_list" v-model="payment_status"
                variant="outlined" density="compact"></v-select>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="d-flex justify-center">
          <v-btn color="error" @click="closeStatusDialog()">Cancel</v-btn>
          <v-btn  :disabled="btn_loader" :loading="btn_loader" @click="orderStatusUpdate()" color="primary">Update</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Confirm Delete Dialog -->
    <v-dialog v-model="confirmDeleteDialog" max-width="500" persistent scrollable>
      <v-card>
        <v-card-title>Confirm Delete</v-card-title>
        <v-card-text>Are you sure you want to delete this Order?</v-card-text>
        <v-card-actions class="d-flex justify-center">
          <v-btn color="error" @click="closeConfirmDialog()">Cancel</v-btn>
          <v-btn @click="deleteOrder()" color="primary">Yes</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import axios from "axios";
import { ability } from "../../ability.js";
import { EyeIcon, EditIcon, TrashIcon, Disabled2Icon } from "vue-tabler-icons";
import { mapState } from "vuex";
import moment from "moment";

export default {
  components: { EyeIcon, EditIcon, TrashIcon },
  data() {
    return {
      headers: [
        { title: "No.", key: "sr_no" },
        { title: "User Name", key: "user_name" },
        { title: "Slug", key: "slug" },
        { title: "Status", key: "status" },
        { title: "Payment Type", key: "payment_type" },
        { title: "Payment Status", key: "payment_status" },
        { title: "Amount", key: "total_amount" },
        { title: "Date", key: "created_at" },
        { title: "Actions", key: "actions", sortable: false, align: "end" },
      ],
      url_user_id: null,
      url_product_id: null,
      loader: false,
      page: 1,
      per_row: 25,
      search: "",
      order_list: [],
      order_total: 1,
      order_info: null,
      confirmDeleteDialog: false,
      ability: ability,

      category_id: "",
      sub_category_id: "",
      category_list: [],
      sub_category_list: [],
      product_id: "",
      product_list: [],
      status_list: ['All', 'Pending', 'Progress', 'Delivered', 'Completed', 'Cancel'],
      status: 'Pending',
      order_type: 'user',

      btn_loader:false,
      status_dialog: false,
      order_status_list: ['Pending', 'Progress', 'Delivered', 'Completed', 'Cancel'],
      order_status: null,
      payment_status_list: ['Pending', 'Paid', 'Failed'],
      payment_status: null,
      route_name: null,
    };
  },

  computed: {
    ...mapState({
      user: (state) => state.login.user,
    }),
  },

  async mounted() {
    const path = window.location.pathname;
    this.route_name = path;
    if (path.includes('/admin/profile') || path.includes('/admin/user')) {
      this.url_user_id = this.$route.params.uuid;
      this.order_type = 'user';
      await this.orderList();
    } else if (path.includes('/my-account')) {
      this.url_user_id = this.user.uuid;
      this.order_type = 'user';
      await this.orderList();
    }
    if (path.includes('/admin/product')) {
      this.url_product_id = this.$route.params.id;
      this.order_type = 'product';
      await this.orderList();
    }
    await this.getCategoryList();
    await this.productList();
  },

  watch: {
    per_row(newVal, oldVal) {
      if (newVal !== oldVal) { this.orderList(); }
    },

    page(newVal, oldVal) {
      if (newVal !== oldVal) { this.orderList(); }
    },

    category_id(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.product_id = null;
        this.subCategoryList();
        this.productList();
        this.orderList();
      }
    },

    sub_category_id(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.product_id = null;
        this.productList();
        this.orderList();
      }
    },
    product_id(newVal, oldVal) {
      if (newVal !== oldVal) { this.orderList(); }
    },

    status(newVal, oldVal) {
      if (newVal !== oldVal) { this.orderList(); }
    },
  },

  methods: {
    formatDate(date) {
      return moment(date).format('MMMM Do YYYY, h:mm:ss a');
    },

    async getCategoryList() {
      await axios.post("/api/dropdown-category-list").then((response) => {
        this.category_list = response.data.data;
        this.category_list = [{ id: "", name: "Select All" }, ...this.category_list,];
      }).catch((error) => {
        let message = error.response ? error.response.data.message : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      });
    },

    async subCategoryList() {
      await axios.post("/api/dropdown-sub-category-list", {
        category_id: this.category_id,
      }).then((response) => {
        this.sub_category_list = response.data.data;
        this.sub_category_list = [{ id: "", name: "Select All" }, ...this.sub_category_list,];
      }).catch((error) => {
        this.sub_category_list = [];
        let message = error.response ? error.response.data.message : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      });
    },

    async productList() {
      await axios.post("/api/dropdown-user-product-list", {
        category_id: this.category_id,
        sub_category_id: this.sub_category_id,
      }).then((response) => {
        this.product_list = response.data.data;
        this.product_list = [{ id: "", name: "Select All" }, ...this.product_list,];
      }).catch((error) => {
        this.sub_category_list = [];
        let message = error.response ? error.response.data.message : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      });
    },

    nameInitial(name) {
      if (!name) return "";
      const nameParts = name.trim().split(" ");
      const firstInitial = nameParts[0] ? nameParts[0][0].toUpperCase() : "";
      const secondInitial = nameParts[1] ? nameParts[1][0].toUpperCase() : "";
      return `${firstInitial}${secondInitial}`;
    },

    Refresh() {
      this.page = 1;
      this.per_row = 25;
      this.category_id = "";
      this.sub_category_id = "";
      this.product_id = "";
      this.onClear();
    },

    onClear() {
      this.search = "";
      this.orderList();
    },

    async orderList() {
      this.loader = true;
      const params = {
        search: this.search,
        page: this.page,
        perPage: this.per_row,
        url_product_id: this.url_product_id,
        url_product_id: this.url_product_id,
        category_id: this.category_id,
        sub_category_id: this.sub_category_id,
        product_id: this.product_id,
        status: this.status,
        order_type: this.order_type
      };

      await axios.post("/api/order-list", params).then((response) => {
        this.order_list = response.data.data.data;
        this.order_total = response.data.data.last_page;
        this.loader = false;
      }).catch((error) => {
        this.loader = false;
        let message = error.response ? error.response.data.message : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      });
    },

    // delete order Function
    statusDialog(order) {
      this.order_info = order;
      this.order_status = order.status;
      this.payment_status = order.payment_status;
      this.status_dialog = true;
    },

    closeStatusDialog() {
      this.status_dialog = false;
      this.order_info = null;
      this.order_status = null;
      this.payment_status = null;
    },

    async orderStatusUpdate() {
      this.btn_loader =true;
      await axios.post('/api/order-status-update', {
        order_id: this.order_info.id,
        status: this.order_status,
        payment_status: this.payment_status,
      }).then((response) => {
        this.orderList();
        this.closeStatusDialog();
        this.$store.dispatch("globalState/successSnackBar", response.data.message);
      }).catch((error) => {
        let message = error.response ? error.response.data.message : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      });
      this.btn_loader = false;
    },

    // delete order Function
    confirmDelete(order) {
      this.order_info = order;
      this.confirmDeleteDialog = true;
    },

    closeConfirmDialog() {
      this.confirmDeleteDialog = false;
      this.order_info = null;
    },

    async deleteOrder() {
      const params = { id: this.order_info.id };
      await axios.post("/api/order-delete", params).then((response) => {
        this.orderList();
        this.closeConfirmDialog();
      }).catch((error) => {
        let message = error.response ? error.response.data.message : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      });
    },
  },
};
</script>
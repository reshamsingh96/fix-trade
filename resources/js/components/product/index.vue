<template>
  <v-progress-linear color="primary" indeterminate v-if="loader"></v-progress-linear>

  <v-container class="mt-3 my-2" fluid>
    <v-card elevation="10">
      <v-row class="mx-1" align="center">
        <v-col cols="6" sm="6" md="6">
          <h4 class="text-h4">Products</h4>
        </v-col>

        <v-col class="text-end" cols="6" sm="6" md="6">
          <v-btn @click="openCreateDialog" color="primary" variant="flat" class="mr-2">Add New</v-btn>
          <v-icon @click="refresh" class="cursor-pointer">mdi-refresh</v-icon>
        </v-col>
      </v-row>

      <v-row class="mx-1 mb-1">
        <v-col cols="12" sm="4" md="3" lg="2">
          <v-text-field v-model="search" label="Search" hide-details density="compact" clearable variant="outlined"
            @click:clear="onClear" @keyup="getProductList"></v-text-field>
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

        <v-col cols="6" sm="4" md="3" lg="2">
          <v-autocomplete class="mr-2" v-model="user_id" :items="user_list" hide-details label="User" variant="outlined"
            density="compact" item-title="name" item-value="uuid">
          </v-autocomplete>
        </v-col>
      </v-row>

      <v-data-table-server v-model:items-per-page="per_page" :headers="headers" :items="product_list"
        :items-length="totalItems" item-value="name" @update:options="getUserList">
        <template v-slot:item.name="{ item }">
          <v-avatar color="primary" size="36" class="mr-2 avatar-img-cover">
            <v-img v-if="item.product_images.length > 0" :src="item.product_images[0].image_url"></v-img>
            <span v-else class="white--text">{{ nameInitial(item.name) }}</span>
          </v-avatar>
          {{ item.name }}
        </template>

        <template v-slot:item.phone="{ item }">
          <p>+{{ item.phone_code }} {{ item.phone }}</p>
        </template>
        <!-- :label="item.status" -->
        <template v-slot:item.status="{ item }">
          <v-switch v-model="item.status"  false-value="In-Active" true-value="Active" hide-details
            color="primary" @change="() => toggleProductStatus(item)"></v-switch>
        </template>

        <template v-slot:item.actions="{ item }">
          <v-btn @click="viewProductDetail(item)" color="secondary" size="small" variant="text" icon>
            <EyeIcon />
          </v-btn>

          <v-btn @click="openEditDialog(item)" size="small" variant="text" icon>
            <EditIcon />
          </v-btn>

          <v-btn @click="confirmDelete(item)" size="small" variant="text" color="error" icon>
            <TrashIcon />
          </v-btn>
        </template>
      </v-data-table-server>
    </v-card>
  </v-container>
</template>

<script>
import axios from "axios";
import { ability } from "../../ability.js";
import { EyeIcon, EditIcon, TrashIcon } from "vue-tabler-icons";

export default {
  components: { EyeIcon, EditIcon, TrashIcon },
  data() {
    return {
      headers: [
        { title: "Name", key: "name" },
        { title: "Category", key: "category.name" },
        { title: "Sub Category", key: "sub_category.name" },
        { title: "Quantity", key: "quantity" },
        { title: "Status", key: "status" },
        { title: "Price", key: "discount_unit_price" },
        { title: "Actions", key: "actions", sortable: false, align: "end" },
      ],
      loader: false,
      page: 1,
      per_page: 25,
      totalItems: 0,
      search: "",
      product_list: [],
      totalPages: 1,
      confirmDeleteDialog: false,
      product_info: null,
      ability: ability,
      category_id: "",
      sub_category_id: "",
      category_list: [],
      sub_category_list: [],
      user_id: "",
      user_list: [],
    };
  },

  watch: {
    page(newVal) {
      this.getProductList();
    },
    per_row(newVal) {
      this.getProductList();
    },
    category_id(newVal) {
      this.subCategoryList();
      this.getProductList();
    },
    sub_category_id(newVal) {
      this.getProductList();
    },
    user_id(newVal) {
      this.getProductList();
    },
  },

  created() {
    this.getCategoryList();
    this.dropdownUserList();
    this.getProductList();
  },

  methods: {
    async getCategoryList() {
      await axios
        .post("/api/dropdown-category-list")
        .then((response) => {
          this.category_list = response.data.data;
          this.category_list = [{ id: "", name: "Select All" }, ...this.category_list];
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

    async dropdownUserList() {
      await axios
        .post("/api/dropdown-user-list")
        .then((response) => {
          this.user_list = response.data.data;
          this.user_list = [{ id: "", name: "Select All" }, ...this.user_list];
        })
        .catch((error) => {
          this.user_list = [];
          let message = error.response
            ? error.response.data.message
            : "Something went wrong!";
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

    refresh() {
      this.page = 1;
      this.per_page = 25;
      this.category_id = "";
      this.sub_category_id = "";
      this.user_id = "";
      this.onClear();
    },

    onClear() {
      this.search = "";
      this.getProductList();
    },

    async getProductList() {
      this.loader = true;
      const params = {
        search: this.search,
        page: this.page,
        perPage: this.per_page,
        category_id: this.category_id,
        sub_category_id: this.sub_category_id,
        user_id: this.user_id,
      };

      await axios
        .post("/api/product-list", params)
        .then((response) => {
          this.product_list = response.data.data.data;
          this.totalItems = response.data.data.total;
        })
        .catch((error) => {
          let message = error.response
            ? error.response.data.message
            : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
      this.loader = false;
    },

    viewProductDetail(product) {
      this.$router.push({ name: "product.view", params: { id: product.id } });
    },

    openCreateDialog() {
      this.$router.push({ name: "product.create" });
    },

    openEditDialog(product) {
      this.$router.push({ name: "product.edit", params: { id: product.id } });
    },

    async toggleProductStatus(item) {
      await axios.post('/api/product-update-status', {
        product_id: item.id,
        status: item.status
      }).then((response) => {
        item.status = item.status;
        this.$store.dispatch("globalState/successSnackBar", response.data.message);
      }).catch((error) => {
        let message = error.response ? error.response.data.message : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      });
    },

    // delete User Function
    confirmDelete(product) {
      this.product_info = product;
      this.confirmDeleteDialog = true;
    },

    closeConfirmDeleteDialog() {
      this.confirmDeleteDialog = false;
      this.product_info = null;
    },

    async deleteProduct() {
      const params = {
        id: this.product_info.id,
      };
      await axios
        .post("/api/product-delete", params)
        .then((response) => {
          this.closeConfirmDeleteDialog();
        })
        .catch((error) => {
          let message = error.response ? error.response.data.message : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },
  },
};
</script>
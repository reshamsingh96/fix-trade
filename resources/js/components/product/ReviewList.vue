<template>
  <v-progress-linear color="primary" indeterminate v-if="review_loader"></v-progress-linear>
  <v-container class="mt-3 my-2" fluid>
    <!-- Review Product List -->
    <v-card elevation="10">
      <v-row class="mx-1" align="center">
        <v-col cols="6" sm="6" md="4">
          <h4 class="text-h4">Review List</h4>
        </v-col>

        <v-col order="2" order-md="1" cols="12" sm="12" md="4">
          <v-text-field v-model="review_search" label="Search" hide-details density="compact" clearable
            variant="outlined" @click:clear="reviewOnClear()" @keyup="productReviewList()"></v-text-field>
        </v-col>

        <v-col order="1" order-md="2" class="text-end" cols="6" sm="6" md="4">
          <v-icon @click="productReviewList()" class="cursor-pointer">mdi-refresh</v-icon>
        </v-col>
      </v-row>

      <v-data-table-server v-model:items-per-page="review_per_row" :headers="headers" :items="review_list"
        :items-length="review_total" item-value="name" @update:options="productReviewList()">

        <!-- Sr No Slot -->
        <template v-slot:item.sr_no="{ item ,index}">
          {{ (review_page - 1) * review_per_row + index + 1 }}
        </template>

        <!-- User Name Slot -->
        <template v-slot:item.user_name="{ item }" v-if="product_id">
          <v-avatar color="blue" size="36" class="mr-2">
            <v-img v-if="item.user && item.user.image_url" :src="item.user.image_url"></v-img>
            <span v-else class="white--text">{{ nameInitial(item.user.name) }}</span>
          </v-avatar>
          {{ item.user.name }}
        </template>

        <!-- Product Name Slot -->
        <template v-slot:item.product_name="{ item }" v-if="user_id">
          <v-avatar color="blue" size="36" class="mr-2">
            <v-img v-if="item.product.image" :src="item.product.image.image_url"></v-img>
            <span v-else class="white--text">{{ nameInitial(item.product.name) }}</span>
          </v-avatar>
          {{ item.product.name }}
        </template>
        <template v-slot:item.rating="{ item }" >
        <v-rating v-model="item.rating" color="warning" half-increments size="small" readonly density="compact"></v-rating>
        </template>

        <!-- Actions Slot -->
        <template v-slot:item.actions="{ item }">
          <v-btn @click="confirmDelete(item)" size="small" variant="text" color="error" icon>
            <TrashIcon />
          </v-btn>
        </template>
      </v-data-table-server>
    </v-card>
  </v-container>
  <div class="ml-5">
    <!-- Confirm Delete Dialog -->
    <v-dialog v-model="confirmDeleteDialog" max-width="500" persistent scrollable>
      <v-card>
        <v-card-title>Confirm Delete</v-card-title>
        <v-card-text>Are you sure you want to delete this Review and Rating?</v-card-text>
        <v-card-actions class="d-flex justify-center">
          <v-btn color="error" @click="closeConfirmDialog()">Cancel</v-btn>
          <v-btn @click="deleteReview()" color="primary">Yes</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import axios from "axios";
import { ability } from "../../ability.js";
import { EyeIcon, EditIcon, TrashIcon } from "vue-tabler-icons";
import { mapState } from "vuex";

export default {
  components: { EyeIcon, EditIcon, TrashIcon },
  data() {
    return {
      ability: ability,
      headers: [
        { title: "No.", value: "sr_no" },
        { title: "User Name", value: "user_name" },
        { title: "Product name", value: "product_name" },
        { title: "Review", value: "review" },
        { title: "Rating", value: "rating" },
        { title: "Actions", value: "actions", sortable: false, align: "end" },
      ],
      user_id: null,
      product_id: null,
      review_loader: false,
      review_page: 1,
      review_per_row: 25,
      review_search: '',
      review_list: [],
      review_total: 1,
      review_info: null,
      confirmDeleteDialog: false,
    };
  },

  computed: {
    ...mapState({
      user: (state) => state.login.user,
    }),
  },

  async mounted() {
    const path = window.location.pathname;
    if (path.includes('/admin/profile') || path.includes('/admin/user')) {
      this.user_id = this.$route.params.uuid;
      await this.productReviewList();
    }else if(path.includes('/my-account')){
      this.user_id = this.user.uuid;
      await this.productReviewList();
    }
    if (path.includes('/admin/product')) {
      this.product_id = this.$route.params.id;
      await this.productReviewList();
    } 
    this.filterHeadersAndItems();
  },

  watch: {
    review_page(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.productReviewList();
      }
    },

    review_per_row(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.productReviewList();
      }
    },
  },

  methods: {
    filterHeadersAndItems() {
      if (this.product_id) { this.headers = this.headers.filter(header => header.value !== 'product_name'); }
      if (this.user_id) { this.headers = this.headers.filter(header => header.value !== 'user_name'); }
    },
    nameInitial(name) {
      if (!name) return '';
      const nameParts = name.trim().split(' ');
      const firstInitial = nameParts[0] ? nameParts[0][0].toUpperCase() : '';
      const secondInitial = nameParts[1] ? nameParts[1][0].toUpperCase() : '';
      return `${firstInitial}${secondInitial}`;
    },
    reviewRefresh() {
      this.review_page = 1;
      this.review_per_row = 25;
      this.reviewOnClear();
    },

    reviewOnClear() {
      this.review_search = '';
      this.productReviewList();
    },

    async productReviewList() {
      if(!this.user_id && !this.product_id) return;
      if(this.review_loader) return;
      this.review_loader = true;
      const params = {
        search: this.review_search,
        page: this.review_page,
        perPage: this.review_per_row,
        user_id: this.user_id,
        product_id: this.product_id,
      };

      await axios.post('/api/product-review-list', params).then(response => {
        this.review_list = response.data.data.data;
        this.review_total = response.data.data.last_page;
        this.review_loader = false;
      }).catch(error => {
        this.review_loader = false;
        let message = error.response ? error.response.data.message : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      });
    },

    // delete review Function
    confirmDelete(review) {
      this.review_info = review;
      this.confirmDeleteDialog = true;
    },

    closeConfirmDialog() {
      this.confirmDeleteDialog = false;
      this.review_info = null;
    },

    async deleteReview() {
      const params = {
        id: this.review_info.id,
      };
      await axios.post('/api/product-review-delete', params)
        .then(response => {
          this.productReviewList();
          this.closeConfirmDialog();
        })
        .catch(error => {
          let message = error.response ? error.response.data.message : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },
  },
};
</script>
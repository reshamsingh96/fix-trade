<template>
  <v-progress-linear color="primary" indeterminate v-if="bookmark_loader"></v-progress-linear>
  <v-container class="mt-3 my-2" fluid>
    <!-- Bookmark Product List -->
    <v-card elevation="10">
      <v-row class="mx-1" align="center">
        <v-col cols="6" sm="6" md="4">
          <h4 class="text-h4">Bookmark List</h4>
        </v-col>

        <v-col order="2" order-md="1" cols="12" sm="12" md="4">
          <v-text-field v-model="bookmark_search" label="Search" hide-details density="compact" clearable
            variant="outlined" @click:clear="bookmarkOnClear" @keyup="productBookmarkList"></v-text-field>
        </v-col>

        <v-col order="1" order-md="2" class="text-end" cols="6" sm="6" md="4">
          <v-icon @click="productBookmarkList" class="cursor-pointer">mdi-refresh</v-icon>
        </v-col>
      </v-row>

      <v-data-table-server v-model:items-per-page="bookmark_per_row" :headers="headers" :items="bookmark_list"
        :items-length="bookmark_total" item-value="name" @update:options="productBookmarkList">

        <!-- Sr No Slot -->
        <template v-slot:item.sr_no="{ item, index }">
          {{ (bookmark_page - 1) * bookmark_per_row + index + 1 }}
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

        <!-- Bookmark Slot -->
        <template v-slot:item.is_bookmark="{ item }">
          <v-icon :color="item.is_bookmark ? 'primary' : ''">
            {{ item.is_bookmark ? 'mdi-bookmark' : 'mdi-bookmark-outline' }}
          </v-icon>
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
        <v-card-text>Are you sure you want to delete this Bookmark?</v-card-text>
        <v-card-actions class="d-flex justify-center">
          <v-btn color="error" @click="closeConfirmDialog()">Cancel</v-btn>
          <v-btn @click="deleteBookmark()" color="primary">Yes</v-btn>
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
      headers: [
        { title: "No.", value: "sr_no" },
        { title: "User Name", value: "user_name" },
        { title: "Product name", value: "product_name" },
        { title: "Bookmark", value: "is_bookmark" },
        { title: "Actions", value: "actions", sortable: false, align: "end" },
      ],
      user_id: null,
      product_id: null,
      bookmark_loader: false,
      bookmark_page: 1,
      bookmark_per_row: 25,
      bookmark_search: '',
      bookmark_list: [],
      bookmark_total: 1,
      bookmark_info: null,
      confirmDeleteDialog: false,
      ability: ability,
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
      await this.productBookmarkList();
    }else if(path.includes('/my-account')){
      this.user_id = this.user.uuid;
      await this.productBookmarkList();
    }
    if (path.includes('/admin/product')) {
      this.product_id = this.$route.params.id;
      await this.productBookmarkList();
    } 
    this.filterHeadersAndItems();

  },

  watch: {
    bookmark_page(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.productBookmarkList();
      }
    },

    bookmark_per_row(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.productBookmarkList();
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
      bookmarkRefresh() {
        this.bookmark_page = 1;
        this.bookmark_per_row = 25;
        this.bookmarkOnClear();
      },

      bookmarkOnClear() {
        this.bookmark_search = '';
        this.productBookmarkList();
      },

      async productBookmarkList() {
      if(!this.user_id && !this.product_id) return;
      if(this.bookmark_loader) return;
        this.bookmark_loader = true;
        const params = {
          search: this.bookmark_search,
          page: this.bookmark_page,
          perPage: this.bookmark_per_row,
          user_id: this.user_id,
          product_id: this.product_id,
          type: "web_list",
        };

        await axios.post('/api/product-bookmark-list', params).then(response => {
          this.bookmark_list = response.data.data.data;
          this.bookmark_total = response.data.data.last_page;
          this.bookmark_loader = false;
        }).catch(error => {
          this.bookmark_loader = false;
          let message = error.response ? error.response.data.message : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
      },
      // delete bookmark Function
      confirmDelete(bookmark) {
        this.bookmark_info = bookmark;
        this.confirmDeleteDialog = true;
      },

      closeConfirmDialog() {
        this.confirmDeleteDialog = false;
        this.bookmark_info = null;
      },

      async deleteBookmark() {
        const params = {
          id: this.bookmark_info.id,
        };
        await axios.post('/api/product-bookmark-delete', params)
          .then(response => {
            this.productBookmarkList();
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
<template>
  <v-progress-linear
    color="primary"
    indeterminate
    v-if="loader"
  ></v-progress-linear>

  <v-container class="mt-3 my-2" fluid>
    <v-card elevation="10">
      <v-row class="mx-1" align="center">
        <v-col cols="6" sm="6" md="6" lg="6">
          <h4 class="text-h4">User List</h4>
        </v-col>
        <v-col class="text-end" cols="6" sm="6" md="6">
          <v-btn
            @click="openCreateDialog"
            color="primary"
            variant="flat"
            class="mr-2"
            >Add User</v-btn
          >
          <v-icon @click="refresh" class="cursor-pointer">mdi-refresh</v-icon>
        </v-col>
      </v-row>

      <v-row class="mx-1 mb-1">
        <v-col order="2" order-md="1" cols="12" sm="6" md="4" lg="3">
          <v-text-field
            v-model="searchQuery"
            label="Search"
            hide-details
            clearable
            density="compact"
            variant="outlined"
            @click:clear="onClear"
            @keyup="getUserList"
          ></v-text-field>
        </v-col>

        <v-col order="2" order-md="1" cols="12" sm="6" md="3" lg="2">
          <v-autocomplete
            hide-details
            v-model="status"
            density="compact"
            variant="outlined"
            :items="status_list"
            @input="getUserList"
            placeholder="Select Status"
          ></v-autocomplete>
        </v-col>
      </v-row>

      <v-data-table-server
        v-model:items-per-page="per_page"
        :headers="headers"
        :items="users"
        :items-length="totalItems"
        item-value="name"
        @update:options="getUserList"
      >
        <template v-slot:item.name="{ item }">
          <v-avatar color="primary" size="36" class="mr-2 avatar-img-cover">
            <v-img v-if="item.image_url" :src="item.image_url"></v-img>
            <span v-else class="white--text">{{
              userNameInitial(item.name)
            }}</span>
          </v-avatar>
          {{ item.name }}
        </template>

        <template v-slot:item.account_type="{ item }">
          <p>{{ item.account_type }} </p>
        </template>

        <template v-slot:item.phone="{ item }">
          <p>+{{ item.phone_code }} {{ item.phone }}</p>
        </template>

        <template v-slot:item.status="{ item }">
          <v-switch v-model="item.status"  false-value="In-Active" true-value="Active" hide-details
            color="primary" @change="() => toggleUserStatus(item)"></v-switch>
        </template>

        <template v-slot:item.actions="{ item }">
          <v-btn
            @click="viewUserDetail(item)"
            color="secondary"
            size="small"
            variant="text"
            icon
          >
            <EyeIcon />
          </v-btn>

          <v-btn @click="openEditDialog(item)" size="small" variant="text" icon>
            <EditIcon />
          </v-btn>

          <v-btn
            @click="confirmDelete(item)"
            size="small"
            variant="text"
            color="error"
            icon
          >
            <TrashIcon />
          </v-btn>
        </template>
      </v-data-table-server>
    </v-card>
  </v-container>

  <div class="ml-5">
    <!-- Create User Dialog -->
    <v-dialog v-model="createDialog" max-width="600" persistent scrollable>
      <Create :type="'create_user'" @closeCreateDialog="closeCreateDialog" />
    </v-dialog>

    <!-- Edit User Dialog -->
    <v-dialog v-model="editDialog" max-width="600" persistent scrollable>
      <Create
        :type="'update_user'"
        :user_info="selectedUser"
        :user_id="selectedUser ? selectedUser.uuid : null"
        @closeEditDialog="closeEditDialog"
      />
    </v-dialog>

    <!-- Confirm Delete Dialog -->
    <v-dialog
      v-model="confirmDeleteDialog"
      max-width="500"
      persistent
      scrollable
    >
      <v-card>
        <v-card-title>Confirm Delete</v-card-title>
        <v-card-text>Are you sure you want to delete this user?</v-card-text>
        <v-card-actions class="d-flex justify-center">
          <v-btn @click="closeConfirmDeleteDialog">Cancel</v-btn>
          <v-btn @click="deleteUser" color="error">Yes</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import axios from "axios";
import { ability } from "../../ability.js";
import Create from "./create.vue";
import { EyeIcon, EditIcon, TrashIcon } from "vue-tabler-icons";

export default {
  components: { Create, EyeIcon, EditIcon, TrashIcon },
  data() {
    return {
      status: "All",
      status_list: ["All", "Active", "In-Active"],
      headers: [
        { title: "Full Name", key: "name" },
        { title: "Email", key: "email" },
        { title: "Phone", key: "phone" },
        { title: "Type", key: "account_type" },
        { title: "Status", key: "status" },
        { title: "Actions", key: "actions", sortable: false, align: "end" },
      ],
      delete_type: "",
      loader: false,
      page: 1,
      per_page: 25,
      searchQuery: "",
      totalItems: 0,
      users: [],
      createDialog: false,
      editDialog: false,
      confirmDeleteDialog: false,
      selectedUser: null,
      ability: ability,
    };
  },

  watch: {
    page(newVal) {
      this.getUserList();
    },
    per_row(newVal) {
      this.getUserList();
    },
    status(newVal) {
      this.getUserList();
    },
  },

  created() {
    this.getUserList();
  },

  methods: {
    userNameInitial(name) {
      if (!name) return "";
      const nameParts = name.trim().split(" ");
      const firstInitial = nameParts[0] ? nameParts[0][0].toUpperCase() : "";
      const secondInitial = nameParts[1] ? nameParts[1][0].toUpperCase() : "";
      return `${firstInitial}${secondInitial}`;
    },

    refresh() {
      this.page = 1;
      this.per_page = 25;
      this.status = "All";
      this.onClear();
    },

    onClear() {
      this.searchQuery = "";
      this.getUserList();
    },

    async getUserList() {
      this.loader = true;
      const params = {
        search: this.searchQuery,
        page: this.page,
        perPage: this.per_page,
        status: this.status,
      };

      await axios
        .post("/api/user-list", params)
        .then((response) => {
          this.users = response.data.data.data;
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

    viewUserDetail(user) {
      this.$router.push({ name: "user.view", params: { uuid: user.uuid } });
    },

    // Create User Function
    openCreateDialog() {
      this.createDialog = true;
    },

    closeCreateDialog() {
      this.createDialog = false;
      this.getUserList();
    },

    // update User Function
    openEditDialog(user) {
      this.selectedUser = user;
      this.editDialog = true;
    },

    closeEditDialog() {
      this.editDialog = false;
      this.selectedUser = null;
      this.getUserList();
    },

    async toggleUserStatus(item) {
      await axios.post('/api/user-update-status', {
        user_id: item.uuid,
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
    confirmDelete(user, type) {
      this.selectedUser = user;
      this.delete_type = type;
      this.confirmDeleteDialog = true;
    },

    closeConfirmDeleteDialog() {
      this.confirmDeleteDialog = false;
      this.selectedUser = null;
      this.delete_type = "Delete";
    },

    async deleteUser() {
      const params = {
        user_id: this.selectedUser.uuid,
        delete_type: this.delete_type,
      };
      await axios
        .post("/api/user-delete", params)
        .then((response) => {
          this.getUserList();
          this.closeConfirmDeleteDialog();
        })
        .catch((error) => {
          let message = error.response
            ? error.response.data.message
            : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },
  },
};
</script>
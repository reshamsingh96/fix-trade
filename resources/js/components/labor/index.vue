<template>
  <v-progress-linear
    color="primary"
    indeterminate
    v-if="loader"
  ></v-progress-linear>

  <v-container class="mt-3 my-2" fluid>
    <v-card elevation="10">
      <v-row class="mx-1" align="center">
        <v-col cols="6" sm="6" md="4"> 
          <h4 class="text-h4">Labour</h4>
        </v-col>

        <v-col order="2" order-md="1" cols="12" sm="12" md="4">
          <v-text-field
            v-model="search"
            label="Search"
            hide-details
            density="compact"
            clearable
            variant="outlined"
            @click:clear="onClear"
            @keyup="getLaborList()"
          ></v-text-field>
        </v-col>

        <v-col order="1" order-md="2" class="text-end" cols="6" sm="6" md="4">
          <v-btn
            @click="openCreateDialog"
            color="primary"
            variant="flat"
            class="mr-2"
            >Add User</v-btn
          >
          <v-icon @click="getLaborList" class="cursor-pointer"
            >mdi-refresh</v-icon
          >
        </v-col>
      </v-row>

      <v-data-table-server
        v-model:items-per-page="per_page"
        :headers="headers"
        :items="labor_list"
        :items-length="totalItems"
        item-value="name"
        @update:options="getUserList"
      >
        <template v-slot:item.name="{ item }">
          <v-avatar color="primary" size="36" class="mr-2 avatar-img-cover">
            <v-img v-if="item.image_url" :src="item.image_url"></v-img>
            <span v-else class="white--text">{{
              nameInitial(item.labor_name)
            }}</span>
          </v-avatar>
          {{ item.labor_name }}
        </template>

        <template v-slot:item.status="{ item }">
          <v-switch v-model="item.status"  false-value="In-Active" true-value="Active" hide-details
            color="primary" @change="() => toggleLabourStatus(item)"></v-switch>
        </template>

        <template v-slot:item.actions="{ item }">
          <v-btn
            @click="viewLaborDetail(item)"
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
        { title: "Work", key: "work_title" },
        { title: "Phone", key: "phone" },
        { title: "Status", key: "status" },
        { title: "Actions", key: "actions", sortable: false, align: "end" },
      ],
      loader: false,
      page: 1,
      per_page: 25,
      search: "",
      labor_list: [],
      totalItems: 0,
      confirmDeleteDialog: false,
      labor_info: null,
      ability: ability,
      user_id: null,
    };
  },

  async mounted() {
    if ((this.$route.name == "user.view" || this.$route.name === "profile") && this.$route.params.uuid) {
      this.user_id = this.$route.params.uuid;
    } else {
      this.user_id = null;
    }
    await this.getLaborList();
  },

  watch: {
    per_page(newVal) {
      this.getLaborList();
    },
    page(newVal) {
      this.getLaborList();
    },
  },

  methods: {
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
      this.onClear();
    },

    onClear() {
      this.search = "";
      this.getLaborList();
    },

    async getLaborList() {
      this.loader = true;
      const params = {
        search: this.search,
        page: this.page,
        perPage: this.per_page,
        user_id: this.user_id,
      };

      await axios
        .post("/api/labor-list", params)
        .then((response) => {
          this.labor_list = response.data.data.data;
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

    viewLaborDetail(labor) {
      this.$router.push({ name: "labor.view", params: { id: labor.id } });
    },

    openCreateDialog() {
      this.$router.push({ name: "labor.create" });
    },

    openEditDialog(labor) {
      this.$router.push({ name: "labor.edit", params: { id: labor.id } });
    },

    async toggleLabourStatus(item) {
      await axios.post('/api/labour-update-status', {
        labour_id: item.id,
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
      this.labor_info = product;
      this.confirmDeleteDialog = true;
    },

    closeConfirmDeleteDialog() {
      this.confirmDeleteDialog = false;
      this.labor_info = null;
    },

    async deleteLabor() {
      const params = {
        id: this.labor_info.id,
      };
      await axios
        .post("/api/labor-delete", params)
        .then((response) => {
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
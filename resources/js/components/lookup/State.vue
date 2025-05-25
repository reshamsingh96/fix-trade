<template>
  <v-progress-linear color="primary" indeterminate v-if="loader"></v-progress-linear>
  <v-container class="mt-3 my-2" fluid>
    <v-card elevation="10">
      <!-- Header Row -->
      <v-row class="mx-1" align="center">
        <v-col cols="6" sm="6" md="6">
          <h4 class="text-h4">States</h4>
        </v-col>

        <v-col class="text-end" cols="12" sm="12" md="6">
          <v-btn @click="openCommonDialog(null)" color="primary" class="mr-2">Add</v-btn>
          <v-icon @click="getList" class="cursor-pointer">mdi-refresh</v-icon>
        </v-col>
      </v-row>

      <v-row class="mx-1 mb-1">
        <v-col cols="12" sm="4" md="3" lg="2">
          <v-text-field v-model="search" label="Search" hide-details density="compact" clearable variant="outlined"
            @click:clear="onClear" @keyup="getList"></v-text-field>
        </v-col>

        <v-col cols="12" sm="4" md="3" lg="2">
          <v-autocomplete v-model="country_id" :items="countries" hide-details readonly label="Country"
            variant="outlined" density="compact" item-title="name" item-value="id" :clearable="false">
          </v-autocomplete>
        </v-col>
      </v-row>

      <v-data-table-server v-model:items-per-page="per_row" :headers="headers" :items="List" :items-length="totalPages"
        item-value="name" @update:options="getList">
        <!-- Sr No Slot -->
        <template v-slot:item.sr_no="{ item, index }">
          {{ (page - 1) * per_row + index + 1 }}
        </template>
        <!-- Actions Slot -->
        <template v-slot:item.actions="{ item }">
          <v-btn @click="openCommonDialog(item)" size="small" variant="text" icon>
            <EditIcon />
          </v-btn>

          <v-btn v-if="item.state_type == 'yes'" @click="confirmDelete(item)" size="small" variant="text" color="error"
            icon>
            <TrashIcon />
          </v-btn>
        </template>
      </v-data-table-server>
    </v-card>
  </v-container>

  <div class="ml-5">
    <!-- Create User Dialog -->
    <v-dialog v-model="commonDialog" max-width="500" persistent scrollable>
      <v-card class="mx-auto p-3" elevation="2">
        <v-card-title>
          <h3>{{ !selected_info ? "State Create" : "State Edit" }}</h3>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <v-row>
            <v-col cols="12">
              <v-autocomplete v-model="form.country_id" :items="countries" label="Country" variant="outlined"
                density="compact" readonly item-title="name" item-value="id" :error-messages="v$.form.country_id.$errors.map((e) => e.$message)
                  " @blur="v$.form.country_id.$touch()">
              </v-autocomplete>
            </v-col>

            <!-- Name Field -->
            <v-col cols="12">
              <v-text-field label="Name" variant="outlined" v-model="form.name" density="compact"
                :error-messages="v$.form.name.$errors.map((e) => e.$message)" @blur="v$.form.name.$touch()"
                :rules="[(v) => !!v || 'Name is required']">
              </v-text-field>
            </v-col>

            <!-- State Code Field -->
            <v-col cols="12">
              <v-text-field label="State Code" variant="outlined" v-model="form.state_code" density="compact"
                :error-messages="v$.form.state_code.$errors.map((e) => e.$message)
                  " @blur="v$.form.state_code.$touch()" :rules="[(v) => !!v || 'State Code is required']">
              </v-text-field>
            </v-col>

            <!-- Latitude Field -->
            <v-col cols="12">
              <v-text-field label="Latitude" variant="outlined" v-model="form.latitude" density="compact"
                :error-messages="v$.form.latitude.$errors.map((e) => e.$message)
                  " @blur="v$.form.latitude.$touch()" :rules="[(v) => !!v || 'Latitude is required']">
              </v-text-field>
            </v-col>

            <!-- Longitude Field -->
            <v-col cols="12">
              <v-text-field label="Longitude" variant="outlined" v-model="form.longitude" density="compact"
                :error-messages="v$.form.longitude.$errors.map((e) => e.$message)
                  " @blur="v$.form.longitude.$touch()" :rules="[(v) => !!v || 'Longitude is required']">
              </v-text-field>
            </v-col>
            <!-- State Type -->
            <v-col cols="12">
              <v-select label="State Type" :items="state_types" v-model="form.type" variant="outlined" density="compact"
                :error-messages="v$.form.type.$errors.map((e) => e.$message)" @blur="v$.form.type.$touch()"></v-select>
            </v-col>
            <!-- State Type Field -->
            <v-col cols="12">
              <v-select label="Delete Type" :items="['yes', 'no']" v-model="form.state_type" variant="outlined"
                density="compact" :error-messages="v$.form.state_type.$errors.map((e) => e.$message)
                  " @blur="v$.form.state_type.$touch()"></v-select>
            </v-col>
          </v-row>
        </v-card-text>

        <!-- Divider -->
        <v-divider></v-divider>

        <!-- Actions Section -->
        <v-card-actions class="d-flex justify-end">
          <v-btn class="mx-2" :loading="loader" :disabled="loader" @click="submitForm()">
            {{ selected_info ? "Update" : "Save" }}
          </v-btn>
          <v-btn color="error" class="mx-2" @click="closeCommonDialog()">Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Confirm Delete Dialog -->
    <v-dialog v-model="confirmDeleteDialog" max-width="500" persistent scrollable>
      <v-card>
        <v-card-title>Confirm Delete</v-card-title>
        <v-card-text>Are you sure you want to delete this info?</v-card-text>
        <v-card-actions class="d-flex justify-center">
          <v-btn @click="closeConfirmDialog">Cancel</v-btn>
          <v-btn @click="deleteInfo" color="error">Yes</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import axios from "axios";
import useVuelidate from "@vuelidate/core";
import { required, minLength } from "@vuelidate/validators";
import { ability } from "../../ability.js";
import { EyeIcon, EditIcon, TrashIcon } from "vue-tabler-icons";

export default {
  components: { EyeIcon, EditIcon, TrashIcon },
  setup() {
    const v$ = useVuelidate();
    return { v$ };
  },
  data() {
    return {
      loader: false,
      page: 1,
      per_row: 25,
      search: "",
      headers: [
        { title: "No.", key: "sr_no" },
        { title: "name", key: "name" },
        { title: "State Code", key: "state_code" },
        { title: "Country", key: "country_name" },
        { title: "Latitude", key: "latitude" },
        { title: "Longitude", key: "longitude" },
        { title: "Actions", key: "actions", sortable: false, align: "end" },
      ],
      List: [],
      totalPages: 1,
      commonDialog: false,
      confirmDeleteDialog: false,
      selected_info: null,
      ability: ability,
      country_id: null,
      countries: [],
      state_types: ["state", "Union territory"],
      form: {
        id: null,
        name: "",
        country_id: null,
        state_code: "",
        type: "state",
        latitude: "",
        longitude: "",
        state_type: "yes",
      },
    };
  },

  validations() {
    const isLatitudeValid = (value) => {
      const lat = parseFloat(value);
      return lat >= -90 && lat <= 90;
    };

    const isLongitudeValid = (value) => {
      const long = parseFloat(value);
      return long >= -180 && long <= 180;
    };

    const isAlphaLength = (length) => (value) => {
      return (
        (/^[A-Za-z]+$/.test(value) && value.length === length) ||
        `Must be ${length} alphabetic characters`
      );
    };

    return {
      form: {
        name: { required },
        country_id: { required },
        state_code: { required, isAlphaLength: isAlphaLength(2) },
        type: { required },
        state_type: { required },
        latitude: {
          required,
          isLatitudeValid: {
            $validator: isLatitudeValid,
            $message: "Latitude must be between -90 and 90",
          },
        },
        longitude: {
          required,
          isLongitudeValid: {
            $validator: isLongitudeValid,
            $message: "Longitude must be between -180 and 180",
          },
        },
      },
    };
  },

  async mounted() {
    await this.fetchCountries();
  },

  watch: {
    country_id(newVal, oldVal) {
      if (newVal !== oldVal) { this.getList(); }
    },
    
    per_row(newVal, oldVal) {
      if (newVal !== oldVal) { this.getList(); }
    },

    page(newVal, oldVal) {
      if (newVal !== oldVal) { this.getList(); }
    },
  },

  methods: {
    async fetchCountries() {
      try {
        const response = await axios.post("/api/dropdown-country-list");
        this.countries = response.data.data;
        this.selectedIndia();
      } catch (error) {
        let message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
    },

    selectedIndia() {
      const india = this.countries.find(
        (country) => country.name.toLowerCase() === "india"
      );
      if (india) {
        this.country_id = india.id;
        this.form.country_id = india.id;
      }
    },

    refresh() {
      this.page = 1;
      this.per_row = 25;
      this.onClear();
    },

    onClear() {
      this.search = "";
      this.getList();
    },

    async getList() {
      this.loader = true;
      const params = {
        search: this.search,
        page: this.page,
        perPage: this.per_row,
        country_id: this.country_id,
      };

      await axios
        .post("/api/state-list", params)
        .then((response) => {
          this.List = response.data.data.data;
          this.totalPages = response.data.data.last_page;
        })
        .catch((error) => {
          let message = error.response
            ? error.response.data.message
            : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
      this.loader = false;
    },

    // Create User Function
    openCommonDialog(info = null) {
      if (info) {
        this.selected_info = info;
        this.form = {
          id: info.id,
          name: info.name,
          country_id: info.country_id,
          state_code: info.state_code,
          type: info.type,
          latitude: info.latitude,
          longitude: info.longitude,
          state_type: info.state_type,
        };
      } else {
        this.selected_info = null;
        this.form = {
          id: null,
          name: "",
          state_code: "",
          type: "state",
          latitude: "",
          longitude: "",
          state_type: "yes",
        };
        this.v$.$reset();
        this.selectedIndia();
      }
      this.commonDialog = true;
    },

    closeCommonDialog() {
      this.commonDialog = false;
    },

    async submitForm() {
      this.v$.$touch();
      if (this.v$.$invalid) {
        this.$store.dispatch("globalState/errorSnackBar", "Form is invalid!");
        return;
      }

      this.loader = true;
      const formData = new FormData();
      formData.append("id", this.form.id);
      formData.append("name", this.form.name);
      formData.append("country_id", this.form.country_id);
      formData.append("state_code", this.form.state_code);
      formData.append("type", this.form.type);
      formData.append("latitude", this.form.latitude);
      formData.append("longitude", this.form.longitude);
      formData.append("state_type", this.form.state_type);
      const url = this.selected_info
        ? "/api/state-update"
        : "/api/state-create";
      await axios
        .post(url, formData, {
          headers: { "Content-Type": "multipart/form-data" },
        })
        .then((response) => {
          this.$store.dispatch(
            "globalState/successSnackBar",
            response.data.message
          );
          this.loader = false;
          this.getList();
          this.commonDialog = false;
        })
        .catch((error) => {
          this.loader = false;
          let message = error.response
            ? error.response.data.message
            : "Something went wrong!";
          this.$store.dispatch("globalState/errorSnackBar", message);
        });
    },

    // delete User Function
    confirmDelete(user) {
      this.selected_info = user;
      this.confirmDeleteDialog = true;
    },

    closeConfirmDialog() {
      this.confirmDeleteDialog = false;
      this.selected_info = null;
    },

    async deleteInfo() {
      const params = {
        id: this.selected_info.id,
      };
      await axios
        .post("/api/state-delete", params)
        .then((response) => {
          this.getList();
          this.closeConfirmDialog();
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
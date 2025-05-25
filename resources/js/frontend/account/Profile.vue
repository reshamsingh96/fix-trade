<template>
  <v-progress-linear
    color="primary"
    indeterminate
    v-if="loader"
  ></v-progress-linear>
  <v-container class="mt-3 my-2" fluid v-if="user_info">
    <!-- User Info Card -->
    <v-row>
      <v-col cols="6" sm="6" md="4">
        <h4 class="text-h4">Profile Info</h4>
      </v-col>
      <v-col class="text-end" cols="6" sm="6" md="8">
        <v-btn
          class="mx-2"
          color="primary"
          prepend-icon="mdi-pencil"
          @click="updateDialog = true"
        >
          Edit
        </v-btn>
      </v-col>
    </v-row>

    <div class="d-flex align-center mb-4">
      <v-avatar class="mr-4" size="100">
        <v-img
          :src="user_info.image_url || '/images/profile_image.png'"
          alt="User Image"
        ></v-img>
      </v-avatar>

      <div>
        <h5 class="text-h5 mb-1">{{ user_info.name }}</h5>
        <p>{{ user_info.account_type }}</p>
      </div>
    </div>

    <h6 class="text-h6 mb-2">About</h6>

    <v-row>
      <v-col cols="12" sm="6" md="3">
        <div class="d-flex align-center">
          <MailIcon class="mr-2 flex-shrink-0" size="19" />
          <p class="text-subtitle-1 text-grey">
            Email: <span class="text-black">{{ user_info.email }}</span>
          </p>
        </div>
      </v-col>
      <v-col v-if="user_info.phone" cols="12" sm="6" md="3">
        <div class="d-flex align-center">
          <PhoneIcon class="mr-2 flex-shrink-0" size="19" />
          <p class="text-subtitle-1 text-grey">
            Phone:
            <span class="text-black"
              >+{{ user_info.phone_code }} {{ user_info.phone }}</span
            >
          </p>
        </div>
      </v-col>
      <v-col v-if="user_info.secondary_number" cols="12" sm="6" md="3">
        <div class="d-flex align-center">
          <MailIcon class="mr-2 flex-shrink-0" size="19" />
          <p class="text-subtitle-1 text-grey">
            Secondary Phone:
            <span class="text-black"
              >+{{ user_info.phone_code }}
              {{ user_info.secondary_number }}</span
            >
          </p>
        </div>
      </v-col>
      <v-col v-if="user_info.gender" cols="12" sm="6" md="3">
        <div class="d-flex align-center">
          <GenderMaleIcon class="mr-2 flex-shrink-0" size="19" />
          <p class="text-subtitle-1 text-grey">
            Gender:
            <span class="text-black">{{ user_info.gender }}</span>
          </p>
        </div>
      </v-col>
      <v-col v-if="user_info.date_of_birth" cols="12" sm="6" md="3">
        <div class="d-flex align-center">
          <CakeIcon class="mr-2 flex-shrink-0" size="19" />
          <p class="text-subtitle-1 text-grey">
            DOB:
            <span class="text-black">{{ user_info.date_of_birth }}</span>
          </p>
        </div>
      </v-col>
    </v-row>

    <v-divider class="my-4"></v-divider>

    <h6 class="text-h6 mb-3">Address</h6>

    <v-row v-for="address in user_info.user_address" :key="address.id">
      <v-col class="pb-0" v-if="address.full_address" cols="12" sm="12" md="12">
        <div class="d-flex align-center">
          <MapPinIcon class="mr-2 flex-shrink-0" size="19" />

          <p class="text-subtitle-1 text-grey">
            Address: <span class="text-black">{{ address.full_address }}</span>
          </p>
        </div>
      </v-col>
      <v-col class="pb-0" v-if="address.city.name" cols="12" sm="4" md="3">
        <div class="d-flex align-center">
          <BuildingSkyscraperIcon class="mr-2 flex-shrink-0" size="19" />

          <p class="text-subtitle-1 text-grey">
            City: <span class="text-black">{{ address.city.name }}</span>
          </p>
        </div>
      </v-col>
      <v-col class="pb-0" v-if="address.state.name" cols="12" sm="4" md="3">
        <div class="d-flex align-center">
          <MapIcon class="mr-2 flex-shrink-0" size="19" />

          <p class="text-subtitle-1 text-grey">
            State: <span class="text-black">{{ address.state.name }}</span>
          </p>
        </div>
      </v-col>

      <v-col class="pb-0" v-if="address.country.name" cols="12" sm="4" md="3">
        <div class="d-flex align-center">
          <WorldIcon class="mr-2 flex-shrink-0" size="19" />

          <p class="text-subtitle-1 text-grey">
            Country: <span class="text-black">{{ address.country.name }}</span>
          </p>
        </div>
      </v-col>
      <v-col class="pb-0" v-if="address.pin_code" cols="12" sm="4" md="3">
        <div class="d-flex align-center">
          <MapPinCodeIcon class="mr-2 flex-shrink-0" size="19" />

          <p class="text-subtitle-1 text-grey">
            Pincode: <span class="text-black">{{ address.pin_code }}</span>
          </p>
        </div>
      </v-col>
    </v-row>
    <br />

    <v-dialog v-model="updateDialog" max-width="700" persistent scrollable>
      <UpdateProfile
        :type="'update_user'"
        :user_info="user_info"
        :user_id="user_info ? user_info.uuid : null"
        @closeEditDialog="closeEditDialog"
      />
    </v-dialog>

    <br />
  </v-container>
</template>

<script>
import { mapState } from "vuex";
import axios from "axios";
import UpdateProfile from "../../components/user/create.vue";
import {
  BuildingSkyscraperIcon,
  CakeIcon,
  GenderMaleIcon,
  MailIcon,
  MapIcon,
  MapPinCodeIcon,
  MapPinIcon,
  PhoneIcon,
  WorldIcon,
} from "vue-tabler-icons";

export default {
  components: {
    UpdateProfile,
    PhoneIcon,
    MailIcon,
    GenderMaleIcon,
    CakeIcon,
    MapPinIcon,
    BuildingSkyscraperIcon,
    MapIcon,
    WorldIcon,
    MapPinCodeIcon,
  },
  data() {
    return {
      updateDialog: false,
      loader: false,
      user_info: null,
      user_id: null,
    };
  },

  computed: {
    ...mapState({
      user: (state) => state.login.user,
    }),
  },

  async mounted() {
    if (this.user) {
      this.user_id = this.user.uuid;
      await this.singleUserInfo();
    }
  },

  methods: {
    closeEditDialog() {
      this.updateDialog = false;
      this.singleUserInfo();
    },

    async singleUserInfo() {
      this.loader = true;
      try {
        const response = await axios.post("/api/single-user-info", {
          user_id: this.user.uuid,
        });
        this.user_info = response.data.data;
        localStorage.setItem("user_login", JSON.stringify(this.user_info));
      } catch (error) {
        const message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      } finally {
        this.loader = false;
      }
    },
  },
};
</script>
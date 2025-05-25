<template>
  <v-app class="verticalLayout">
    <Leftsiderbar :drawer="drawer" />

    <v-app-bar elevation="1" class="custom-background">
      <template v-slot:prepend>
        <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
      </template>
      <Header />
    </v-app-bar>

    <v-main>
      <router-view></router-view>
    </v-main>
    <!-- Snackbar dynamically bound to Vuex state -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="2000" top>
      {{ snackbar.message }}

      <template v-slot:actions>
        <v-btn color="white" variant="text" @click="snackbar.show = false">
          Close
        </v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>
<script>
import Leftsiderbar from '../components/navbar/Leftsiderbar.vue';
import Header from '../components/navbar/Header.vue';
import { mapState } from "vuex";
import Cookies from 'js-cookie';
import axios from 'axios';

export default {
  components: { Header, Leftsiderbar },
  data: () => ({
    drawer: null,
    permission_list: [],
  }),
  computed: {
    ...mapState('globalState', {
      snackbar: (state) => state.showSnackBar
    })
  },

  async mounted() {
    await this.userPermission();
  },

  methods: {
    async userPermission() {
      try {
        const response = await axios.post('/api/user-permission');
        this.permission_list = response.data.data;
        this.$store.dispatch("login/setPermissions", this.permission_list);
      } catch (error) {
        let message = error.response ? error.response.data.message : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
    },
  }
};
</script>

<style></style>
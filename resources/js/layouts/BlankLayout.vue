<template>
  <v-app>
    <router-view></router-view>

    <!-- Snackbar dynamically bound to Vuex state -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="2000"
      top
    >
      {{ snackbar.message }}

      <template v-slot:actions>
        <v-btn
          color="white"
          variant="text"
          @click="snackbar.show = false"
        >
          Close
        </v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script>
import { mapState } from "vuex";

export default {
  computed: {
    ...mapState('globalState', {
      snackbar: (state) => state.showSnackBar,  // Access snackbar state from Vuex
    })
  },
};
</script>

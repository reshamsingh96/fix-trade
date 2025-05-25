<template>
  <v-app>
    <Navbar />
    <v-main>
      <v-container class="my-container">
        <v-row justify="center">
          <v-col cols="12" sm="12" md="9">
            <div class="text-center mb-6">
              <h1 class="text-h1">Refund Policy</h1>
            </div>
          </v-col>
        </v-row>
      </v-container>
      <br /><br />
    </v-main>
    <Footer />
  </v-app>
</template>

<script>
import Navbar from "../common/Navbar.vue";
import Footer from "../common/Footer.vue";
export default {
  components: {
    Navbar,
    Footer,
  },
};
</script>

<style>
</style>
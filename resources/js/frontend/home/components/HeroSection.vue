<template>
  <section class="hero-section">
    <v-container class="my-container">
      <v-row>
        <v-col align-self="center" cols="12" sm="12" md="6">
          <p class="hero-subtitle">
            <HomeIcon size="20" />
            <span>Anaaj Culture</span>
          </p>
          <h2 class="h1 hero-title">Empowering Farmers' Marketplace</h2>

          <p class="hero-text">
            Discover the best options in Grains, Fertilizers, Seeds &
            Pesticides, Machinery, Animals, and Labour. Buy, sell, or rent
            products easily with trusted vendors. Connect with others in the
            farming community for reliable deals
          </p>

          <v-btn href="/contact-us" variant="flat" class="mt-5" size="large" color="primary">Make An Enquiry</v-btn>
        </v-col>

        <v-col cols="12" sm="12" md="6">
          <figure class="hero-banner">
            <img
              :src="'images/hero-banner.png'"
              alt="Modern house model"
              class="w-100"
            />
          </figure>
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script>
import { HomeIcon } from "vue-tabler-icons";

export default {
  components: {
    HomeIcon,
  },
};
</script>

<style>
</style>
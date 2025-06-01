<template>
  <section class="hero-section">
    <v-container class="my-container">
      <v-row>
        <v-col align-self="center" cols="12" sm="12" md="6">
          <!-- Tag Line -->
          <p class="hero-subtitle">
            <HomeIcon size="20" />
            <span>Home Service & Product Marketplace</span>
          </p>

          <!-- Main Title -->
          <h2 class="h1 hero-title">Empowering Local Services & Product Exchange</h2>

          <!-- Project Description -->
          <p class="hero-text">
            Find trusted repair services for appliances like washing machines, refrigerators, and more — or sell your
            second-hand products such as electronics and furniture. Work mains can accept requests and chat with
            customers. Subscriptions available for monthly, half-yearly, and yearly access. A complete solution to buy,
            sell, and service — all in one place!
          </p>

          <!-- CTA Button -->
          <v-btn href="/contact-us" variant="flat" class="mt-5" size="large" color="primary">Make An Enquiry</v-btn>
        </v-col>

        <v-col cols="12" sm="12" md="6">
          <figure class="hero-banner">
            <img :src="'images/home-service-marketplaces.png'" alt="Modern house model" class="w-100" />
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

<style></style>
<template>
  <section class="cta-section">
    <v-container class="my-container">
      <div class="cta-card">
        <div class="card-content">
          <h1 class="text-h1 card-title">Find your best products?</h1>

          <p class="card-text">
            We can help you realize your favorites Products
          </p>
        </div>

        <a href="/products">
          <button class="btn cta-btn">
            <span>Explore Products</span>

            <ArrowRightIcon />
          </button>
        </a>
      </div>
    </v-container>
  </section>
</template>

<script>
import { ArrowRightIcon } from "vue-tabler-icons";

export default {
  components: {
    ArrowRightIcon,
  },
};
</script>

<style>
</style>
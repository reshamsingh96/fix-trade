<template>
  <a name="about"></a>
  <section class="about-section">
    <v-container class="my-container">
      <v-row>
        <v-col cols="12" sm="6" md="6">
          <figure class="about-banner">
            <img :src="'images/banner.png'" alt="House interior" />
          </figure>
        </v-col>

        <v-col cols="12" sm="6" md="6">
          <div class="about-content">
            <div class="d-flex">
              <p class="section-subtitle">About Us</p>
            </div>

            <h2 class="h2 section-title">Anaaj Culture Marketplace</h2>

            <p class="about-text">
             Over 19,000 Farmers Have Joined Us Across India!

This incredible nationwide coverage empowers us to serve farmers in every corner of the country. With our specialized services to Buy, Sell, or Rent agricultural products, we’re making farming more accessible, efficient, and profitable for everyone.

Together, we’re building a stronger farming community!


            </p>
            </br>
            <p>
            #AnaajCulture #FarmersFirst #AgricultureInnovation
            </p>

            <ul class="about-list">
              <li class="about-item">
                <div class="about-item-icon">
                  <BuildingStoreIcon size="20" stroke-width="1.5" />
                </div>

                <p class="about-item-text">Sell Products</p>
              </li>

              <li class="about-item">
                <div class="about-item-icon">
                  <ShoppingBagIcon size="20" stroke-width="1.5" />
                </div>

                <p class="about-item-text">Buy Products</p>
              </li>

              <li class="about-item">
                <div class="about-item-icon">
                  <ClickIcon size="20" stroke-width="1.5" />
                </div>

                <p class="about-item-text">Rent Products</p>
              </li>
            </ul>

            <p class="callout">
              "With Anaaj Culture, easily buy, sell, or rent agricultural products and equipment. Our platform connects
              farmers and vendors, streamlining transactions for grains, fertilizers, machinery, and more"
            </p>

            <v-btn variant="flat" class="mt-5" size="large" color="primary">Our Services</v-btn>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script>
import {
  BuildingStoreIcon,
  ClickIcon,
  ShoppingBagIcon,
} from "vue-tabler-icons";

export default {
  components: {
    BuildingStoreIcon,
    ShoppingBagIcon,
    ClickIcon,
  },
};
</script>

<style>
</style>
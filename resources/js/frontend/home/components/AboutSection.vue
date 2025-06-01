<template>
  <a name="about"></a>
  <section class="about-section">
    <v-container class="my-container">
      <v-row>
        <!-- Banner Image -->
        <v-col cols="12" sm="6" md="6">
          <figure class="about-banner">
            <img :src="'images/banner.png'" alt="Home Services Banner" />
          </figure>
        </v-col>

        <!-- About Content -->
        <v-col cols="12" sm="6" md="6">
          <div class="about-content">
            <div class="d-flex">
              <p class="section-subtitle">About Us</p>
            </div>
            <h2 class="h2 section-title">Home Service & Product Marketplace</h2>
            <p class="about-text">
              Join our platform where customers can easily request home services like appliance repair or list items for
              sale,
              including second-hand electronics and furniture. Workmains (service providers) can accept jobs and chat
              with customers directly.
              Our subscription model gives workmains access to a wide range of service requests.
            </p>
            <p>
              #LocalServices #TrustedRepairs #HomeMarketplace
            </p>
            <ul class="about-list mt-5">

              <li class="about-item">
                <div class="about-item-icon">
                  <ClickIcon size="20" stroke-width="1.5" />
                </div>
                <p class="about-item-text">Request Repairs</p>
              </li>

              <li class="about-item">
                <div class="about-item-icon">
                  <ShoppingBagIcon size="20" stroke-width="1.5" />
                </div>
                <p class="about-item-text">Buy & Sell Products</p>
              </li>

              <li class="about-item">
                <div class="about-item-icon">
                  <ClickIcon size="20" stroke-width="1.5" />
                </div>
                <p class="about-item-text">Rent Products</p>
              </li>

              <li class="about-item">
                <div class="about-item-icon">
                  <ClickIcon size="20" stroke-width="1.5" />
                </div>
                <p class="about-item-text">Verified Workmains</p>
              </li>

              <li class="about-item">
                <div class="about-item-icon">
                  <ClickIcon size="20" stroke-width="1.5" />
                </div>
                <p class="about-item-text">Labour</p>
              </li>

              <li class="about-item">
                <div class="about-item-icon">
                  <ClickIcon size="20" stroke-width="1.5" />
                </div>
                <p class="about-item-text">Store</p>
              </li>
            </ul>

            <p class="callout">
              "A trusted platform for home services and second-hand product sales. Customers connect with verified work
              mains
              for repairs or transactions, while service providers can subscribe monthly, half-yearly, or yearly to
              access job requests."
            </p>

            <v-btn variant="flat" class="mt-5" size="large" color="primary">Our Services</v-btn>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>


<script>
import { BuildingStoreIcon, ClickIcon, ShoppingBagIcon } from "vue-tabler-icons";

export default {
  components: {
    BuildingStoreIcon,
    ShoppingBagIcon,
    ClickIcon,
  },
};
</script>

<style></style>
<template>
  <section class="featured-section">
    <v-container class="my-container">
      <div class="text-center">
        <p class="section-subtitle">Featured</p>
        <h1 class="text-h1 section-title">Featured Listings</h1>
      </div>
      <br />
      <br />
      <v-row>
        <v-col v-for="item in 3" :key="item" cols="12" sm="6" md="4">
          <a href="/product/red-sindhi-cow" class="product-card-link">
            <div class="product-card">
              <figure class="card-banner">
                <img
                  src="https://images.unsplash.com/photo-1693667308707-1ce4fabfa78d?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8cmVkJTIwY293fGVufDB8fDB8fHww"
                />

                <div class="card-badge golden">Animals</div>
              </figure>

              <div class="card-content">
                <div class="card-price">
                  <strong>₹ 70,500</strong>
                </div>

                <h3 class="h3 card-title">
                  <a href="#">Red Sindhi Cow</a>
                </h3>

                <p class="card-text">
                  The Red Sindhi Cow is a hardy and highly productive dairy
                  breed known for its distinctive reddish-brown color.
                </p>
              </div>
            </div>
          </a>
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script>
export default {};
</script>

<style>
</style>
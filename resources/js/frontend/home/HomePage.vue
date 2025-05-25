<template>
  <v-app>
    <Navbar />
    <v-main>
      <HeroSection />
      <AboutSection />
      <TopCategories />
      <Teams />
      <FeaturesProducts />
    </v-main>
    <CtaSection />
    <Footer/>
  </v-app>
</template>

<script>
import Navbar from "../common/Navbar.vue";
import HeroSection from "./components/HeroSection.vue";
import AboutSection from "./components/AboutSection.vue";
import TopCategories from "./components/TopCategories.vue";
import Teams from "./components/Teams.vue";
import FeaturesProducts from "./components/FeaturesProducts.vue";
import CtaSection from "./components/CtaSection.vue";
import Footer from "../common/Footer.vue";

export default {
  components: {
    Navbar,
    HeroSection,
    AboutSection,
    TopCategories,
    Teams,
    FeaturesProducts,
    CtaSection,
    Footer
  },
};
</script>

<style>
</style>
<template>
  <a name="category"></a>
  <section class="top-categories">
    <v-container class="my-container">
      <div class="text-center">
        <p class="section-subtitle">Categories</p>
        <h1 class="text-h1 section-title">Our Main Categories</h1>
      </div>
      <br />
      <br />
      <v-row justify="center">
        <v-col v-for="(item, i) in categories" :key="i" cols="12" sm="6" md="4">
          <div class="category-card">
            <div class="card-icon">
              <img :src="item.image" :alt="item.title" />
            </div>

            <h3 class="text-h3">{{ item.title }}</h3>

            <p class="card-text">
              {{ item.desc }}
            </p>

            <a href="/products" class="card-link">
              <span>Find Products</span>

              <ArrowRightIcon />
            </a>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script>
import { ref } from "vue";
import { ArrowRightIcon } from "vue-tabler-icons";

export default {
  components: {
    ArrowRightIcon,
  },
  setup() {
    const categories = ref([
      {
        id: "1",
        image: "/images/service-1.png",
        title: "Grains",
        desc: "Explore a wide variety of grains from trusted sellers. Buy or sell wheat, rice, and other staple crops directly through the app.",
      },
      {
        id: "2",
        image: "/images/service-2.png",
        title: "Fertilizers, Seed & Pesticides",
        desc: "Browse through a range of seeds and pesticides for every type of crop.",
      },
      {
        id: "3",
        image: "/images/service-3.png",
        title: "Machinery",
        desc: "Access essential farming machinery for sale or rent. Buy or rent tractors, tillers, and other equipment from verified sellers.",
      },
      {
        id: "4",
        image: "/images/cow.png",
        title: "Animals",
        desc: "Buy or sell livestock and animals with ease. Connect with buyers or sellers for secure and hassle-free transactions.",
      },
      {
        id: "5",
        image: "/images/labour.png",
        title: "Labour",
        desc: "Find and hire skilled labourers or offer your services easily. Connect quickly for all your agricultural labour needs.",
      },
    ]);

    return {
      categories,
    };
  },
};
</script>

<style>
</style>
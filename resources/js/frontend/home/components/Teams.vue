<template>
  <a name="team"></a>
  <section class="teams-section">
    <v-container class="my-container">
      <div class="text-center">
        <p class="section-subtitle">Our Team</p>
        <h1 class="text-h1 section-title">Building Aminities</h1>
      </div>
      <br />
      <br />
      <v-row justify="center">
        <v-col v-for="(item, i) in teams" :key="i" cols="12" sm="4" md="3">
          <div class="teams-card text-center">
            <figure class="team-avatar">
              <img :src="item.image" :alt="item.title" class="w-100" />
            </figure>

            <h3 class="text-h3">{{ item.title }}</h3>

            <p class="card-text">
              {{ item.desc }}
            </p>
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
    const teams = ref([
      // {
      //   image: "/images/",
      //   title: "Founder",
      // },
      {
        image: "/images/general-manager.jpg",
        title: "Director",
      },
      // {
      //   image: "/images/logo.jpg",
      //   title: "Social Media Manager",
      // },
      // {
      //   image: "/images/legal-manager.",
      //   title: "Legal Manager",
      // },
      // {
      //   image: "/images/associate-vice-president.",
      //   title: "Associate Vice President(AVP)",
      // },
      {
        image: "/images/technical-support.jpg",
        title: "Technical Support",
      },
    ]);

    return {
      teams,
    };
  },
};
</script>

<style>
</style>
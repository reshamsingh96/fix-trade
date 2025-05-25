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
      {
        image: "/images/Founder.png",
        title: "Founder",
      },
      {
        image: "/images/General Manager.png",
        title: "Director",
      },
      {
        image: "/images/logo.png",
        title: "Social Media Manager",
      },
      {
        image: "/images/Legal Manager.png",
        title: "Legal Manager",
      },
      {
        image: "/images/associate_vice_president.jpeg",
        title: "Associate Vice President(AVP)",
      },
      {
        image: "/images/technical_support.jpg",
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
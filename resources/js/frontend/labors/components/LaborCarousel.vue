<template>
  <div>
    <Carousel id="gallery" :items-to-show="1" :wrap-around="false" v-model="currentSlide">
      <Slide v-for="(slide, i) in slideShow" :key="i">
        <div class="carousel_item">
          <img alt="product" :src="slide.image" class="w-100 rounded-md" />
        </div>
      </Slide>

      <Slide v-if="!slideShow || slideShow.length == 0">
        <div class="carousel_item">
          <img alt="product" :src="'/images/labour.png'" class="w-100 rounded-md" />
        </div>
      </Slide>

      <template #addons>
        <Navigation />
      </template>
    </Carousel>

    <Carousel v-if="slideShow && slideShow.length > 0" id="thumbnails" :currentSlide="currentSlide" :transition="500" :items-to-show="6" :wrap-around="true"
      v-model="currentSlide" ref="carousel">
      <Slide v-for="(slide, i) in slideShow" :key="i">
        <div class="carousel_item cursor-pointer" @click="slideTo(slide.id - 1)">
          <img alt="product" :src="slide.image" class="w-100" />
        </div>
      </Slide>

      <template #addons>
        <Navigation />
      </template>
    </Carousel>
  </div>
</template>

<script>
import { ref } from "vue";
import { Carousel, Slide, Navigation } from "vue3-carousel";
import "vue3-carousel/dist/carousel.css";

export default {
  props: {
    slideShow: Array,
  },
  components: {
    Carousel,
    Slide,
    Navigation,
  },
  setup() {
  },

  data() {
    return {
      currentSlide: 0
    };
  },

  watch: {
    slideShow(newVal) {
      if (newVal && newVal.length > 0) {
        this.slideTo(0);
      }
    },
  },

  methods: {
    slideTo(val) {
      this.currentSlide = val;
    },
  },
};
</script>

<style lang="scss">
#thumbnails {
  .carousel_slide {
    border: 2px solid transparent;
    line-height: 0px;
  }

  .carousel_slide--active {
    border: 2px solid rgb(var(--v-theme-primary));
  }
}
</style>

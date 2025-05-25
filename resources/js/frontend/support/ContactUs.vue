<template>
  <v-app>
    <Navbar />
    <v-main>
      <v-container class="my-container">
        <div class="text-center">
          <h1 class="text-h1">Contact our team</h1>
          <p class="mt-5 font-weight-regular text-h5">
            Got any question about the product or scaling on our platform? We're
            here to help.
          </p>
        </div>
        <br /><br />
        <v-row justify="center">
          <v-col cols="12" sm="12" md="6">
            <v-card elevation="10">
              <v-card-text>
                <v-row>
                  <v-col cols="12" sm="12" md="6">
                    <p class="text-subtitle-1 font-weight-regular pb-2">
                      First name
                    </p>
                    <v-text-field
                      density="compact"
                      placeholder="First name"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" sm="12" md="6">
                    <p class="text-subtitle-1 font-weight-regular pb-2">
                      Last name
                    </p>
                    <v-text-field
                      density="compact"
                      placeholder="Last name"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" sm="12" md="12">
                    <p class="text-subtitle-1 font-weight-regular pb-2">
                      Email
                    </p>
                    <v-text-field
                      density="compact"
                      placeholder="you@company.com"
                    ></v-text-field>
                  </v-col>

                  <v-col cols="12" sm="12" md="12">
                    <p class="text-subtitle-1 font-weight-regular pb-2">
                      Phone number
                    </p>
                    <v-text-field
                      density="compact"
                      placeholder="+91 00000-00000"
                    ></v-text-field>
                  </v-col>

                  <v-col cols="12" sm="12" md="12">
                    <p class="text-subtitle-1 font-weight-regular pb-2">
                      Message
                    </p>
                    <v-textarea placeholder="Type here"></v-textarea>
                  </v-col>
                </v-row>

                <v-btn size="large" block color="primary">Send message</v-btn>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col class="ml-5" cols="12" sm="12" md="4">
            <div class="contact-detail">
              <ul class="contact-list">
                <li>
                  <a href="tel:+91 63968 73155" class="contact-link">
                    <PhoneIcon size="20" stroke-width="1.8" />

                    <span>+91 63968-73155</span>
                  </a>
                </li>

                <li>
                  <a href="mailto:anaaj.culture@gmail.com" class="contact-link">
                    <MailIcon size="20" stroke-width="1.8" />

                    <span>anaaj.culture@gmail.com</span>
                  </a>
                </li>
              </ul>

              <ul class="social-list">
                <li>
                  <a href="#" class="social-link">
                    <BrandFacebookIcon size="20" stroke-width="1.8" />
                  </a>
                </li>

                <li>
                  <a href="#" class="social-link">
                    <BrandTwitterIcon size="20" stroke-width="1.8" />
                  </a>
                </li>

                <li>
                  <a href="#" class="social-link">
                    <BrandLinkedinIcon size="20" stroke-width="1.8" />
                  </a>
                </li>

                <li>
                  <a href="#" class="social-link">
                    <BrandYoutubeIcon size="20" stroke-width="1.8" />
                  </a>
                </li>
              </ul>
            </div>
          </v-col>
        </v-row>
        <br />
        <br />
        <br />
      </v-container>
    </v-main>
    <Footer />
  </v-app>
</template>

<script>
import Navbar from "../common/Navbar.vue";
import Footer from "../common/Footer.vue";
import {
  MailIcon,
  PhoneIcon,
  BrandFacebookIcon,
  BrandTwitterIcon,
  BrandLinkedinIcon,
  BrandYoutubeIcon,
} from "vue-tabler-icons";

export default {
  components: {
    Navbar,
    Footer,
    PhoneIcon,
    MailIcon,
    BrandFacebookIcon,
    BrandTwitterIcon,
    BrandLinkedinIcon,
    BrandYoutubeIcon,
  },
};
</script>

<style>
</style>
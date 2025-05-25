<template>
  <section class="footer">
    <div class="footer-top">
      <v-container class="my-container">
        <v-row>
          <v-col cols="12" sm="6" md="4">
            <div class="footer-brand">
              <p class="section-text">
                Anaaj Culture is your trusted platform for finding best products
                for you. Its help to sell or buy your products.
              </p>

              <ul class="contact-list">
                <li>
                  <a href="tel:+91 63968 73155" class="contact-link">
                    <PhoneIcon size="20" stroke-width="1.3" />

                    <span>+91 63968-73155</span>
                  </a>
                </li>

                <li>
                  <a href="mailto:anaaj.culture@gmail.com" class="contact-link">
                    <MailIcon size="20" stroke-width="1.3" />

                    <span>anaaj.culture@gmail.com</span>
                  </a>
                </li>
              </ul>

              <ul class="social-list">
                <li>
                  <a href="#" class="social-link">
                    <BrandFacebookIcon size="20" stroke-width="1.3" />
                  </a>
                </li>

                <li>
                  <a href="#" class="social-link">
                    <BrandTwitterIcon size="20" stroke-width="1.3" />
                  </a>
                </li>

                <li>
                  <a href="#" class="social-link">
                    <BrandLinkedinIcon size="20" stroke-width="1.3" />
                  </a>
                </li>

                <li>
                  <a href="#" class="social-link">
                    <BrandYoutubeIcon size="20" stroke-width="1.3" />
                  </a>
                </li>
              </ul>
            </div>
          </v-col>
          <v-col class="ml-auto" cols="12" sm="6" md="2">
            <ul class="footer-list">
              <li>
                <p class="footer-list-title">Quick Links</p>
              </li>
              <li>
                <a href="#" class="footer-link">Home</a>
              </li>
              <li>
                <a href="#" class="footer-link">Category</a>
              </li>
              <li>
                <a href="/products" class="footer-link">Products</a>
              </li>
              <li>
                <a href="/labors" class="footer-link">Labour</a>
              </li>
              <li>
                <a href="#" class="footer-link">Team</a>
              </li>
              <li>
                <a href="#" class="footer-link">About</a>
              </li>
            </ul>
          </v-col>

          <v-col cols="12" sm="6" md="2">
            <ul class="footer-list">
              <li>
                <p class="footer-list-title">Accounts</p>
              </li>

              <li>
                <a href="#" class="footer-link">Login</a>
              </li>

              <li>
                <a href="#" class="footer-link">Register</a>
              </li>

              <li>
                <a href="#" class="footer-link">My Account</a>
              </li>

              <li>
                <a href="#" class="footer-link">My Wishlist</a>
              </li>
            </ul>
          </v-col>
          <v-col cols="12" sm="6" md="2">
            <ul class="footer-list">
              <li>
                <p class="footer-list-title">Support</p>
              </li>
              <li>
                <a href="/contact-us" class="footer-link">Contact us</a>
              </li>
              <li>
                <a href="/privacy-policy" class="footer-link">Privacy Policy</a>
              </li>
              <li>
                <a href="/terms-conditions" class="footer-link">Terms & Conditions</a>
              </li>
              <li>
                <a href="/refund-policy" class="footer-link">Refund Policy</a>
              </li>
            </ul>
          </v-col>
        </v-row>
      </v-container>
    </div>
    <div class="footer-bottom">
      <div class="container">
        <p class="copyright">
          &copy; {{ getCurrentYear() }} All Rights Reserved
        </p>
      </div>
    </div>
  </section>
</template>

<script> 
import {
  BrandFacebookIcon,
  BrandLinkedinIcon,
  BrandTwitterIcon,
  BrandYoutubeIcon,
  MailIcon,
  PhoneIcon,
} from "vue-tabler-icons";

export default {
  components: {
    PhoneIcon,
    MailIcon,
    BrandFacebookIcon,
    BrandTwitterIcon,
    BrandLinkedinIcon,
    BrandYoutubeIcon,
  },
  methods: {
    getCurrentYear() {
      return new Date().getFullYear();
    },
  },
};
</script>

<style>
</style>
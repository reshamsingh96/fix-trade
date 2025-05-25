<template>
  <v-app>
    <Navbar />
    <v-main>
      <v-container class="my-container">
        <v-card elevation="10">
          <AppBaseCard>
            <template v-slot:leftpart>
              <v-list class="pa-4 scrollnavbar">
                <v-list-item
                  v-for="item in menuItems"
                  :key="item.id"
                  :class="{ 'active-menu': selectedItem === item.id }"
                  @click="selectMenuItem(item.id)"
                  color="primary"
                  rounded
                  class="mb-1"
                >
                  <template v-slot:prepend>
                    <Icon :item="item.icon" />
                  </template>

                  <v-list-item-title>{{ item.label }}</v-list-item-title>
                </v-list-item>
              </v-list>
            </template>
            <template v-slot:rightpart>
              <div v-if="selectedItem === 'profile'">
                <!-- Profile Component -->
                <Profile />
                <!-- Add your profile component or content here -->
              </div>

              <div v-if="selectedItem === 'changePassword'">
                <!-- Change Password Component -->
                <ChangePassword />
              </div>

              <div v-if="selectedItem === 'orderList'">
                <!-- Order List Component -->
                <Order />
              </div>

              <div v-if="selectedItem === 'bookmarkProductList'">
                <!-- Bookmark Product List Component -->
                <Bookmark />
              </div>
            </template>

            <template v-slot:mobileLeftContent>
              <v-list class="pa-4 scrollnavbar">
                <v-list-item
                  v-for="item in menuItems"
                  :key="item.id"
                  :class="{ 'active-menu': selectedItem === item.id }"
                  @click="selectMenuItem(item.id)"
                  color="primary"
                  rounded
                  class="mb-1"
                >
                  <template v-slot:prepend>
                    <Icon :item="item.icon" />
                  </template>

                  <v-list-item-title>{{ item.label }}</v-list-item-title>
                </v-list-item>
              </v-list>
            </template>
          </AppBaseCard>
        </v-card>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
import Navbar from "../common/Navbar.vue";
import Profile from "./Profile.vue";
import ChangePassword from "./ChangePassword.vue";
import Order from "../../components/order/index.vue";
import Bookmark from "../../components/product/BookmarkList.vue";
import AppBaseCard from "../../common/AppBaseCard.vue";
import { BookmarkIcon, HeartIcon, LockIcon, PackageIcon, UserIcon } from "vue-tabler-icons";
import Icon from "../../common/Icon.vue";

export default {
  components: {
    Navbar,
    Profile,
    ChangePassword,
    Order,
    Bookmark,
    AppBaseCard,
    Icon,
  },
  data() {
    return {
      selectedItem: "profile", // Default selected item
      menuItems: [
        { id: "profile", icon: UserIcon, label: "Profile" },
        { id: "changePassword", icon: LockIcon, label: "Change Password" },
        { id: "orderList", icon: PackageIcon, label: "My Orders" },
        { id: "bookmarkProductList", icon: BookmarkIcon, label: "Bookmarks" },
      ],
    };
  },
  methods: {
    selectMenuItem(itemId) {
      this.selectedItem = itemId; // Update the selected menu item
    },
  },
};
</script>

<style scoped>
.menu-item {
  cursor: pointer;
}

.active-menu {
  color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.1);
}
</style>
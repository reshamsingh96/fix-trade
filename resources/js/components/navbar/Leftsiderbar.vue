<template>
  <v-navigation-drawer
    v-model="drawer_value"
    left
    elevation="0"
    app
    width="270"
    class="leftSidebarcustom-navbar"
  >
    <div class="drawer-header">
      <v-img
        class="mx-auto mt-4"
        max-width="50"
        src="/images/logo.jpg"
        width="50"
        height="50"
      ></v-img>
      <v-card-title class="text-center text-white pa-0"
        >Home Service</v-card-title
      >
    </div>

    <v-list class="scrollnavbar pa-6">
      <template v-for="(item, i) in sidebarItems" :key="i">
        <NavCollapse
          class="leftPadding"
          :item="item"
          :level="0"
          v-if="item.children"
        />
        <NavItem v-else :item="item" class="leftPadding" />
      </template>
    </v-list>

    <template v-slot:append>
      <div class="pa-2">
        <v-btn block variant="text" color="white" @click="logout">Logout</v-btn>
      </div>
    </template>
  </v-navigation-drawer>
</template>

<script>
import axios from "axios";
import Cookies from "js-cookie";
import {
  CategoryIcon,
  CheckupListIcon,
  LayoutDashboardIcon,
  PackageIcon,
  PointIcon,
  ReportIcon,
  TableIcon,
  UserIcon,
  UsersGroupIcon,
} from "vue-tabler-icons";
import NavItem from "./NavItem.vue";
import NavCollapse from "./NavCollapse.vue";

export default {
  props: {
    drawer: Boolean,
  },
  components: {
    NavItem,
    NavCollapse,
    LayoutDashboardIcon,
    UserIcon,
    PackageIcon,
    CheckupListIcon,
    UsersGroupIcon,
    ReportIcon,
    TableIcon,
    PointIcon,
  },
  data() {
    return {
      drawer_value: true,
      sidebarItems: [
        {
          title: "Dashboard",
          icon: LayoutDashboardIcon,
          to: "/admin/dashboard",
        },
        {
          title: "Users",
          icon: UserIcon,
          to: "/admin/users",
        },
        {
          title: "Products",
          icon: PackageIcon,
          to: "/admin/products",
        },
        {
          title: "Category",
          icon: CategoryIcon,
          to: "/",
          children: [
            {
              title: "Categories",
              icon: PointIcon,
              to: "/admin/lookup/category",
            },
            {
              title: "Sub-Categories",
              icon: PointIcon,
              to: "/admin/lookup/sub-category",
            },
          ],
        },
        {
          title: "Orders",
          icon: CheckupListIcon,
          to: "/admin/orders",
        },
        {
          title: "Labour",
          icon: UsersGroupIcon,
          to: "/admin/labors",
        },
        {
          title: "Reports",
          icon: ReportIcon,
          to: "/admin/reports",
        },
        {
          title: "Lookup",
          icon: TableIcon,
          to: "/",
          children: [
            {
              title: "Country",
              icon: PointIcon,
              to: "/admin/lookup/country",
            },
            {
              title: "State",
              icon: PointIcon,
              to: "/admin/lookup/state",
            },
            {
              title: "City",
              icon: PointIcon,
              to: "/admin/lookup/city",
            },
          ],
        },
      ],
    };
  },
  watch: {
    drawer(val) {
      this.drawer_value = val;
    },
  },
  methods: {
    async logout() {
      try {
        const response = await axios.post("/api/logout");
        if (response.data.status == 200) {
          this.$store.dispatch("login/setPermissions", []);
          localStorage.removeItem("user_login");
          Cookies.remove("access_token");
          localStorage.removeItem("access_token");
          this.$store.dispatch("login/setUser", null);
          this.$router.push({ name: "adminLogin" });
        }
      } catch (error) {
        localStorage.removeItem("user_login");
        Cookies.remove("access_token");
        localStorage.removeItem("access_token");
        this.$store.dispatch("login/setUser", null);
        this.$router.push({ name: "adminLogin" });
        let message = error.response
          ? error.response.data.message
          : "Something went wrong!";
        this.$store.dispatch("globalState/errorSnackBar", message);
      }
    },
  },
};
</script>

<style scoped>
:deep(.v-list) {
  background-color: transparent !important;
  color: #aeadad;
}

:deep(.v-list) {
  background-color: transparent !important;
}
</style>

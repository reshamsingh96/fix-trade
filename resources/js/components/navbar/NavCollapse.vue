<script setup>
import Icon from "../../common/Icon.vue";
import NavItem from "./NavItem.vue";

const props = defineProps({ item: Object, level: Number });
</script>

<template>
  <v-list-group no-action>
    <template v-slot:activator="{ props }">
      <v-list-item v-bind="props" :value="item.title" rounded class="mb-1">
        <template v-slot:prepend>
          <Icon :item="item.icon" :level="level" />
        </template>
        <v-list-item-title class="mr-auto">{{ item.title }}</v-list-item-title>
      </v-list-item>
    </template>
    <template
      v-for="(subitem, i) in item.children"
      :key="i"
      v-if="item.children"
    >
      <NavCollapse :item="subitem" v-if="subitem.children" :level="level + 1" />
      <NavItem :item="subitem" :level="level + 1" v-else></NavItem>
    </template>
  </v-list-group>
</template>

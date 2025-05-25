<template>
    <v-progress-linear color="primary" indeterminate v-if="loader"></v-progress-linear>
    <v-container class="mt-3 my-2" fluid>
        <v-card elevation="10"> 
            <v-row class="mx-1" align="center">
                <v-col cols="6" sm="6" md="4">
                    <h4 class="text-h4">Up-Coming</h4>
                </v-col>

                <v-col order="2" order-md="1" cols="12" sm="12" md="4">
                </v-col>

                <v-col order="1" order-md="2" class="text-end" cols="6" sm="6" md="4">
                </v-col>
            </v-row>
        </v-card>
    </v-container>
</template>
<script>
import axios from "axios";
import { ability } from "../../ability.js";
import { EyeIcon, EditIcon, TrashIcon } from "vue-tabler-icons";
import loader from "sass-loader";
export default {
    components: { EyeIcon, EditIcon, TrashIcon },
    data() {
        return {
            ability: ability,
            loader: false,
        };
    },
};
</script>
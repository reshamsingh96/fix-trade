<template>
    <v-progress-linear color="primary" indeterminate v-if="loader"></v-progress-linear>
    <v-container class="mt-3 my-2" fluid>
        <v-card elevation="10">
            <!-- Header Row -->
            <v-row class="mx-1" align="center">
                <v-col cols="6" sm="6" md="4">
                    <h4 class="text-h4">Category List</h4>
                </v-col>

                <v-col order="2" order-md="1" cols="12" sm="12" md="4">
                    <v-text-field class="mr-2" v-model="search" label="Search" hide-details density="compact" clearable
                        variant="outlined" @click:clear="onClear" @keyup="getList"></v-text-field>
                </v-col>

                <v-col order="1" order-md="2" class="text-end" cols="6" sm="6" md="4">
                    <v-btn @click="openCommonDialog(null)" color="primary" class="mr-2">Add</v-btn>
                    <v-icon @click="getList" class="cursor-pointer">mdi-refresh</v-icon>
                </v-col>
            </v-row>

            <v-data-table-server v-model:items-per-page="per_row" :headers="headers" :items="List"
                :items-length="totalPages" item-value="name" @update:options="getList">

                <!-- Sr No Slot -->
                <template v-slot:item.sr_no="{ item, index }">
                    {{ (page - 1) * per_row + index + 1 }}
                </template>

                <!-- Name Slot -->
                <template v-slot:item.name="{ item }" >
                    <v-avatar color="blue" size="36" class="mr-2">
                        <v-img v-if="item.category_url" :src="item.category_url"></v-img>
                        <span v-else class="white--text">{{ nameInitial(item.name) }}</span>
                    </v-avatar>
                    {{ item.name }}
                </template>

                <!-- Actions Slot -->
                <template v-slot:item.actions="{ item }">
                    <v-btn @click="openCommonDialog(item)" size="small" variant="text" icon>
                        <EditIcon />
                    </v-btn>

                    <v-btn v-if="item.category_type == 'yes'" @click="confirmDelete(item)" size="small" variant="text"
                        color="error" icon>
                        <TrashIcon />
                    </v-btn>
                </template>
            </v-data-table-server>
        </v-card>
    </v-container>

    <div class="ml-5">
        <!-- Create User Dialog -->
        <v-dialog v-model="commonDialog" max-width="500" persistent scrollable>
            <v-card class="mx-auto p-3" elevation="2">
                <v-card-title>
                    <h3>{{ !selected_info ? "Labor Create" : 'Labor Edit' }}</h3>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <v-row>
                        <!-- Image Preview at the Top -->
                        <v-col cols="12" class="text-center mb-2 d-flex" v-if="image_url">
                            <v-img :src="image_url || '/images/profile_image.png'" max-width="150" rounded="lg"></v-img>
                            <v-btn color="red" @click="removeImage" v-if="form.image" class="mt-2">Remove Image </v-btn>
                        </v-col>

                        <!-- File Input with Image Size Display -->
                        <v-col cols="12" class="text-center">
                            <v-file-input id="image-selected-empty" :clearable="false" v-model="form.image"
                                label="Choose Image" accept="image/*" variant="outlined" @change="onImageSelected"
                                show-size truncate-length="40"></v-file-input>
                        </v-col>
                    </v-row>
                    <v-row>
                        <!-- Name Field -->
                        <v-col cols="12">
                            <v-text-field label="Name" variant="outlined" v-model="form.name" density="compact"
                                :error-messages="v$.form.name.$errors.map(e => e.$message)"
                                @blur="v$.form.name.$touch()" :rules="[(v) => !!v || 'Name is required']">
                            </v-text-field>
                        </v-col>

                        <!-- category_type Field -->
                        <v-col cols="12">
                            <v-select label="Delete Type" :items="['yes', 'no']" v-model="form.category_type"
                                variant="outlined" density="compact"
                                :error-messages="v$.form.category_type.$errors.map(e => e.$message)"
                                @blur="v$.form.category_type.$touch()"></v-select>
                        </v-col>

                        <!-- Description Field -->
                        <v-col cols="12">
                            <v-textarea label="Description" variant="outlined" v-model="form.description" hide-details
                                auto-grow rows="2" density="compact">
                            </v-textarea>
                        </v-col>
                    </v-row>
                </v-card-text>

                <!-- Divider -->
                <v-divider></v-divider>

                <!-- Actions Section -->
                <v-card-actions class="d-flex justify-end">
                    <v-btn class="mx-2" :loading="loader" :disabled="loader" @click="submitForm()">
                        {{ selected_info ? 'Update' : 'Save' }}
                    </v-btn>
                    <v-btn color="error" class="mx-2" @click="closeCommonDialog()">Close
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Confirm Delete Dialog -->
        <v-dialog v-model="confirmDeleteDialog" max-width="500" persistent scrollable>
            <v-card>
                <v-card-title>Confirm Delete</v-card-title>
                <v-card-text>Are you sure you want to delete this info?</v-card-text>
                <v-card-actions class="d-flex justify-center">
                    <v-btn @click="closeConfirmDialog">Cancel</v-btn>
                    <v-btn @click="deleteInfo" color="error">Yes</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import axios from 'axios';
import useVuelidate from '@vuelidate/core';
import { required, minLength } from '@vuelidate/validators';
import { ability } from "../../ability.js";
import { EyeIcon, EditIcon, TrashIcon } from "vue-tabler-icons";

export default {
    components: { EyeIcon, EditIcon, TrashIcon },
    setup() {
        const v$ = useVuelidate();
        return { v$ };
    },
    data() {
        return {
            loader: false,
            page: 1,
            per_row: 25,
            search: '',
            headers: [
                { title: "No.", key: "sr_no" },
                { title: "name", key: "name" },
                { title: "Description", key: "description" },
                { title: "Actions", key: "actions", sortable: false, align: "end" },
            ],

            List: [],
            totalPages: 1,
            commonDialog: false,
            confirmDeleteDialog: false,
            selected_info: null,
            ability: ability,

            image_url: null,
            form: {
                id: null,
                name: '',
                category_type: 'yes',
                description: '',
                image: '',
            }
        };
    },

    validations() {
        return {
            form: {
                name: { required },
                category_type: { required },
                description: { required },
            }
        };
    },

    mounted() {
        this.getList();
    },

    watch: {
        per_row(newVal, oldVal) {
            if (newVal !== oldVal) { this.getList(); }
        },

        page(newVal, oldVal) {
            if (newVal !== oldVal) { this.getList(); }
        },
    },

    methods: {
        nameInitial(name) {
            if (!name) return '';
            const nameParts = name.trim().split(' ');
            const firstInitial = nameParts[0] ? nameParts[0][0].toUpperCase() : '';
            const secondInitial = nameParts[1] ? nameParts[1][0].toUpperCase() : '';
            return `${firstInitial}${secondInitial}`;
        },

        refresh() {
            this.page = 1;
            this.per_row = 25;
            this.onClear();
        },

        onClear() {
            this.search = '';
            this.getList();
        },

        async getList() {
            this.loader = true;
            const params = {
                search: this.search,
                page: this.page,
                perPage: this.per_row,
            };

            await axios.post('/api/category-list', params)
                .then(response => {
                    this.List = response.data.data.data;
                    this.totalPages = response.data.data.last_page;
                })
                .catch(error => {
                    let message = error.response ? error.response.data.message : "Something went wrong!";
                    this.$store.dispatch("globalState/errorSnackBar", message);
                });
            this.loader = false;
        },

        chooseImage() {
            this.$refs.imageInput.click();
        },

        onImageSelected(event) {
            const file = event.target.files[0];
            if (file) {
                this.form.image = file;
                const fileSizeKB = (file.size / 1024).toFixed(2);
                event.target.nextElementSibling.innerHTML = `${file.name} (${fileSizeKB} KB)`;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.image_url = e.target.result
                };
                reader.readAsDataURL(file);
            } else {
                event.target.value = "";
                event.target.nextElementSibling.innerHTML = '';
            }
        },

        removeImage() {
            this.image_url = null;
            this.form.image = null;
            const imageInput = document.getElementById('image-selected-empty');
            if (imageInput) {
                imageInput.value = "";
                imageInput.nextElementSibling.innerHTML = '';
            }
        },

        async submitForm() {
            this.v$.$touch();
            if (this.v$.$invalid) {
                this.$store.dispatch("globalState/errorSnackBar", "Form is invalid!");
                return;
            }

            this.loader = true;
            const formData = new FormData();
            formData.append('id', this.form.id);
            formData.append('name', this.form.name);
            formData.append('category_type', this.form.category_type);
            formData.append('description', this.form.description);
            if (this.form.image) {
                formData.append('image', this.form.image);
            } else {
                formData.append('image', null);
            }

            const url = this.selected_info ? '/api/category-update' : '/api/category-create';
            await axios.post(url, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
                .then((response) => {
                    this.$store.dispatch("globalState/successSnackBar", response.data.message);
                    this.loader = false;
                    this.getList();
                    this.commonDialog = false;
                })
                .catch((error) => {
                    this.loader = false;
                    let message = error.response ? error.response.data.message : "Something went wrong!";
                    this.$store.dispatch("globalState/errorSnackBar", message);
                });
        },

        // Create User Function
        openCommonDialog(info = null) {
            if (info) {
                this.selected_info = info;
                this.image_url = info.category_url;
                this.form = {
                    id: info.id,
                    name: info.name,
                    category_type: info.category_type,
                    description: info.description,
                    image: '',
                };
            } else {
                this.selected_info = null;
                this.image_url = null;
                this.form = {
                    id: null,
                    name: '',
                    category_type: 'yes',
                    description: '',
                    image: '',
                };
                this.v$.$reset();
            }
            this.commonDialog = true;
        },

        closeCommonDialog() {
            this.commonDialog = false;
        },

        // delete User Function
        confirmDelete(user) {
            this.selected_info = user;
            this.confirmDeleteDialog = true;
        },

        closeConfirmDialog() {
            this.confirmDeleteDialog = false;
            this.selected_info = null;
        },

        async deleteInfo() {
            const params = {
                id: this.selected_info.id,
            };
            await axios.post('/api/category-delete', params)
                .then(response => {
                    this.getList();
                    this.closeConfirmDialog();
                })
                .catch(error => {
                    let message = error.response ? error.response.data.message : "Something went wrong!";
                    this.$store.dispatch("globalState/errorSnackBar", message);
                });
        },
    },


};
</script>
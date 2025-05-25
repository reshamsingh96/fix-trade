<template>
    <v-progress-linear color="primary" indeterminate v-if="loader"></v-progress-linear>
    <v-container class="mt-3 my-2" fluid>
        <v-card elevation="10">
            <!-- Header Row -->
            <v-row class="mx-1" align="center">
                <v-col cols="6" sm="6" md="4">
                    <h4 class="text-h4">Country List</h4>
                </v-col>
                <v-col order="2" order-md="1" cols="12" sm="12" md="4"> <v-text-field class="mr-2" v-model="search"
                        label="Search" hide-details density="compact" clearable variant="outlined"
                        @click:clear="onClear" @keyup="getList"></v-text-field> </v-col>
                <v-col order="1" order-md="2" class="text-end" cols="12" sm="12" md="4">

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
                <template v-slot:item.name="{ item }">
                    {{ item.name }}
                </template>

                <template v-slot:item.ios="{ item }">
                    {{ item.iso3 }} - {{ item.iso2 }}
                </template>

                <template v-slot:item.phone_code="{ item }">
                    <span v-for="(val, i) in item.phone_code" :key="i">{{ val }} {{ item.phone_code.length > 1 &&
                        item.phone_code.length != (i + 1) ? ',' : '' }}</span>
                </template>

                <template v-slot:item.currency="{ item }">
                    {{ item.currency_name }}({{ item.currency_symbol }})
                </template>
                <template v-slot:item.nationality="{ item }">
                    {{ item.region }} - {{ item.nationality }}
                </template>

                <!-- Actions Slot -->
                <template v-slot:item.actions="{ item }" v-if="false">
                    <v-btn @click="openCommonDialog(item)" size="small" variant="text" icon>
                        <EditIcon />
                    </v-btn>

                    <v-btn v-if="item.country_type == 'yes'" @click="confirmDelete(item)" size="small" variant="text"
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
                    <h3>{{ !selected_info ? "Country Create" : 'Country Edit' }}</h3>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <v-row>
                        <!-- Name Field -->
                        <v-col cols="12">
                            <v-text-field label="Name" variant="outlined" v-model="form.name" density="compact"
                                :error-messages="v$.form.name.$errors.map(e => e.$message)"
                                @blur="v$.form.name.$touch()" :rules="[(v) => !!v || 'Name is required']">
                            </v-text-field>
                        </v-col>

                        <!-- ISO3 Field -->
                        <v-col cols="6">
                            <v-text-field label="ISO3" variant="outlined" v-model="form.iso3" density="compact"
                                :error-messages="v$.form.iso3.$errors.map(e => e.$message)"
                                @blur="v$.form.iso3.$touch()" :rules="[(v) => !!v || 'ISO3 is required']">
                            </v-text-field>
                        </v-col>

                        <!-- ISO2 Field -->
                        <v-col cols="6">
                            <v-text-field label="ISO2" variant="outlined" v-model="form.iso2" density="compact"
                                :error-messages="v$.form.iso2.$errors.map(e => e.$message)"
                                @blur="v$.form.iso2.$touch()" :rules="[(v) => !!v || 'ISO2 is required']">
                            </v-text-field>
                        </v-col>

                        <!-- Numeric Code Field -->
                        <v-col cols="6">
                            <v-text-field label="Numeric Code" variant="outlined" v-model="form.numeric_code"
                                density="compact" :error-messages="v$.form.numeric_code.$errors.map(e => e.$message)"
                                @blur="v$.form.numeric_code.$touch()"
                                :rules="[(v) => !!v || 'Numeric Code is required']">
                            </v-text-field>
                        </v-col>

                        <!-- Phone Code Field -->
                        <v-col cols="6">
                            <v-text-field label="Phone Code" variant="outlined" v-model="form.phone_code"
                                density="compact" :error-messages="v$.form.phone_code.$errors.map(e => e.$message)"
                                @blur="v$.form.phone_code.$touch()" :rules="[(v) => !!v || 'Phone Code is required']">
                            </v-text-field>
                        </v-col>

                        <!-- Capital Field -->
                        <v-col cols="6">
                            <v-text-field label="Capital" variant="outlined" v-model="form.capital" density="compact"
                                :error-messages="v$.form.capital.$errors.map(e => e.$message)"
                                @blur="v$.form.capital.$touch()" :rules="[(v) => !!v || 'Capital is required']">
                            </v-text-field>
                        </v-col>

                        <!-- Currency Field -->
                        <v-col cols="6">
                            <v-text-field label="Currency" variant="outlined" v-model="form.currency" density="compact"
                                :error-messages="v$.form.currency.$errors.map(e => e.$message)"
                                @blur="v$.form.currency.$touch()" :rules="[(v) => !!v || 'Currency is required']">
                            </v-text-field>
                        </v-col>

                        <!-- Currency Name Field -->
                        <v-col cols="6">
                            <v-text-field label="Currency Name" variant="outlined" v-model="form.currency_name"
                                density="compact" :error-messages="v$.form.currency_name.$errors.map(e => e.$message)"
                                @blur="v$.form.currency_name.$touch()"
                                :rules="[(v) => !!v || 'Currency Name is required']">
                            </v-text-field>
                        </v-col>

                        <!-- Currency Symbol Field -->
                        <v-col cols="6">
                            <v-text-field label="Currency Symbol" variant="outlined" v-model="form.currency_symbol"
                                density="compact" :error-messages="v$.form.currency_symbol.$errors.map(e => e.$message)"
                                @blur="v$.form.currency_symbol.$touch()"
                                :rules="[(v) => !!v || 'Currency Symbol is required']">
                            </v-text-field>
                        </v-col>

                        <!-- Region Field -->
                        <v-col cols="6">
                            <v-text-field label="Region" variant="outlined" v-model="form.region" density="compact"
                                :error-messages="v$.form.region.$errors.map(e => e.$message)"
                                @blur="v$.form.region.$touch()" :rules="[(v) => !!v || 'Region is required']">
                            </v-text-field>
                        </v-col>

                        <!-- Nationality Field -->
                        <v-col cols="6">
                            <v-text-field label="Nationality" variant="outlined" v-model="form.nationality"
                                density="compact" :error-messages="v$.form.nationality.$errors.map(e => e.$message)"
                                @blur="v$.form.nationality.$touch()" :rules="[(v) => !!v || 'Nationality is required']">
                            </v-text-field>
                        </v-col>

                        <!-- Timezones Field -->
                        <v-col cols="12">
                            <v-text-field label="Timezones" variant="outlined" v-model="form.timezones"
                                density="compact" :error-messages="v$.form.timezones.$errors.map(e => e.$message)"
                                @blur="v$.form.timezones.$touch()" :rules="[(v) => !!v || 'Timezones are required']">
                            </v-text-field>
                        </v-col>

                        <!-- Emoji Field -->
                        <v-col cols="6">
                            <v-text-field label="Emoji" variant="outlined" v-model="form.emoji" density="compact"
                                :error-messages="v$.form.emoji.$errors.map(e => e.$message)"
                                @blur="v$.form.emoji.$touch()" :rules="[(v) => !!v || 'Emoji is required']">
                            </v-text-field>
                        </v-col>

                        <!-- EmojiU Field -->
                        <v-col cols="6">
                            <v-text-field label="EmojiU" variant="outlined" v-model="form.emojiU" density="compact"
                                :error-messages="v$.form.emojiU.$errors.map(e => e.$message)"
                                @blur="v$.form.emojiU.$touch()" :rules="[(v) => !!v || 'EmojiU is required']">
                            </v-text-field>
                        </v-col>

                        <!-- Latitude Field -->
                        <v-col cols="6">
                            <v-text-field label="Latitude" variant="outlined" v-model="form.latitude" density="compact"
                                :error-messages="v$.form.latitude.$errors.map(e => e.$message)"
                                @blur="v$.form.latitude.$touch()" :rules="[(v) => !!v || 'Latitude is required']">
                            </v-text-field>
                        </v-col>

                        <!-- Longitude Field -->
                        <v-col cols="6">
                            <v-text-field label="Longitude" variant="outlined" v-model="form.longitude"
                                density="compact" :error-messages="v$.form.longitude.$errors.map(e => e.$message)"
                                @blur="v$.form.longitude.$touch()" :rules="[(v) => !!v || 'Longitude is required']">
                            </v-text-field>
                        </v-col>

                        <!-- Country Type Field -->
                        <v-col cols="6">
                            <v-select label="Delete Type" :items="['yes', 'no']" v-model="form.country_type"
                                variant="outlined" density="compact"
                                :error-messages="v$.form.country_type.$errors.map(e => e.$message)"
                                @blur="v$.form.country_type.$touch()"></v-select>
                        </v-col>
                    </v-row>
                </v-card-text>

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
                { title: "Name", key: "name" },
                { title: "Iso3 - Iso2", key: "ios" },
                { title: "Phone Code", key: "phone_code" },
                { title: "Currency(Symbol)", key: "currency" },
                { title: "Region - Nationality", key: "nationality" },
                { title: "Emoji", key: "emoji" },
                { title: "Latitude", key: "latitude" },
                { title: "Longitude", key: "longitude" },
                // { title: "Actions", key: "actions", sortable: false, align: "end" },
            ],
            List: [],
            totalPages: 1,
            commonDialog: false,
            confirmDeleteDialog: false,
            selected_info: null,
            ability: ability,
            form: {
                id: null,
                name: '',
                iso3: '',
                iso2: '',
                numeric_code: '',
                phone_code: '',
                capital: '',
                currency: '',
                currency_name: '',
                currency_symbol: '',
                region: '',
                nationality: '',
                timezones: '',
                latitude: '',
                longitude: '',
                emoji: '',
                emojiU: '',
                country_type: 'yes',
            }
        };
    },

    validations() {
        const isLatitudeValid = (value) => {
            const lat = parseFloat(value);
            return lat >= -90 && lat <= 90;
        };

        const isLongitudeValid = (value) => {
            const long = parseFloat(value);
            return long >= -180 && long <= 180;
        };

        const isNumericCode = (value) => {
            const num = parseInt(value);
            return num >= 0 && num <= 999 || 'Numeric code must be a number between 0 and 999';
        };

        const isJsonArray = (value) => {
            try {
                const parsed = JSON.parse(value);
                return Array.isArray(parsed) || 'Timezones must be a valid JSON array';
            } catch (error) {
                return 'Timezones must be a valid JSON array';
            }
        };

        const isEmoji = (value) => {
            return /\p{Emoji}/u.test(value) || 'Must be a valid emoji character';
        };

        const isUnicodeEmoji = (value) => {
            return /^U\+[A-F0-9]{4,6}$/.test(value) || 'Must be a valid Unicode emoji (e.g., U+1F600)';
        };
        const isAlphaLength = (length) => (value) => {
            return /^[A-Za-z]+$/.test(value) && value.length === length || `Must be ${length} alphabetic characters`;
        };

        return {
            form: {
                name: { required },
                phone_code: { required },
                capital: { required },
                currency: { required },
                currency_name: { required },
                currency_symbol: { required },
                region: { required },
                nationality: { required },
                country_type: { required },
                iso3: { required, isAlphaLength: isAlphaLength(3) },
                iso2: { required, isAlphaLength: isAlphaLength(2) },
                numeric_code: { required, isNumericCode },
                timezones: { required, isJsonArray },
                emoji: { required, isEmoji },
                emojiU: { required, isUnicodeEmoji },
                latitude: {
                    required,
                    isLatitudeValid: {
                        $validator: isLatitudeValid,
                        $message: 'Latitude must be between -90 and 90',
                    }
                },
                longitude: {
                    required,
                    isLongitudeValid: {
                        $validator: isLongitudeValid,
                        $message: 'Longitude must be between -180 and 180',
                    }
                },
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

            await axios.post('/api/country-list', params)
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


        // Create User Function
        openCommonDialog(info = null) {
            if (info) {
                this.selected_info = info;
                this.form = {
                    id: info.id,
                    name: info.name,
                    iso3: info.iso3,
                    iso2: info.iso2,
                    numeric_code: info.numeric_code,
                    phone_code: info.phone_code,
                    capital: info.capital,
                    currency: info.currency,
                    currency_name: info.currency_name,
                    currency_symbol: info.currency_symbol,
                    region: info.region,
                    nationality: info.nationality,
                    timezones: info.timezones,
                    latitude: info.latitude,
                    longitude: info.longitude,
                    emoji: info.emoji,
                    emojiU: info.emojiU,
                    country_type: info.country_type,
                };
            } else {
                this.selected_info = null;
                this.form = {
                    id: null,
                    name: '',
                    iso3: '',
                    iso2: '',
                    numeric_code: '',
                    phone_code: '',
                    capital: '',
                    currency: '',
                    currency_name: '',
                    currency_symbol: '',
                    region: '',
                    nationality: '',
                    timezones: '',
                    latitude: '',
                    longitude: '',
                    emoji: '',
                    emojiU: '',
                    country_type: 'yes',
                };
                this.v$.$reset();
            }
            this.commonDialog = true;
        },

        closeCommonDialog() {
            this.commonDialog = false;
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
            formData.append('iso3', this.form.iso3);
            formData.append('iso2', this.form.iso2);
            formData.append('numeric_code', this.form.numeric_code);
            formData.append('phone_code', this.form.phone_code);
            formData.append('capital', this.form.capital);
            formData.append('currency', this.form.currency);
            formData.append('currency_name', this.form.currency_name);
            formData.append('currency_symbol', this.form.currency_symbol);
            formData.append('region', this.form.region);
            formData.append('nationality', this.form.nationality);
            formData.append('timezones', this.form.timezones);
            formData.append('latitude', this.form.latitude);
            formData.append('longitude', this.form.longitude);
            formData.append('emoji', this.form.emoji);
            formData.append('emojiU', this.form.emojiU);
            formData.append('country_type', this.form.country_type);
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
            await axios.post('/api/country-delete', params)
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
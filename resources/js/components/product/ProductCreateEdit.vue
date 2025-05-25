<template>
    <v-container class="mt-3 my-2" fluid>
        <v-card class="mx-auto p-3" elevation="2">
            <v-card-title>
                <h3>{{ $route.name == 'product.create' ? "Product Create" : 'Product Edit' }}</h3>
            </v-card-title>

            <v-divider></v-divider>
            <v-card-text>
                <v-row>
                    <v-col cols="12" class="mt-4">
                        <v-row>
                            <!-- Button to Choose Images -->
                            <v-col cols="2">
                                <v-btn @click="chooseImage()" color="primary" class="mb-2"> Upload Image
                                </v-btn>
                                <!-- Hidden File Input for Multiple Image Upload -->
                                <input :ref="'imageInput'" type="file" multiple accept="image/*" class="d-none"
                                    @change="onImagesSelected($event)" />
                            </v-col>

                            <!-- Image Preview -->
                            <v-col cols="1" class="image-container" v-for="(img, imgIndex) in form.product_images"
                                :key="imgIndex">
                                <!-- Image Display -->
                                <v-img :src="img.media_url || '/images/default.png'" max-width="100"
                                    class="mb-2"></v-img>

                                <!-- Delete Icon Overlay -->
                                <v-icon small color="red" @click="removeImage(imgIndex, img.product_image_id)"
                                    class="delete-icon">
                                    mdi-close-circle </v-icon>
                            </v-col>
                        </v-row>
                    </v-col>

                    <!-- Name Field -->
                    <v-col cols="12">
                        <v-text-field label="Name" variant="outlined" v-model="form.name" density="compact"
                            :error-messages="v$.form.name.$errors.map(e => e.$message)" @blur="v$.form.name.$touch()"
                            :rules="[(v) => !!v || 'Name is required']">
                        </v-text-field>
                    </v-col>

                    <!-- Category Fields -->
                    <v-col cols="6">
                        <v-autocomplete v-model="form.category_id" :items="category_list" label="Category"
                            variant="outlined" density="compact" item-title="name" item-value="id"
                            :error-messages="v$.form.category_id.$errors.map(e => e.$message)"
                            @blur="v$.form.category_id.$touch()">
                        </v-autocomplete>
                    </v-col>

                    <v-col cols="6">
                        <v-autocomplete v-model="form.sub_category_id" :items="sub_category_list" label="Sub Category"
                            variant="outlined" density="compact" item-title="name" item-value="id"
                            :error-messages="v$.form.sub_category_id.$errors.map(e => e.$message)"
                            @blur="v$.form.sub_category_id.$touch()">
                        </v-autocomplete>
                    </v-col>

                    <v-col :cols="is_machinery ? '4' : '6'" v-if="is_machinery">
                        <v-select label="Select Type" :items="type_list" v-model="form.type"
                            @change="changeProductType()" variant="outlined" density="compact" item-title="text"
                            item-value="value"></v-select>
                    </v-col>

                    <v-col :cols="is_machinery ? '4' : '6'">
                        <v-select label="Select Tax Type" :items="tax_type_list" v-model="form.tax_type"
                            variant="outlined" density="compact"></v-select>
                    </v-col>

                    <v-col :cols="is_machinery ? '4' : '6'">
                        <v-autocomplete v-model="form.tax_id" :items="tax_list" label="Select Tax" variant="outlined"
                            density="compact" item-title="tax_name" item-value="id">
                        </v-autocomplete>
                    </v-col>

                    <!-- Description Field -->
                    <v-col cols="12">
                        <v-textarea label="Description" variant="outlined" v-model="form.description" hide-details
                            auto-grow rows="2" density="compact">
                        </v-textarea>
                    </v-col>

                    <v-col :cols="is_duration ? '2' : '3'">
                        <v-text-field v-model="form.quantity" label="Quantity" variant="outlined" density="compact"
                            type="tel" @input="form.quantity = form.quantity.replace(/\D/g, '')" />
                    </v-col>
                    <v-col cols="3">
                        <v-text-field v-model="form.unit_price" label="Price" variant="outlined" density="compact"
                            type="tel" @input="form.unit_price = form.unit_price.replace(/\D/g, '')"
                            :error-messages="v$.form.unit_price.$errors.map(e => e.$message)"
                            @blur="v$.form.unit_price.$touch()" />
                    </v-col>
                    <v-col cols="2" v-if="is_duration && is_machinery">
                        <v-text-field v-model="form.duration" label="Duration(Hr)" variant="outlined" density="compact"
                            type="tel" @input="form.duration = form.duration.replace(/\D/g, '')" />
                    </v-col>
                    <v-col :cols="is_duration ? '2' : '3'">
                        <v-autocomplete v-model="form.unit_id" :items="unit_list" label="Select Unit" variant="outlined"
                            density="compact" item-title="name" item-value="id"
                            :error-messages="v$.form.unit_id.$errors.map(e => e.$message)"
                            @blur="v$.form.unit_id.$touch()">
                        </v-autocomplete>
                    </v-col>

                    <v-col cols="4">
                        <v-select label="Discount Type " :items="discount_type_list" v-model="form.discount_type"
                            variant="outlined" density="compact"></v-select>
                    </v-col>

                    <v-col cols="4">
                        <v-text-field v-model="form.discount" label="Discount" variant="outlined" density="compact"
                            type="tel" @input="form.discount = form.discount.replace(/\D/g, '')" />
                    </v-col>

                    <v-col cols="4">
                        <v-select label="Status" :items="status_list" v-model="form.status" variant="outlined"
                            density="compact" :error-messages="v$.form.status.$errors.map(e => e.$message)"
                            @blur="v$.form.status.$touch()">
                            ></v-select>
                    </v-col>
                    <v-col cols="6">
                        <v-text-field v-model="form.latitude" label="Latitude" variant="outlined" density="compact"
                            type="text" :error-messages="v$.form.latitude.$errors.map(e => e.$message)"
                            @blur="v$.form.latitude.$touch()"></v-text-field>
                    </v-col>

                    <v-col cols="6">
                        <v-text-field v-model="form.longitude" label="Longitude" variant="outlined" density="compact"
                            type="text" :error-messages="v$.form.longitude.$errors.map(e => e.$message)"
                            @blur="v$.form.longitude.$touch()"></v-text-field>
                    </v-col>

                    <!-- comment Field -->
                    <v-col cols="12">
                        <v-textarea label="comment" variant="outlined" v-model="form.comment" hide-details auto-grow
                            rows="2" density="compact">
                        </v-textarea>
                    </v-col>
                </v-row>
            </v-card-text>

            <!-- Divider -->
            <v-divider></v-divider>

            <!-- Actions Section -->
            <v-card-actions class="d-flex justify-end">
                <v-btn class="mx-2" :loading="loader" :disabled="loader" @click="submitForm()">
                    {{ $route.name == 'product.create' ? 'Save' : 'Update' }}
                </v-btn>
                <v-btn color="error" class="mx-2" @click="close">Close
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-container>
</template>
<script>
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import axios from 'axios';
import { EyeIcon, EditIcon, TrashIcon } from "vue-tabler-icons";
export default {
    components: { EyeIcon, EditIcon, TrashIcon },
    setup() {
        const v$ = useVuelidate();
        return { v$ };
    },
    data() {
        return {
            color_list: [
                { name: 'Red', code: '#FF0000' },
                { name: 'Green', code: '#008000' },
                { name: 'Blue', code: '#0000FF' },
                { name: 'Yellow', code: '#FFFF00' },
                { name: 'Black', code: '#000000' },
                { name: 'White', code: '#FFFFFF' },
                { name: 'Orange', code: '#FFA500' },
                { name: 'Purple', code: '#800080' },
                { name: 'Pink', code: '#FFC0CB' },
                { name: 'Brown', code: '#A52A2A' }
            ],
            discount_type_list: ['Fixed', 'Percentage'],
            tax_type_list: ['Inclusive', 'Exclusive'],
            status_list: ['Active', 'In-Active'],
            tax_list: [],
            image_url: null,
            form: {
                id: null,
                user_id: null,
                image: '',
                name: '',
                category_id: null,
                sub_category_id: null,
                description: '',
                type: 'Seller',
                discount_type: 'Fixed',
                discount: 0,
                tax_type: 'Exclusive',
                tax_id: null,
                status: 'Active',
                comment: '',
                quantity: 0,
                unit_price: 0,
                duration: 0,
                unit_id: null,
                store_id: null,
                product_images: [],
                latitude: null,
                longitude: null,
            },
            unit_list: [],
            category_list: [],
            sub_category_list: [],
            product_info: null,
            loader: false,
            is_machinery: false,
            is_duration: false,
            type_list: [
                // { value: 'Buyer', text: 'Buy' },
                { value: 'Seller', text: 'Sale' },
                { value: 'Rent', text: 'Rent' }],
            delete_images: [],
        };
    },

    validations() {
        return {
            form: {
                name: { required },
                category_id: { required },
                sub_category_id: { required },
                unit_price: { required },
                status: { required },
                unit_id: { required },
                latitude: { required },
                longitude: { required },
            }
        };
    },
    mounted() {
        this.getCategoryList();
        this.getTaxList();
        this.getUnitList();

        if (this.$route.name == 'product.edit' && this.$route.params.id) this.productInfo();

    },

    watch: {
        'form.category_id'(newVal) {
            if (newVal) {
                this.subCategoryList();
            }
        },

        'form.type'(newVal) {
            if (newVal) {
                this.changeProductType();
            }
        },
        'form.discount_type': 'resetDiscount',
        'form.discount': 'handleDiscountInput',
    },

    methods: {
        resetDiscount() {
            this.form.discount = 0;
        },

        handleDiscountInput() {
            if (this.form.discount_type === 'Percentage') {
                if (this.form.discount > 100) {
                    this.form.discount = 100;
                }
            } else if (this.form.discount_type === 'Fixed') {
            }
        },

        async getTaxList() {
            await axios.post('/api/dropdown-tax-list').then((response) => {
                this.tax_list = response.data.data;
            }).catch((error) => {
                let message = error.response ? error.response.data.message : "Something went wrong!";
                this.$store.dispatch("globalState/errorSnackBar", message);
            });
        },

        async getUnitList() {
            await axios.post('/api/dropdown-unit-list').then((response) => {
                this.unit_list = response.data.data;
            }).catch((error) => {
                let message = error.response ? error.response.data.message : "Something went wrong!";
                this.$store.dispatch("globalState/errorSnackBar", message);
            });
        },

        changeProductType() {
            this.is_duration = false;
            let info = this.type_list.find(item => item.value == this.form.type);
            if (info && info.value == 'Rent' && this.form.type == 'Rent') {
                this.is_duration = true;
            }
        },
        async getCategoryList() {
            await axios.post('/api/dropdown-category-list').then((response) => {
                this.category_list = response.data.data;
            }).catch((error) => {
                let message = error.response ? error.response.data.message : "Something went wrong!";
                this.$store.dispatch("globalState/errorSnackBar", message);
            });
        },

        async subCategoryList() {
            this.is_machinery = false;
            this.is_duration = false;
            this.form.type = 'Seller';
            if (!this.form.category_id) return;
            let category = this.category_list.find(item => item.id == this.form.category_id);
            if (category && category.name == 'Machinery') {
                this.is_machinery = true;
            }

            await axios.post('/api/dropdown-sub-category-list', { category_id: this.form.category_id })
                .then((response) => {
                    console.log(response.data);
                    this.sub_category_list = response.data.data;
                }).catch((error) => {
                    let message = error.response ? error.response.data.message : "Something went wrong!";
                    this.$store.dispatch("globalState/errorSnackBar", message);
                });
        },

        // Edit Function
        async productInfo() {
            if (!this.$route.params.id) return;
            try {
                const response = await axios.post('/api/edit-product-info', { id: this.$route.params.id });
                this.product_info = response.data.data;
                this.setFieldDate(this.product_info);
            } catch (error) {
                this.loader = false;
                let message = error.response ? error.response.data.message : "Something went wrong!";
                this.$store.dispatch("globalState/errorSnackBar", message);
            }
        },

        setFieldDate(product) {
            this.image_url = product.image ? product.image.image_url : null;
            this.form.id = product.id;
            this.form.user_id = product.user_id;
            this.form.image = null;
            this.form.name = product.name;
            this.form.category_id = product.category_id;
            this.form.sub_category_id = product.sub_category_id;
            this.form.description = product.description;
            this.form.type = product.type;
            if (this.form.type == 'Rent') {
                this.is_machinery = true;
                this.is_duration = true;
            }
            this.form.discount_type = product.discount_type;
            this.form.discount = product.discount;
            this.form.tax_type = product.tax_type;
            this.form.tax_id = product.tax_id;
            this.form.status = product.status;
            this.form.comment = product.comment;
            this.form.unit_id = product.unit_id;
            this.form.quantity = parseInt(product.quantity);
            this.form.unit_price = parseInt(product.unit_price);
            this.form.duration = parseInt(product.duration);
            this.form.latitude = product.latitude;
            this.form.longitude = product.longitude;
            this.form.store_id = product.store_id;
            this.form.product_images = [];

            if (product.product_images && product.product_images.length > 0) {
                product.product_images.forEach((image) => {
                    this.form.product_images.push({
                        product_image_id: image.id,
                        image_file: null,
                        media_url: image.image_url,
                        product_id: image.product_id,
                        user_id: image.user_id,
                    });
                });
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
            formData.append('product_id', this.form.id);
            formData.append('user_id', this.form.user_id);
            formData.append('name', this.form.name);
            formData.append('category_id', this.form.category_id);
            formData.append('sub_category_id', this.form.sub_category_id);
            formData.append('description', this.form.description);
            formData.append('type', this.form.type);
            formData.append('discount_type', this.form.discount_type);
            formData.append('discount', this.form.discount);
            formData.append('tax_type', this.form.tax_type);
            formData.append('tax_id', this.form.tax_id);
            formData.append('status', this.form.status);
            formData.append('comment', this.form.comment);
            formData.append('quantity', this.form.quantity);
            formData.append('unit_price', this.form.unit_price);
            formData.append('duration', this.form.type == 'Rent' ? this.form.duration : 0);
            formData.append('unit_id', this.form.unit_id);
            formData.append('latitude', this.form.latitude);
            formData.append('longitude', this.form.longitude);
            formData.append('store_id', this.form.store_id);
            this.delete_images.forEach((image) => {
                formData.append('delete_images[]', image);
            });

            this.form.product_images.forEach((image, imgIndex) => {
                if (image.image_file instanceof File) {
                    formData.append(`product_images[${imgIndex}][image_file]`, image.image_file);
                }
            });

            // Send the form data to the API
            const url = this.$route.name == 'product.create' ? '/api/product-create' : '/api/product-update';
            await axios.post(url, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
                .then((response) => {
                    this.$store.dispatch("globalState/successSnackBar", response.data.message);
                    this.loader = false;
                    this.close();
                })
                .catch((error) => {
                    this.loader = false;
                    let message = error.response ? error.response.data.message : "Something went wrong!";
                    this.$store.dispatch("globalState/errorSnackBar", message);
                });
        },



        // Trigger file input click
        chooseImage() {
            const inputElement = this.$refs[`imageInput`];
            if (inputElement && inputElement instanceof Array) {
                inputElement[0].click();
            } else {
                inputElement.click();
            }
        },

         onImagesSelected(event) {
            const files = event.target.files;

            if (!files.length) {
                return;
            }

            for (const file of files) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.product_images.push({
                        product_image_id: null,
                        image_file: file,
                        media_url: e.target.result,
                        product_id: null,
                        user_id: null,
                    });
                };
                reader.readAsDataURL(file);
            }
        },
        // Remove selected image from the list
        removeImage(imageIndex, product_image_id = null) {
            this.form.product_images.splice(imageIndex, 1);
            if (product_image_id) this.delete_images.push(product_image_id);
        },

        close() {
            this.reset();
            this.$router.push({ name: 'products' });
        },

        reset() {
            this.v$.$reset();
            this.image_url = null;
            this.delete_images = [];
            this.form = {
                id: null,
                user_id: null,
                image: '',
                name: '',
                category_id: null,
                sub_category_id: null,
                description: '',
                type: 'Seller',
                discount_type: 'Fixed',
                discount: 0,
                tax_type: 'Exclusive',
                tax_id: null,
                status: 'Active',
                comment: '',
                quantity: 0,
                unit_price: 0,
                product_images: [],
                duration: 0,
                unit_id: null,
            };
        }
    }
};
</script>

<style scoped>
.image-container {
    position: relative;
    /* Make the container relative to place the icon correctly */
}

.delete-icon {
    position: absolute;
    top: 5px;
    right: 5px;
    cursor: pointer;
}
</style>

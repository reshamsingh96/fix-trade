<template>
    <v-container class="mt-3 my-2" fluid>
        <v-card class="mx-auto p-3" elevation="10">
            <v-card-title>
                <h3>{{ $route.name == 'labor.create' ? "Labor Create" : 'Labor Edit' }}</h3>
            </v-card-title>

            <v-divider></v-divider>
            <v-card-text>
                <div class="d-flex align-center mt-3 mb-4">
                    <v-img :src="image_url || '/images/profile_image.png'" max-width="150" rounded="lg">
                    </v-img>
                    <v-btn class="ml-2" variant="text" icon size="small" color="error" @click="removeImage"
                        v-if="image_url">
                        <TrashIcon />
                    </v-btn>
                </div>
                
                <v-row>
                    <!-- File Input with Image Size Display -->
                    <v-col cols="12" class="text-center">
                        <v-file-input id="image-selected-empty" :clearable="false" v-model="form.image"
                            label="Choose Image" accept="image/*" variant="outlined" @change="onImageSelected" show-size
                            truncate-length="40"></v-file-input>
                    </v-col>

                    <!-- Work Title Field -->
                    <v-col cols="6">
                        <v-text-field label="Work Title" variant="outlined" v-model="form.work_title" density="compact"
                            :error-messages="v$.form.work_title.$errors.map(e => e.$message)"
                            @blur="v$.form.work_title.$touch()" :rules="[(v) => !!v || 'Work Title is required']">
                        </v-text-field>
                    </v-col>

                    <!-- Labor Name Field -->
                    <v-col cols="6">
                        <v-text-field label="Labor Name" variant="outlined" v-model="form.labor_name" density="compact"
                            :error-messages="v$.form.labor_name.$errors.map(e => e.$message)"
                            @blur="v$.form.labor_name.$touch()" :rules="[(v) => !!v || 'Labor Name is required']">
                        </v-text-field>
                    </v-col>

                    <!-- Phone Field -->
                    <v-col cols="6">
                        <v-text-field label="Phone" variant="outlined" v-model="form.phone" density="compact"
                            @input="sanitizeInteger" :error-messages="v$.form.phone.$errors.map(e => e.$message)"
                            @blur="v$.form.phone.$touch()">
                        </v-text-field>
                    </v-col>

                    <!-- Status Field -->
                    <v-col cols="6">
                        <v-select label="Status" :items="['Active', 'In-Active']" v-model="form.status"
                            variant="outlined" density="compact"
                            :error-messages="v$.form.status.$errors.map(e => e.$message)"
                            @blur="v$.form.status.$touch()"></v-select>
                    </v-col>

                    <!-- Select Day Field -->
                    <v-col cols="12">
                        <v-select chips label="Select Working Days" :items="day_list" multiple v-model="form.days"
                            @change="changeWorkingDayList" variant="outlined" density="compact" item-title="text"
                            item-value="value" :error-messages="v$.form.days.$errors.map(e => e.$message)"
                            @blur="v$.form.days.$touch()"></v-select>
                    </v-col>

                    <!-- <v-card outlined>
                        <v-card-text> -->
                    <!-- Name and Price -->
                    <v-col v-for="(working, index) in form.labor_day_working" :key="index" cols="12">
                        <v-row>
                            <!-- Day Name -->
                            <v-col cols="2">
                                <v-text-field v-model="working.day_name" label="Day Name" readonly variant="outlined"
                                    density="compact" />
                            </v-col>

                            <!-- Start Time Picker -->
                            <v-col cols="3">
                                <v-text-field type="time" v-model="working.start_time" label="Start Time"
                                    variant="outlined" density="compact"></v-text-field>
                            </v-col>

                            <!-- End Time Picker -->
                            <v-col cols="3">
                                <v-text-field type="time" v-model="working.end_time" label="End Time" variant="outlined"
                                    density="compact"></v-text-field>
                            </v-col>

                            <!-- Break Minutes (Select) -->
                            <v-col cols="2">
                                <v-select v-model="working.break_minute" :items="[0, 15, 30, 45, 60, 75, 90, 105, 120]"
                                    label="Break (minutes)" variant="outlined" density="compact"></v-select>
                            </v-col>

                            <!-- Per Hour Amount -->
                            <v-col cols="2">
                                <v-text-field v-model="working.per_hour_amount" label="Per Hour Amount"
                                    variant="outlined" density="compact" type="tel"
                                    @input="working.per_hour_amount = working.per_hour_amount.replace(/\D/g, '')"
                                    :rules="[v => /^\d+$/.test(v) || 'Enter a valid integer']">
                                </v-text-field>
                            </v-col>
                        </v-row>
                    </v-col>

                    <!-- </v-card-text>
                    </v-card> -->

                    <!--  Choose labor work Images -->
                    <v-col cols="12" class="mt-2">
                        <v-row>
                            <v-col cols="2">
                                <v-btn @click="chooseLaborImage()" color="primary" class="mb-2">Upload Image</v-btn>
                                <input ref="laborImageInput" type="file" multiple accept="image/*" class="d-none"
                                    @change="onLaborImagesSelected($event)" />
                            </v-col>

                            <v-col cols="1" class="image-container" v-for="(img, imgIndex) in form.labour_images"
                                :key="imgIndex">
                                <v-img :src="img.image_url || '/images/default.png'" max-width="100"
                                    class="mb-2"></v-img>
                                <v-icon small color="red" @click="removeLaborImage(imgIndex)" class="delete-icon">
                                    mdi-close-circle </v-icon>
                            </v-col>
                        </v-row>
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
                    {{ $route.name == 'labor.create' ? 'Save' : 'Update' }}
                </v-btn>
                <v-btn color="error" class="mx-2" @click="close">Close
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-container>
</template>
<script>
import useVuelidate from '@vuelidate/core';
import { required, minLength } from '@vuelidate/validators';
import axios from 'axios';

export default {
    setup() {
        const v$ = useVuelidate();
        return { v$ };
    },
    data() {
        return {
            image_url: null,
            form: {
                id: null,
                user_id: null,
                work_title: '',
                labor_name: '',
                phone: '',
                status: 'Active',
                description: '',
                image: '',
                days: [],
                labor_day_working: [],
                // {
                // labor_day_working_id: null,
                // labor_id: null,
                // day_name: '',
                // day_number: 0,
                // start_time: '08:00',
                // end_time: '17:20',
                // break_minute: 0,
                // per_hour_amount: 0,
                // working_hour: 0,
                // day_amount: 0,
                // }
                // ],
                labour_images: [],
            },
            labor_info: null,
            loader: false,
            day_list: [
                { value: 1, text: 'Monday' },
                { value: 2, text: 'Tuesday' },
                { value: 3, text: 'Wednesday' },
                { value: 4, text: 'Thursday' },
                { value: 5, text: 'Friday' },
                { value: 6, text: 'Saturday' },
                { value: 0, text: 'Sunday' }
            ]
        };
    },

    validations() {
        return {
            form: {
                work_title: { required },
                labor_name: { required },
                phone: { required },
                status: { required },
                days: {
                    required,
                    minLength: minLength(1)
                }
            }
        };
    },
    mounted() {
        if (this.$route.name == 'labor.edit' && this.$route.params.id) this.productInfo();
    },

    watch: {
        'form.days'(newVal) {
            if (newVal) {
                this.changeWorkingDayList();
            }
        },
    },

    methods: {
        sanitizeInteger(event) {
            const sanitizedValue = event.target.value.replace(/\D/g, '');
            event.target.value = sanitizedValue;
        },

        // Edit Function
        async productInfo() {
            if (!this.$route.params.id) return;
            try {
                const response = await axios.post('/api/single-labor-info', { id: this.$route.params.id });
                this.labor_info = response.data.data;
                this.setFieldDate(this.labor_info);
            } catch (error) {
                let message = error.response ? error.response.data.message : "Something went wrong!";
                this.$store.dispatch("globalState/errorSnackBar", message);
            }
        },

        setFieldDate(labor) {
            this.image_url = labor.image_url ? labor.image_url : null;

            this.form.id = labor.id;
            this.form.user_id = labor.user_id;
            this.form.work_title = labor.work_title;
            this.form.labor_name = labor.labor_name;
            this.form.phone = labor.phone;
            this.form.status = labor.status;
            this.form.description = labor.description;
            this.form.image = null;

            labor.work_images.forEach((image) => {
                this.form.labour_images.push({
                    labor_image_id: image.id,
                    image_file: null,
                    image_url: image.image_url,
                    labor_id: image.labor_id,
                });
            });

            this.form.labor_day_working = [];
            let days = [];
            labor.working_day.forEach((item) => {
                console.log(item.day_name, '  ', item.day_number);
                days.push(item.day_number);
                this.form.labor_day_working.push({
                    labor_day_working_id: item.id,
                    labor_id: item.labor_id,
                    day_name: item.day_name,
                    day_number: item.day_number,
                    start_time: item.start_time,
                    end_time: item.end_time,
                    break_minute: item.break_minute,
                    per_hour_amount: item.per_hour_amount,
                    // working_hour: item.working_hour,
                    // day_amount: item.day_amount,
                });
            });
            this.form.days = days;
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

        changeWorkingDayList() {
            console.log("Selected days:", JSON.stringify(this.form.days));

            // Step 1: Filter out any existing rows in labor_day_working that are not selected in days
            this.form.labor_day_working = this.form.labor_day_working.filter(item => {
                return this.form.days.includes(item.day_number);
            });

            // Step 2: Add new days or update existing ones
            this.form.days.forEach(day_number => {
                console.log(day_number);

                let old_info = this.form.labor_day_working.find(value => day_number == value.day_number);
                // if (!old_info && this.$route.name == 'labor.edit' && this.$route.params.id) {
                //     old_info = this.labor_info.working_day.find(value => day_number == value.day_number);
                // }
                if (!old_info) {
                    this.form.labor_day_working.push({
                        labor_day_working_id: null,
                        labor_id: this.$route.name == 'labor.edit' && this.$route.params.id ? this.$route.params.id : null,
                        day_name: this.getDayName(day_number),
                        day_number: day_number,
                        start_time: '08:00',
                        end_time: '17:00',
                        break_minute: 0,
                        per_hour_amount: 0,
                        working_hour: 0,
                        day_amount: 0,
                    });
                }
            });
        },

        // Trigger file input click
        chooseLaborImage() {
            const inputElement = this.$refs[`laborImageInput`];
            if (inputElement && inputElement instanceof Array) {
                inputElement[0].click();
            } else {
                inputElement.click();
            }
        },

        onLaborImagesSelected(event) {
            const files = event.target.files;

            if (!files.length) { return; }

            for (const file of files) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.labour_images.push({
                        labor_image_id: null,
                        image_file: file,
                        image_url: e.target.result,
                        labor_id: null,
                    });
                };
                reader.readAsDataURL(file);
            }
        },

        // Remove selected image from the list
        removeLaborImage(imageIndex) {
            this.form.labour_images.splice(imageIndex, 1);
        },

        formatTimeTo24Hours(time) {
            const [hours, minutes] = time.split(':');
            return `${hours.padStart(2, '0')}:${minutes}`;
        },

        async submitForm() {
            this.v$.$touch();
            if (this.v$.$invalid) {
                this.$store.dispatch("globalState/errorSnackBar", "Form is invalid!");
                return;
            }

            this.loader = true;
            const formData = new FormData();

            // Append main product data
            formData.append('id', this.form.id);
            formData.append('work_title', this.form.work_title);
            formData.append('labor_name', this.form.labor_name);
            formData.append('status', this.form.status);
            formData.append('phone', this.form.phone);
            formData.append('user_id', this.form.user_id);
            formData.append('description', this.form.description);

            // Append main product image if available
            if (this.form.image) {
                formData.append('image', this.form.image);
            } else {
                formData.append('image', null);
            }

            // Append and their images Up-coming
            this.form.labor_day_working.forEach((work, index) => {
                let start_time = this.formatTimeTo24Hours(work.start_time);
                let end_time = this.formatTimeTo24Hours(work.end_time);
                formData.append(`labor_day_working[${index}][labor_day_working_id]`, work.labor_day_working_id);
                formData.append(`labor_day_working[${index}][labor_id]`, work.labor_id);
                formData.append(`labor_day_working[${index}][day_name]`, work.day_name);
                formData.append(`labor_day_working[${index}][day_number]`, work.day_number);
                formData.append(`labor_day_working[${index}][start_time]`, start_time);
                formData.append(`labor_day_working[${index}][end_time]`, end_time);
                formData.append(`labor_day_working[${index}][break_minute]`, work.break_minute);
                formData.append(`labor_day_working[${index}][per_hour_amount]`, work.per_hour_amount);
            });

            // let info = this.form.labor_day_working[0];
            // this.form.days.forEach((day, index) => {
            //     let start_time = this.formatTimeTo24Hours(info.start_time);
            //     let end_time = this.formatTimeTo24Hours(info.end_time);

            //     formData.append(`labor_day_working[${index}][labor_day_working_id]`, info.labor_day_working_id);
            //     formData.append(`labor_day_working[${index}][labor_id]`, info.labor_id);
            //     formData.append(`labor_day_working[${index}][day_name]`, this.getDayName(parseInt(day)));
            //     formData.append(`labor_day_working[${index}][day_number]`, day);
            //     formData.append(`labor_day_working[${index}][start_time]`, start_time);
            //     formData.append(`labor_day_working[${index}][end_time]`, end_time);
            //     formData.append(`labor_day_working[${index}][break_minute]`, info.break_minute);
            //     formData.append(`labor_day_working[${index}][per_hour_amount]`, info.per_hour_amount);
            // });

            if (this.form.labour_images.length == 0) {
                formData.append(`labour_images`, []);
            }

            // Append images
            this.form.labour_images.forEach((image, imgIndex) => {
                if (image.image_file instanceof File) {
                    formData.append(`labour_images[${imgIndex}][image_file]`, image.image_file);
                }
                formData.append(`labour_images[${imgIndex}][labor_image_id]`, image.labor_image_id);
                formData.append(`labour_images[${imgIndex}][image_url]`, image.image_url);
                formData.append(`labour_images[${imgIndex}][labor_id]`, image.labor_id);
            });

            // Send the form data to the API
            const url = this.$route.name == 'labor.create' ? '/api/labor-create' : '/api/labor-update';
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

        close() {
            this.reset();
            this.$router.push({ name: 'labor' });
        },

        reset() {
            this.v$.$reset();
            this.image_url = null;
            this.form = {
            };
        },

        getDayName(dayNumber) {
            switch (dayNumber) {
                case 0:
                    return 'Sunday';
                case 1:
                    return 'Monday';
                case 2:
                    return 'Tuesday';
                case 3:
                    return 'Wednesday';
                case 4:
                    return 'Thursday';
                case 5:
                    return 'Friday';
                case 6:
                    return 'Saturday';
                default:
                    return null;
            }
        },

        getDayNumber(day_name) {
            switch (day_name) {
                case 'Sunday':
                    return 0;
                case 'Monday':
                    return 1;
                case 'Tuesday':
                    return 2;
                case 'Wednesday':
                    return 3;
                case 'Thursday':
                    return 4;
                case 'Friday':
                    return 5;
                case 'Saturday':
                    return 6;
                default:
                    return null;
            }
        }
    },
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

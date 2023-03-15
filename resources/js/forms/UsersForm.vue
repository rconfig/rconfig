<template>
    <loading-spinner :showSpinner="isLoading"></loading-spinner>
    <form novalidate class="pf-c-form" v-if="!isLoading">
        <input id="editid" name="editid" type="hidden" :value="viewstate.editid" />
        <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
                <label class="pf-c-form__label" for="form-demo-basic-name">
                    <span class="pf-c-form__label-text">Name</span>
                    <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                </label>
            </div>
            <div class="pf-c-form__group-control">
                <input class="pf-c-form-control" required type="text" id="name" name="name" v-model="model.name" :aria-invalid="errors.name ? true : false" autocomplete="off" />
                <p v-if="errors.name" class="pf-c-form__helper-text pf-m-error">
                    {{ errors.name[0] }}
                </p>
                <!-- <p class="pf-c-form__helper-text">Please provide a user name</p> -->
            </div>
        </div>
        <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
                <label class="pf-c-form__label" for="form-demo-basic-name">
                    <span class="pf-c-form__label-text">Username</span>
                    <!-- <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span> -->
                </label>
            </div>
            <div class="pf-c-form__group-control">
                <input class="pf-c-form-control" required type="text" id="username" name="username" v-model="model.username" :aria-invalid="errors.username ? true : false" autocomplete="off" />
                <p v-if="errors.username" class="pf-c-form__helper-text pf-m-error">
                    {{ errors.username[0] }}
                </p>
                <!-- <p class="pf-c-form__helper-text">Please provide a user username</p> -->
            </div>
        </div>
        <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
                <label class="pf-c-form__label" for="form-demo-basic-name">
                    <span class="pf-c-form__label-text">Email</span>
                    <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                </label>
            </div>
            <div class="pf-c-form__group-control">
                <input class="pf-c-form-control" required type="email" id="email" name="email" v-model="model.email" :aria-invalid="errors.email ? true : false" autocomplete="off" />
                <p v-if="errors.email" class="pf-c-form__helper-text pf-m-error">
                    {{ errors.email[0] }}
                </p>
                <!-- <p class="pf-c-form__helper-text">Please provide a user name</p> -->
            </div>
        </div>
        <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
                <label class="pf-c-form__label" for="form-demo-basic-name">
                    <span class="pf-c-form__label-text">Password</span>
                    <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                </label>
            </div>
            <div class="pf-c-form__group-control">
                <input
                    class="pf-c-form-control"
                    required
                    type="password"
                    id="password"
                    name="password"
                    v-model="model.password"
                    :aria-invalid="errors.password || repeat_password_match_fail ? true : false"
                    @keyup="matchPassword"
                />
                <p v-if="errors.password" class="pf-c-form__helper-text pf-m-error">
                    {{ errors.password[0] }}
                </p>
                <p v-if="repeat_password_match_fail[0] === '' || repeat_password_match_pass[0] === ''" class="pf-c-form__helper-text">
                    Password should contain at least 8 chacters, one lowercase letter, one uppercase letter, One number, and one special character.
                </p>
                <p v-if="repeat_password_match_fail" class="pf-c-form__helper-text pf-m-error">
                    {{ repeat_password_match_fail[0] }}
                </p>
                <p v-if="repeat_password_match_pass" class="pf-c-form__helper-text pf-m-success">
                    {{ repeat_password_match_pass[0] }}
                </p>
            </div>
        </div>
        <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
                <label class="pf-c-form__label" for="form-demo-basic-name">
                    <span class="pf-c-form__label-text">Repeat Password</span>
                    <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                </label>
            </div>
            <div class="pf-c-form__group-control">
                <input
                    class="pf-c-form-control"
                    required
                    type="password"
                    id="repeat_password"
                    name="repeat_password"
                    v-model="model.repeat_password"
                    :aria-invalid="errors.repeat_password || repeat_password_match_fail ? true : false"
                    @keyup="matchPassword"
                />
                <p v-if="errors.repeat_password" class="pf-c-form__helper-text pf-m-error">
                    {{ errors.repeat_password[0] }}
                </p>

                <!-- <p class="pf-c-form__helper-text">Please provide a user name</p> -->
            </div>
        </div>
        <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
                <label class="pf-c-form__label" for="form-demo-basic-name">
                    <span class="pf-c-form__label-text">User Role</span>
                    <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                </label>
            </div>
            <div class="pf-c-form__group-control">
                <div class="pf-c-select pf-m-expanded">
                    <span id="select-single-expanded-selected-label" hidden>Choose a role</span>
                    <button class="pf-c-select__toggle" type="button" @click="showRoleOptions = !showRoleOptions">
                        <div class="pf-c-select__toggle-wrapper">
                            <span class="pf-c-select__toggle-text" v-text="model.role ? model.role : 'Choose a role'"></span>
                        </div>
                        <span class="pf-c-select__toggle-arrow">
                            <i class="fas fa-caret-down" aria-hidden="true"></i>
                        </span>
                    </button>

                    <ul class="pf-c-select__menu" role="listbox" aria-labelledby="select-single-expanded-label" v-if="showRoleOptions ? 'hidden' : ''">
                        <li role="presentation">
                            <button
                                class="pf-c-select__menu-item"
                                role="option"
                                @click.prevent="
                                    model.role = 'Admin';
                                    showRoleOptions = false;
                                "
                            >
                                Admin
                                <span class="pf-c-select__menu-item-icon" v-if="model.role === 'Admin'">
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                </span>
                            </button>
                        </li>
                        <li role="presentation">
                            <button
                                class="pf-c-select__menu-item"
                                role="option"
                                @click.prevent="
                                    model.role = 'User';
                                    showRoleOptions = false;
                                "
                            >
                                User
                                <span class="pf-c-select__menu-item-icon" v-if="model.role === 'User'">
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                </span>
                            </button>
                        </li>
                    </ul>
                </div>

                <p v-if="errors.role" class="pf-c-form__helper-text pf-m-error">
                    {{ errors.role[0] }}
                </p>
                <!-- <p class="pf-c-form__helper-text">Please provide a user name</p> -->
            </div>
        </div>
        <div class="pf-c-form__group pf-m-action">
            <div class="pf-c-form__group-control">
                <div class="pf-c-form__actions">
                    <button class="pf-c-button pf-m-primary" type="submit" @click.prevent="saveModels">Save</button>
                    <button class="pf-c-button pf-m-link" type="button" @click="close">Cancel</button>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
import { ref, onMounted, watchEffect, watch } from 'vue';
import useModels from '../composables/ModelsFactory';
import LoadingSpinner from '../components/LoadingSpinner.vue';

export default {
    props: {
        viewstate: {
            type: Object
        }
    },
    emits: ['closeDrawer', 'formsubmitted'],

    components: {
        LoadingSpinner
    },

    setup(props, { emit }) {
        const showRoleOptions = ref(false);
        const formtype = ref(props.viewstate.editid === 0 ? 'add' : 'edit');
        const { errors, model, clearModel, updateModel, getModel, storeModel, isLoading } = useModels(props.viewstate.modelName, props.viewstate.modelObject);

        onMounted(() => {
            getModel(props.viewstate.editid);
        });

        const saveModels = async () => {
            if (props.viewstate.editid != 0) {
                await updateModel(model);
            } else {
                await storeModel(model);
            }

            if (errors.value === '') {
                emit('formsubmitted', props.viewstate.pagenamesingle + ' ' + formtype.value + 'ed!');
                close();
            }
        };

        function close() {
            emit('closeDrawer');
        }

        // Specifically for the Users Add/Edit Form
        const repeat_password_match_pass = ref('');
        const repeat_password_match_fail = ref('');
        function matchPassword() {
            if (model.repeat_password === model.password) {
                repeat_password_match_fail.value = '';
                repeat_password_match_pass.value = ['Passwords match'];
            } else {
                repeat_password_match_pass.value = '';
                repeat_password_match_fail.value = ['Passwords do not match'];
            }
        }
        // Specifically for the Users Add/Edit Form

        return { showRoleOptions, close, errors, model, saveModels, clearModel, matchPassword, repeat_password_match_fail, repeat_password_match_pass, isLoading };
    }
};
</script>

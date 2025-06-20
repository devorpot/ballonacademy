<template>
 
  <div class="row">
      <div class="form-group mb-3 col-12 col-sm-6" :class="classObject">
        <div class="form-floating">

          <input
            :id="id"
            type="password"
            v-model="passwordValue"
            class="form-control"
            :placeholder="placeholder"
            :class="{ 'is-invalid': (showValidation && validationMessage) || formError }"
            @blur="() => $emit('blur', 'password')"
          />
           <label :for="id">{{ label }} <strong v-if="required">*</strong></label>
          <div v-if="(showValidation && validationMessage) || formError" class="invalid-feedback">
            {{ formError || validationMessage }}
          </div>
      </div>
    </div>

    <div class="form-group col-12 col-sm-6 ">
        <div class="form-floating">
          <input
              :id="confirmId"
              type="password"
              v-model="passwordConfirmationValue"
              class="form-control"
              :placeholder="'Confirme ' + label"
              :class="{ 'is-invalid': (showValidation && confirmValidationMessage) || confirmFormError }"
              @blur="() => $emit('blur', 'password_confirmation')"
            />
            <label :for="confirmId">Confirmar {{ label }}</label>
            <div v-if="(showValidation && confirmValidationMessage) || confirmFormError" class="invalid-feedback">
              {{ confirmFormError || confirmValidationMessage }}
            </div>
      </div>
    </div>
  </div>
 
</template>

<script>
export default {
  props: {
    id: String,
    confirmId: String,
    label: String,
    password: String,
    passwordConfirmation: String,
    placeholder: String,
    required: Boolean,
    showValidation: Boolean,
    formError: String,
    confirmFormError: String,
    validateFunction: Function,
    validateConfirmFunction: Function,
    classObject: String,
  },
  emits: ["update:password", "update:passwordConfirmation", "blur"],
  computed: {
    passwordValue: {
      get() {
        return this.password;
      },
      set(value) {
        this.$emit("update:password", value);
      }
    },
    passwordConfirmationValue: {
      get() {
        return this.passwordConfirmation;
      },
      set(value) {
        this.$emit("update:passwordConfirmation", value);
      }
    },
    validationMessage() {
      return this.validateFunction ? this.validateFunction() : "";
    },
    confirmValidationMessage() {
      return this.validateConfirmFunction ? this.validateConfirmFunction() : "";
    }
  }
};
</script>

<template>
  <div :class="$attrs.class">
    <label v-if="label" class="form-label" :for="id">{{ label }}:</label>
    <input :id="id" ref="inputRef" v-bind="{ ...$attrs, class: null }" class="form-input" :class="{ error: error }" :type="type" @input="$emit('update:modelValue', $event.target.value)" />
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script>
import { v4 as uuid } from 'uuid'
import { useCurrencyInput } from 'vue-currency-input'

export default {
  inheritAttrs: false,
  props: {
    id: {
      type: String,
      default() {
        return `text-input-${uuid()}`
      },
    },
    type: {
      type: String,
      default: 'text',
    },
    error: String,
    label: String,
    modelValue: Number,
    options: Object,
  },
  setup(props) {
    const { inputRef } = useCurrencyInput(props.options)

    return { inputRef }
  },
  emits: ['update:modelValue'],
}
</script>

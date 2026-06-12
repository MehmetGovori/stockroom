<script setup lang="ts">
const props = defineProps<{ modelValue: number; min?: number; max?: number }>()
const emit = defineEmits<{ 'update:modelValue': [value: number] }>()

function clamp(value: number): number {
  const lo = props.min ?? 0
  const hi = props.max ?? Number.MAX_SAFE_INTEGER
  return Math.max(lo, Math.min(hi, value))
}

function step(delta: number) {
  emit('update:modelValue', clamp(props.modelValue + delta))
}

function onInput(event: Event) {
  const raw = Number((event.target as HTMLInputElement).value)
  emit('update:modelValue', Number.isFinite(raw) ? clamp(Math.round(raw)) : props.min ?? 0)
}
</script>

<template>
  <div class="stepper">
    <button
      type="button"
      class="stepper__btn"
      :disabled="modelValue <= (min ?? 0)"
      @click="step(-1)"
      aria-label="Decrease"
    >
      &minus;
    </button>
    <input
      class="stepper__input mono"
      type="number"
      :value="modelValue"
      :min="min"
      :max="max"
      @input="onInput"
    />
    <button
      type="button"
      class="stepper__btn"
      :disabled="max !== undefined && modelValue >= max"
      @click="step(1)"
      aria-label="Increase"
    >
      +
    </button>
  </div>
</template>

<style scoped>
.stepper {
  display: inline-flex;
  align-items: center;
  border: 1px solid var(--line-strong);
  border-radius: var(--radius);
  background: var(--paper-raised);
  overflow: hidden;
}

.stepper__btn {
  width: 34px;
  height: 36px;
  border: none;
  background: transparent;
  color: var(--ink);
  font-size: 18px;
  cursor: pointer;
  transition: background 0.12s ease;
}

.stepper__btn:hover:not(:disabled) {
  background: var(--paper-sunk);
}

.stepper__btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.stepper__input {
  width: 46px;
  height: 36px;
  border: none;
  border-left: 1px solid var(--line);
  border-right: 1px solid var(--line);
  background: transparent;
  text-align: center;
  font-size: 14px;
  font-weight: 600;
  color: var(--ink);
}

.stepper__input::-webkit-outer-spin-button,
.stepper__input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.stepper__input {
  -moz-appearance: textfield;
  appearance: textfield;
}

.stepper__input:focus {
  outline: none;
}
</style>

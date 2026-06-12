<script setup lang="ts">
import { computed } from 'vue'
import { stockLevel, stockLabel } from '../lib/format'

const props = defineProps<{ quantity: number }>()

const level = computed(() => stockLevel(props.quantity))
const label = computed(() => stockLabel(props.quantity))
</script>

<template>
  <span class="tag" :class="`tag--${level}`">{{ label }}</span>
</template>

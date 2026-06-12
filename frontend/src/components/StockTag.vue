<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { stockLevel } from '../lib/format'

const props = defineProps<{ quantity: number }>()
const { t } = useI18n()

const level = computed(() => stockLevel(props.quantity))

const label = computed(() => {
  if (level.value === 'bad') return t('stock.out')
  if (level.value === 'warn') return t('stock.low', { count: props.quantity })
  return t('stock.inStock', { count: props.quantity })
})
</script>

<template>
  <span class="tag" :class="`tag--${level}`">{{ label }}</span>
</template>

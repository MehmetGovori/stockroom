<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { fetchOrders } from '../api/orders'
import { money } from '../lib/format'
import type { Order } from '../types'

const { t } = useI18n()
const orders = ref<Order[]>([])
const loading = ref(true)
const failed = ref(false)

onMounted(async () => {
  try {
    orders.value = await fetchOrders()
  } catch {
    failed.value = true
  } finally {
    loading.value = false
  }
})

function summarize(order: Order): string {
  return order.items.map((item) => `${item.quantity}× ${item.product_name}`).join(', ')
}
</script>

<template>
  <section>
    <header class="head rise">
      <p class="eyebrow">{{ t('orders.eyebrow') }}</p>
      <h1>{{ t('orders.title') }}</h1>
    </header>

    <p v-if="failed" class="banner banner--bad">{{ t('orders.loadError') }}</p>
    <div v-if="loading" class="state">{{ t('orders.loading') }}</div>

    <div v-else-if="orders.length === 0" class="state">
      {{ t('orders.empty') }} <RouterLink to="/" class="link">{{ t('orders.placeFirst') }}</RouterLink>
    </div>

    <div v-else class="list">
      <RouterLink
        v-for="(order, index) in orders"
        :key="order.id"
        :to="`/orders/${order.id}`"
        class="card order rise"
        :style="{ animationDelay: `${index * 0.04}s` }"
      >
        <div class="order__main">
          <div class="order__top">
            <span class="order__id mono">#{{ order.id }}</span>
            <span class="tag tag--ok">{{ t(`status.${order.status}`) }}</span>
          </div>
          <p class="order__items">{{ summarize(order) }}</p>
        </div>
        <div class="order__total mono">{{ money(order.total) }}</div>
      </RouterLink>
    </div>
  </section>
</template>

<style scoped>
.head {
  margin-bottom: 26px;
}

.head h1 {
  font-size: 38px;
  margin-top: 6px;
}

.state {
  padding: 60px 20px;
  text-align: center;
  color: var(--ink-faint);
  border: 1px dashed var(--line-strong);
  border-radius: var(--radius);
}

.link {
  color: var(--accent);
  font-weight: 600;
}

.banner {
  padding: 12px 16px;
  border-radius: var(--radius);
  margin-bottom: 18px;
  background: var(--bad-bg);
  color: var(--bad);
}

.list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.order {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  padding: 18px 22px;
  transition: transform 0.14s ease, box-shadow 0.14s ease;
}

.order:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-pop);
}

.order__top {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 6px;
}

.order__id {
  font-weight: 700;
  font-size: 16px;
}

.order__items {
  margin: 0;
  color: var(--ink-soft);
  font-size: 14px;
}

.order__total {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 22px;
  white-space: nowrap;
}
</style>

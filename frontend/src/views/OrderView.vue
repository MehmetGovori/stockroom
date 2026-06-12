<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { fetchOrder } from '../api/orders'
import { money } from '../lib/format'
import type { Order } from '../types'

const props = defineProps<{ id: string }>()
const { t } = useI18n()

const order = ref<Order | null>(null)
const loading = ref(true)
const failed = ref(false)

onMounted(async () => {
  try {
    order.value = await fetchOrder(Number(props.id))
  } catch {
    failed.value = true
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <section class="wrap">
    <div v-if="loading" class="state">{{ t('confirmation.loading') }}</div>
    <div v-else-if="failed" class="state">{{ t('confirmation.notFound') }}</div>

    <div v-else-if="order">
      <div class="confirm rise">
        <span class="check" aria-hidden="true">✓</span>
        <p class="eyebrow">{{ t('confirmation.confirmed') }}</p>
        <h1 class="confirm__id mono">{{ t('confirmation.orderNo', { id: order.id }) }}</h1>
        <p class="confirm__sub">{{ t('confirmation.sub') }}</p>
      </div>

      <div class="card receipt rise" style="animation-delay: 0.08s">
        <div class="receipt__head">
          <span class="eyebrow">{{ t('confirmation.lineItems') }}</span>
          <span class="tag tag--ok">{{ t(`status.${order.status}`) }}</span>
        </div>
        <table class="items">
          <tbody>
            <tr v-for="item in order.items" :key="item.id">
              <td>
                <span class="item__name">{{ item.product_name }}</span>
              </td>
              <td class="mono qty">{{ item.quantity }} × {{ money(item.unit_price) }}</td>
              <td class="mono line">{{ money(item.line_total) }}</td>
            </tr>
          </tbody>
        </table>
        <div class="receipt__total">
          <span>{{ t('confirmation.total') }}</span>
          <span class="mono">{{ money(order.total) }}</span>
        </div>
      </div>

      <div class="actions rise" style="animation-delay: 0.12s">
        <RouterLink to="/" class="btn btn--accent">{{ t('confirmation.backToCatalog') }}</RouterLink>
        <RouterLink to="/orders" class="btn btn--ghost">{{ t('confirmation.viewAll') }}</RouterLink>
      </div>
    </div>
  </section>
</template>

<style scoped>
.wrap {
  max-width: 640px;
  margin: 0 auto;
}

.state {
  padding: 60px 20px;
  text-align: center;
  color: var(--ink-faint);
}

.confirm {
  text-align: center;
  margin-bottom: 28px;
}

.check {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 54px;
  height: 54px;
  border-radius: 50%;
  background: var(--ok-bg);
  color: var(--ok);
  font-size: 26px;
  margin-bottom: 16px;
}

.confirm__id {
  font-size: 34px;
  margin-top: 8px;
}

.confirm__sub {
  color: var(--ink-soft);
  margin-top: 8px;
}

.receipt {
  padding: 22px 24px;
}

.receipt__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--line-strong);
}

.items {
  width: 100%;
  border-collapse: collapse;
}

.items td {
  padding: 14px 0;
  border-bottom: 1px solid var(--line);
  vertical-align: middle;
}

.items tr:last-child td {
  border-bottom: none;
}

.item__name {
  font-weight: 600;
}

.qty {
  text-align: right;
  color: var(--ink-soft);
  font-size: 13px;
  white-space: nowrap;
  padding-right: 18px;
}

.line {
  text-align: right;
  font-weight: 700;
  white-space: nowrap;
}

.receipt__total {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 2px solid var(--ink);
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 26px;
}

.actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
}

@media (max-width: 520px) {
  .actions {
    flex-direction: column;
  }
}
</style>

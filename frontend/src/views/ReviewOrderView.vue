<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useI18n } from 'vue-i18n'
import { AxiosError } from 'axios'
import { useCartStore } from '../stores/cart'
import { useProductStore } from '../stores/products'
import { createOrder } from '../api/orders'
import { money } from '../lib/format'
import type { ApiError, StockShortfall } from '../types'
import QtyStepper from '../components/QtyStepper.vue'

const cart = useCartStore()
const productStore = useProductStore()
const router = useRouter()
const { t } = useI18n()
const { lines, total, isEmpty } = storeToRefs(cart)

const submitting = ref(false)
const errorMessage = ref<string | null>(null)
const shortfalls = ref<StockShortfall[]>([])
const availabilityAdjusted = ref(false)

const shortfallRows = computed(() =>
  shortfalls.value.map((shortfall) => ({
    ...shortfall,
    name:
      cart.lineFor(shortfall.product_id)?.product.name ??
      t('review.productFallback', { id: shortfall.product_id }),
  })),
)

function extractShortfalls(error?: ApiError): StockShortfall[] {
  const items = error?.errors?.items
  if (!Array.isArray(items)) return []

  return items.filter(
    (item): item is StockShortfall =>
      typeof item === 'object' &&
      item !== null &&
      'product_id' in item &&
      'requested' in item &&
      'available' in item,
  )
}

async function submit() {
  submitting.value = true
  errorMessage.value = null
  shortfalls.value = []
  availabilityAdjusted.value = false
  try {
    const order = await createOrder(cart.payload())
    cart.clear()
    router.push({ name: 'order', params: { id: order.id } })
  } catch (err) {
    const axiosError = err as AxiosError<ApiError>
    const apiError = axiosError.response?.data
    shortfalls.value = extractShortfalls(apiError)
    errorMessage.value = apiError?.message ?? t('review.error')

    if (axiosError.response?.status === 409 && shortfalls.value.length > 0) {
      await productStore.load()
      availabilityAdjusted.value = cart.syncAvailability(productStore.items)
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <section class="wrap">
    <header class="head rise">
      <p class="eyebrow">{{ t('review.eyebrow') }}</p>
      <h1>{{ t('review.title') }}</h1>
    </header>

    <div v-if="errorMessage" class="banner banner--bad rise">
      <strong>{{ errorMessage }}</strong>
      <ul v-if="shortfallRows.length > 0" class="shortfalls">
        <li v-for="item in shortfallRows" :key="item.product_id">
          <span>{{ item.name }}</span>
          <span class="mono">
            {{
              item.available > 0
                ? t('review.availableShortfall', { requested: item.requested, available: item.available })
                : t('review.unavailableShortfall', { requested: item.requested })
            }}
          </span>
        </li>
      </ul>
      <p v-if="availabilityAdjusted" class="banner__note">{{ t('review.cartAdjusted') }}</p>
    </div>

    <div v-if="isEmpty" class="state rise">
      {{ t('review.empty') }}
      <RouterLink to="/" class="link">{{ t('review.browse') }}</RouterLink>
    </div>

    <div v-else class="grid rise" style="animation-delay: 0.05s">
      <div class="card lines">
        <div v-for="line in lines" :key="line.product.id" class="line">
          <div class="line__info">
            <span class="line__name">{{ line.product.name }}</span>
            <span class="line__sku mono">{{ line.product.sku }}</span>
            <span class="line__unit mono">{{ t('review.each', { price: money(line.product.price) }) }}</span>
          </div>
          <QtyStepper
            :model-value="line.quantity"
            :min="1"
            :max="line.product.stock_quantity"
            @update:model-value="(q: number) => cart.setQuantity(line.product, q)"
          />
          <span class="line__total mono">{{ money(line.product.price * line.quantity) }}</span>
          <button class="remove" title="Remove" @click="cart.remove(line.product.id)">×</button>
        </div>
      </div>

      <aside class="card summary">
        <h3 class="summary__title">{{ t('review.summary') }}</h3>
        <dl class="summary__rows">
          <div>
            <dt>{{ t('review.lines') }}</dt>
            <dd class="mono">{{ lines.length }}</dd>
          </div>
          <div>
            <dt>{{ t('review.units') }}</dt>
            <dd class="mono">{{ cart.count }}</dd>
          </div>
        </dl>
        <div class="summary__total">
          <span>{{ t('review.total') }}</span>
          <span class="mono">{{ money(total) }}</span>
        </div>

        <button class="btn btn--accent btn--block" :disabled="submitting" @click="submit">
          {{ submitting ? t('review.placing') : t('review.place') }}
        </button>
        <RouterLink to="/" class="btn btn--ghost btn--block">{{ t('review.keepShopping') }}</RouterLink>
      </aside>
    </div>
  </section>
</template>

<style scoped>
.wrap {
  max-width: 920px;
  margin: 0 auto;
}

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

.grid {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 20px;
  align-items: start;
}

.lines {
  padding: 6px 20px;
}

.line {
  display: grid;
  grid-template-columns: 1fr auto auto auto;
  align-items: center;
  gap: 18px;
  padding: 18px 0;
  border-bottom: 1px solid var(--line);
}

.line:last-child {
  border-bottom: none;
}

.line__info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.line__name {
  font-weight: 600;
}

.line__sku {
  font-size: 12px;
  color: var(--ink-faint);
}

.line__unit {
  font-size: 12px;
  color: var(--ink-soft);
}

.line__total {
  font-weight: 700;
  min-width: 78px;
  text-align: right;
}

.remove {
  width: 30px;
  height: 30px;
  border: 1px solid var(--line-strong);
  border-radius: var(--radius);
  background: transparent;
  color: var(--ink-faint);
  font-size: 18px;
  line-height: 1;
  cursor: pointer;
  transition: all 0.12s ease;
}

.remove:hover {
  border-color: var(--bad);
  color: var(--bad);
  background: var(--bad-bg);
}

.summary {
  padding: 22px;
  position: sticky;
  top: 92px;
}

.summary__title {
  font-size: 18px;
  margin-bottom: 16px;
}

.summary__rows {
  margin: 0 0 14px;
  padding: 0 0 14px;
  border-bottom: 1px dashed var(--line-strong);
}

.summary__rows > div {
  display: flex;
  justify-content: space-between;
  padding: 5px 0;
  color: var(--ink-soft);
  font-size: 14px;
}

.summary__rows dt,
.summary__rows dd {
  margin: 0;
}

.summary__total {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 24px;
  margin-bottom: 18px;
}

.btn--block {
  width: 100%;
  margin-top: 10px;
}

.banner {
  padding: 13px 16px;
  border-radius: var(--radius);
  font-size: 13px;
  margin-bottom: 18px;
}

.banner--bad {
  background: var(--bad-bg);
  color: var(--bad);
}

.banner strong {
  display: block;
  margin-bottom: 8px;
}

.shortfalls {
  list-style: none;
  margin: 0;
  padding: 0;
}

.shortfalls li {
  display: flex;
  justify-content: space-between;
  gap: 14px;
  padding: 5px 0;
  color: var(--ink);
}

.banner__note {
  margin: 8px 0 0;
  color: var(--ink-soft);
}

@media (max-width: 760px) {
  .grid {
    grid-template-columns: 1fr;
  }

  .summary {
    position: static;
  }

  .line {
    grid-template-columns: 1fr auto;
    grid-template-areas: 'info remove' 'stepper total';
    row-gap: 12px;
  }

  .line__info {
    grid-area: info;
    min-width: 0;
  }

  :deep(.stepper) {
    grid-area: stepper;
  }

  .remove {
    grid-area: remove;
    justify-self: end;
  }

  .line__total {
    grid-area: total;
    justify-self: end;
  }
}

@media (max-width: 520px) {
  .head {
    margin-bottom: 20px;
  }

  .head h1 {
    font-size: 32px;
  }

  .lines,
  .summary {
    padding: 18px;
  }

  .line {
    grid-template-columns: 1fr 32px;
    gap: 10px 12px;
  }

  .line__name,
  .line__sku,
  .line__unit {
    overflow-wrap: anywhere;
  }

  .shortfalls li {
    align-items: flex-start;
    flex-direction: column;
    gap: 2px;
  }

  .summary__total {
    font-size: 22px;
  }
}
</style>

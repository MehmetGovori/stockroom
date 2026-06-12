<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { storeToRefs } from 'pinia'
import { AxiosError } from 'axios'
import { useCartStore } from '../stores/cart'
import { createOrder } from '../api/orders'
import { money } from '../lib/format'
import type { ApiError } from '../types'
import QtyStepper from '../components/QtyStepper.vue'

const cart = useCartStore()
const router = useRouter()
const { lines, total, isEmpty } = storeToRefs(cart)

const submitting = ref(false)
const errorMessage = ref<string | null>(null)

async function submit() {
  submitting.value = true
  errorMessage.value = null
  try {
    const order = await createOrder(cart.payload())
    cart.clear()
    router.push({ name: 'order', params: { id: order.id } })
  } catch (err) {
    const axiosError = err as AxiosError<ApiError>
    errorMessage.value =
      axiosError.response?.data?.message ?? 'Could not place the order. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <section class="wrap">
    <header class="head rise">
      <p class="eyebrow">Checkout</p>
      <h1>Review order</h1>
    </header>

    <div v-if="isEmpty" class="state rise">
      Your order is empty.
      <RouterLink to="/" class="link">Browse the catalog →</RouterLink>
    </div>

    <div v-else class="grid rise" style="animation-delay: 0.05s">
      <div class="card lines">
        <div v-for="line in lines" :key="line.product.id" class="line">
          <div class="line__info">
            <span class="line__name">{{ line.product.name }}</span>
            <span class="line__sku mono">{{ line.product.sku }}</span>
            <span class="line__unit mono">{{ money(line.product.price) }} each</span>
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
        <h3 class="summary__title">Summary</h3>
        <dl class="summary__rows">
          <div>
            <dt>Lines</dt>
            <dd class="mono">{{ lines.length }}</dd>
          </div>
          <div>
            <dt>Units</dt>
            <dd class="mono">{{ cart.count }}</dd>
          </div>
        </dl>
        <div class="summary__total">
          <span>Total</span>
          <span class="mono">{{ money(total) }}</span>
        </div>

        <p v-if="errorMessage" class="banner banner--bad">{{ errorMessage }}</p>

        <button class="btn btn--accent btn--block" :disabled="submitting" @click="submit">
          {{ submitting ? 'Placing…' : 'Place order' }}
        </button>
        <RouterLink to="/" class="btn btn--ghost btn--block">Keep shopping</RouterLink>
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
  padding: 11px 14px;
  border-radius: var(--radius);
  font-size: 13px;
  margin-bottom: 6px;
}

.banner--bad {
  background: var(--bad-bg);
  color: var(--bad);
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
</style>

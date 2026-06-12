<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useI18n } from 'vue-i18n'
import { useProductStore } from '../stores/products'
import { useCartStore } from '../stores/cart'
import { money } from '../lib/format'
import StockTag from '../components/StockTag.vue'

const productStore = useProductStore()
const cart = useCartStore()
const { t } = useI18n()
const { items, loading, error } = storeToRefs(productStore)

const search = ref('')
const category = ref('')

onMounted(() => productStore.load())

const categories = computed(() => {
  const set = new Set(items.value.map((p) => p.category))
  return Array.from(set).sort()
})

const filtered = computed(() =>
  items.value.filter((p) => {
    const matchesSearch =
      !search.value ||
      p.name.toLowerCase().includes(search.value.toLowerCase()) ||
      p.sku.toLowerCase().includes(search.value.toLowerCase())
    const matchesCategory = !category.value || p.category === category.value
    return matchesSearch && matchesCategory
  }),
)

function inCart(productId: number): number {
  return cart.lineFor(productId)?.quantity ?? 0
}
</script>

<template>
  <section>
    <header class="head rise">
      <div>
        <p class="eyebrow">{{ t('products.eyebrow') }}</p>
        <h1 class="head__title">{{ t('products.title') }}</h1>
        <p class="head__sub">{{ t('products.subtitle') }}</p>
      </div>
      <RouterLink to="/products/new" class="btn btn--accent">{{ t('products.newProduct') }}</RouterLink>
    </header>

    <div class="filters rise" style="animation-delay: 0.05s">
      <input v-model="search" class="input" type="search" :placeholder="t('products.searchPlaceholder')" />
      <select v-model="category" class="select">
        <option value="">{{ t('products.allCategories') }}</option>
        <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
      </select>
    </div>

    <p v-if="error" class="banner banner--bad">{{ t('products.loadError') }}</p>

    <div v-if="loading" class="state">{{ t('products.loading') }}</div>

    <div v-else-if="filtered.length === 0" class="state">
      {{ t('products.empty') }} <RouterLink to="/products/new" class="link">{{ t('products.addFirst') }}</RouterLink>
    </div>

    <div v-else class="card table-wrap rise" style="animation-delay: 0.1s">
      <table class="ledger">
        <thead>
          <tr>
            <th>{{ t('products.colProduct') }}</th>
            <th>{{ t('products.colCategory') }}</th>
            <th class="num">{{ t('products.colPrice') }}</th>
            <th class="num">{{ t('products.colStock') }}</th>
            <th class="act"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in filtered" :key="p.id">
            <td :data-label="t('products.colProduct')" class="prod-cell">
              <div class="prod">
                <span class="prod__name">{{ p.name }}</span>
                <span class="prod__sku mono">{{ p.sku }}</span>
              </div>
            </td>
            <td :data-label="t('products.colCategory')"><span class="cat">{{ p.category }}</span></td>
            <td :data-label="t('products.colPrice')" class="num mono price">{{ money(p.price) }}</td>
            <td :data-label="t('products.colStock')" class="num"><StockTag :quantity="p.stock_quantity" /></td>
            <td class="act" data-label="">
              <div class="row-actions">
                <RouterLink :to="`/products/${p.id}/edit`" class="icon-link" :title="t('products.edit')">{{ t('products.edit') }}</RouterLink>
                <button
                  class="btn btn--sm"
                  :disabled="p.stock_quantity <= 0 || inCart(p.id) >= p.stock_quantity"
                  @click="cart.add(p, 1)"
                >
                  {{ inCart(p.id) > 0 ? t('products.inOrder', { count: inCart(p.id) }) : t('products.add') }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<style scoped>
.head {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  margin-bottom: 26px;
}

.head__title {
  font-size: 42px;
  margin-top: 6px;
}

.head__sub {
  color: var(--ink-soft);
  margin: 8px 0 0;
  max-width: 46ch;
}

.filters {
  display: grid;
  grid-template-columns: 1fr 220px;
  gap: 12px;
  margin-bottom: 22px;
}

.banner {
  padding: 12px 16px;
  border-radius: var(--radius);
  margin-bottom: 18px;
  font-size: 14px;
}

.banner--bad {
  background: var(--bad-bg);
  color: var(--bad);
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

.table-wrap {
  overflow: hidden;
}

.ledger {
  width: 100%;
  border-collapse: collapse;
}

.ledger thead th {
  text-align: left;
  font-family: var(--font-mono);
  font-size: 11px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--ink-faint);
  padding: 14px 18px;
  border-bottom: 1px solid var(--line-strong);
  background: var(--paper-sunk);
}

.ledger tbody td {
  padding: 15px 18px;
  border-bottom: 1px solid var(--line);
  vertical-align: middle;
}

.ledger tbody tr:last-child td {
  border-bottom: none;
}

.ledger tbody tr {
  transition: background 0.12s ease;
}

.ledger tbody tr:hover {
  background: var(--paper);
}

.num {
  text-align: right;
}

.act {
  text-align: right;
  width: 1%;
  white-space: nowrap;
}

.prod {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.prod__name {
  font-weight: 600;
}

.prod__sku {
  font-size: 12px;
  color: var(--ink-faint);
}

.cat {
  font-size: 13px;
  color: var(--ink-soft);
}

.price {
  font-weight: 600;
}

.row-actions {
  display: inline-flex;
  align-items: center;
  gap: 14px;
}

.icon-link {
  font-size: 13px;
  font-weight: 600;
  color: var(--ink-faint);
}

.icon-link:hover {
  color: var(--accent);
}

@media (max-width: 720px) {
  .head {
    align-items: stretch;
    flex-direction: column;
    gap: 14px;
    margin-bottom: 20px;
  }

  .head .btn {
    width: 100%;
  }

  .filters {
    grid-template-columns: 1fr;
    margin-bottom: 16px;
  }

  .head__title {
    font-size: 32px;
  }

  .ledger thead {
    display: none;
  }

  .table-wrap {
    overflow: visible;
    border: none;
    background: transparent;
    box-shadow: none;
  }

  .ledger,
  .ledger tbody {
    display: block;
    width: 100%;
  }

  .ledger tbody {
    display: grid;
    gap: 12px;
  }

  .ledger tbody tr {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 14px;
    padding: 16px;
    border: 1px solid var(--line);
    border-radius: var(--radius);
    background: var(--paper-raised);
    box-shadow: var(--shadow-card);
  }

  .ledger tbody td {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    width: 100%;
    padding: 0;
    border: 0;
  }

  .ledger tbody td::before {
    content: attr(data-label);
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--ink-faint);
  }

  .ledger tbody .prod-cell {
    grid-column: 1 / -1;
    display: block;
  }

  .ledger tbody .prod-cell::before,
  .ledger tbody .act::before {
    display: none;
  }

  .num,
  .act {
    text-align: left;
  }

  .act {
    grid-column: 1 / -1;
    margin-top: 4px;
  }

  .row-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    width: 100%;
  }

  .row-actions .btn,
  .icon-link {
    justify-content: center;
    width: 100%;
    min-height: 38px;
  }

  .icon-link {
    display: inline-flex;
    align-items: center;
    border: 1px solid var(--line-strong);
    border-radius: var(--radius);
    background: var(--paper);
  }
}
</style>

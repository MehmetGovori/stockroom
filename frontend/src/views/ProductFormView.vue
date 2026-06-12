<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { AxiosError } from 'axios'
import { createProduct, updateProduct } from '../api/products'
import { useProductStore } from '../stores/products'
import type { ApiError, ProductInput } from '../types'

const props = defineProps<{ id?: string }>()

const router = useRouter()
const productStore = useProductStore()
const isEdit = ref(Boolean(props.id))

const form = reactive<ProductInput>({
  name: '',
  sku: '',
  price: 0,
  stock_quantity: 0,
  category: '',
})

const errors = ref<Record<string, string[]>>({})
const errorMessage = ref<string | null>(null)
const saving = ref(false)

onMounted(async () => {
  if (!props.id) return
  if (productStore.items.length === 0) await productStore.load()
  const existing = productStore.byId(Number(props.id))
  if (existing) {
    form.name = existing.name
    form.sku = existing.sku
    form.price = existing.price
    form.stock_quantity = existing.stock_quantity
    form.category = existing.category
  }
})

async function save() {
  saving.value = true
  errors.value = {}
  errorMessage.value = null
  try {
    if (isEdit.value && props.id) {
      await updateProduct(Number(props.id), form)
    } else {
      await createProduct(form)
    }
    await productStore.load()
    router.push('/')
  } catch (err) {
    const axiosError = err as AxiosError<ApiError>
    errors.value = axiosError.response?.data?.errors ?? {}
    errorMessage.value = axiosError.response?.data?.message ?? 'Could not save the product.'
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <section class="wrap">
    <header class="head rise">
      <p class="eyebrow">{{ isEdit ? 'Edit' : 'Create' }}</p>
      <h1>{{ isEdit ? 'Edit product' : 'New product' }}</h1>
    </header>

    <form class="card form rise" style="animation-delay: 0.05s" @submit.prevent="save">
      <div class="field">
        <label>Name</label>
        <input v-model="form.name" class="input" type="text" placeholder="Brushed steel kettle" />
        <span v-if="errors.name" class="err">{{ errors.name[0] }}</span>
      </div>

      <div class="grid-2">
        <div class="field">
          <label>SKU</label>
          <input v-model="form.sku" class="input input--mono" type="text" placeholder="KTL-0042" />
          <span v-if="errors.sku" class="err">{{ errors.sku[0] }}</span>
        </div>
        <div class="field">
          <label>Category</label>
          <input v-model="form.category" class="input" type="text" placeholder="Kitchen" />
          <span v-if="errors.category" class="err">{{ errors.category[0] }}</span>
        </div>
      </div>

      <div class="grid-2">
        <div class="field">
          <label>Price (USD)</label>
          <input
            v-model.number="form.price"
            class="input input--mono"
            type="number"
            min="0"
            step="0.01"
          />
          <span v-if="errors.price" class="err">{{ errors.price[0] }}</span>
        </div>
        <div class="field">
          <label>Stock quantity</label>
          <input
            v-model.number="form.stock_quantity"
            class="input input--mono"
            type="number"
            min="0"
            step="1"
          />
          <span v-if="errors.stock_quantity" class="err">{{ errors.stock_quantity[0] }}</span>
        </div>
      </div>

      <p v-if="errorMessage" class="banner banner--bad">{{ errorMessage }}</p>

      <div class="actions">
        <button class="btn btn--accent" type="submit" :disabled="saving">
          {{ saving ? 'Saving…' : isEdit ? 'Save changes' : 'Create product' }}
        </button>
        <RouterLink to="/" class="btn btn--ghost">Cancel</RouterLink>
      </div>
    </form>
  </section>
</template>

<style scoped>
.wrap {
  max-width: 620px;
  margin: 0 auto;
}

.head {
  margin-bottom: 24px;
}

.head h1 {
  font-size: 34px;
  margin-top: 6px;
}

.form {
  padding: 26px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.err {
  color: var(--bad);
  font-size: 12px;
}

.banner {
  padding: 11px 14px;
  border-radius: var(--radius);
  font-size: 13px;
}

.banner--bad {
  background: var(--bad-bg);
  color: var(--bad);
}

.actions {
  display: flex;
  gap: 12px;
  margin-top: 4px;
}

@media (max-width: 520px) {
  .grid-2 {
    grid-template-columns: 1fr;
  }
}
</style>

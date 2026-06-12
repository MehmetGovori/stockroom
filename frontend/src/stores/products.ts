import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { PageMeta, Product, ProductQuery } from '../types'
import { fetchCategories, fetchProducts } from '../api/products'

const emptyMeta: PageMeta = { current_page: 1, last_page: 1, per_page: 0, total: 0 }

export const useProductStore = defineStore('products', () => {
  const items = ref<Product[]>([])
  const meta = ref<PageMeta>({ ...emptyMeta })
  const categories = ref<string[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function load(params: ProductQuery = {}) {
    loading.value = true
    error.value = null
    try {
      const page = await fetchProducts(params)
      items.value = page.items
      meta.value = page.meta
    } catch {
      error.value = 'Unable to load products.'
    } finally {
      loading.value = false
    }
  }

  async function loadCategories() {
    try {
      categories.value = await fetchCategories()
    } catch {
      categories.value = []
    }
  }

  return { items, meta, categories, loading, error, load, loadCategories }
})

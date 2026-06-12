import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Product } from '../types'
import { fetchProducts } from '../api/products'

export const useProductStore = defineStore('products', () => {
  const items = ref<Product[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function load(params?: { search?: string; category?: string }) {
    loading.value = true
    error.value = null
    try {
      items.value = await fetchProducts(params)
    } catch {
      error.value = 'Unable to load products.'
    } finally {
      loading.value = false
    }
  }

  function byId(id: number): Product | undefined {
    return items.value.find((p) => p.id === id)
  }

  return { items, loading, error, load, byId }
})

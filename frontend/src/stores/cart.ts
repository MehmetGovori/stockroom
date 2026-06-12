import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import type { Product } from '../types'

export interface CartLine {
  product: Product
  quantity: number
}

export const useCartStore = defineStore('cart', () => {
  const lines = ref<CartLine[]>([])

  const isEmpty = computed(() => lines.value.length === 0)

  const count = computed(() => lines.value.reduce((sum, line) => sum + line.quantity, 0))

  const total = computed(() =>
    lines.value.reduce((sum, line) => sum + line.product.price * line.quantity, 0),
  )

  function lineFor(productId: number): CartLine | undefined {
    return lines.value.find((line) => line.product.id === productId)
  }

  function add(product: Product, quantity = 1) {
    if (quantity < 1) return
    const existing = lineFor(product.id)
    const next = (existing?.quantity ?? 0) + quantity
    setQuantity(product, Math.min(next, product.stock_quantity))
  }

  function setQuantity(product: Product, quantity: number) {
    const existing = lineFor(product.id)
    if (quantity < 1) {
      remove(product.id)
      return
    }
    const capped = Math.min(quantity, product.stock_quantity)
    if (existing) {
      existing.quantity = capped
    } else {
      lines.value.push({ product, quantity: capped })
    }
  }

  function remove(productId: number) {
    lines.value = lines.value.filter((line) => line.product.id !== productId)
  }

  function clear() {
    lines.value = []
  }

  function syncAvailability(products: Product[]): boolean {
    const byId = new Map(products.map((product) => [product.id, product]))
    let adjusted = false

    lines.value = lines.value.flatMap((line) => {
      const product = byId.get(line.product.id) ?? line.product
      const quantity = Math.min(line.quantity, product.stock_quantity)

      if (quantity !== line.quantity) {
        adjusted = true
      }

      return quantity > 0 ? [{ product, quantity }] : []
    })

    return adjusted
  }

  function payload() {
    return lines.value.map((line) => ({ product_id: line.product.id, quantity: line.quantity }))
  }

  return { lines, isEmpty, count, total, lineFor, add, setQuantity, remove, clear, syncAvailability, payload }
})

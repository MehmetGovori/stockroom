import { http } from './http'
import type { Product, ProductInput } from '../types'

export async function fetchProducts(params?: { search?: string; category?: string }): Promise<Product[]> {
  const { data } = await http.get<{ data: Product[] }>('/products', { params })
  return data.data
}

export async function createProduct(payload: ProductInput): Promise<Product> {
  const { data } = await http.post<{ data: Product }>('/products', payload)
  return data.data
}

export async function updateProduct(id: number, payload: ProductInput): Promise<Product> {
  const { data } = await http.put<{ data: Product }>(`/products/${id}`, payload)
  return data.data
}

export async function deleteProduct(id: number): Promise<void> {
  await http.delete(`/products/${id}`)
}

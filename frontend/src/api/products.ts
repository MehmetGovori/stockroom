import { http } from './http'
import type { PageMeta, Product, ProductInput, ProductPage, ProductQuery } from '../types'

export async function fetchProducts(params: ProductQuery = {}): Promise<ProductPage> {
  const { data } = await http.get<{ data: Product[]; meta: PageMeta }>('/products', { params })
  return { items: data.data, meta: data.meta }
}

export async function fetchProduct(id: number): Promise<Product> {
  const { data } = await http.get<{ data: Product }>(`/products/${id}`)
  return data.data
}

export async function fetchCategories(): Promise<string[]> {
  const { data } = await http.get<{ data: string[] }>('/products/categories')
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

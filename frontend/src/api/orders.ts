import { http } from './http'
import type { Order, OrderLineInput } from '../types'

export async function fetchOrders(): Promise<Order[]> {
  const { data } = await http.get<{ data: Order[] }>('/orders')
  return data.data
}

export async function fetchOrder(id: number): Promise<Order> {
  const { data } = await http.get<{ data: Order }>(`/orders/${id}`)
  return data.data
}

export async function createOrder(items: OrderLineInput[]): Promise<Order> {
  const { data } = await http.post<{ data: Order }>('/orders', { items })
  return data.data
}

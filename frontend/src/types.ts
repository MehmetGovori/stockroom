export interface Product {
  id: number
  name: string
  sku: string
  price: number
  stock_quantity: number
  category: string
  created_at: string
  updated_at: string
}

export interface OrderItem {
  id: number
  product_id: number
  product_name: string
  quantity: number
  unit_price: number
  line_total: number
}

export interface Order {
  id: number
  status: string
  total: number
  items: OrderItem[]
  created_at: string
}

export interface OrderLineInput {
  product_id: number
  quantity: number
}

export interface ProductInput {
  name: string
  sku: string
  price: number
  stock_quantity: number
  category: string
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

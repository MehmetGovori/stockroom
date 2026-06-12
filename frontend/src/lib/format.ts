const currency = new Intl.NumberFormat('en-US', {
  style: 'currency',
  currency: 'USD',
})

export function money(value: number): string {
  return currency.format(value)
}

export function stockLevel(quantity: number): 'ok' | 'warn' | 'bad' {
  if (quantity <= 0) return 'bad'
  if (quantity <= 5) return 'warn'
  return 'ok'
}

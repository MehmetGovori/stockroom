import axios from 'axios'

const baseURL = (import.meta.env.VITE_API_URL as string | undefined) ?? 'http://localhost:8000/api'

export const TOKEN_KEY = 'stockroom.token'

let onUnauthorized: (() => void) | null = null

export function setUnauthorizedHandler(handler: () => void) {
  onUnauthorized = handler
}

export const http = axios.create({
  baseURL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

http.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY)
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

http.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status
    const url = error.config?.url ?? ''
    if (status === 401 && !url.includes('/login')) {
      onUnauthorized?.()
    }
    return Promise.reject(error)
  },
)

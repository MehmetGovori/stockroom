import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import type { User } from '../types'
import { fetchCurrentUser, login as loginRequest, logout as logoutRequest } from '../api/auth'
import { TOKEN_KEY } from '../api/http'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem(TOKEN_KEY))
  const user = ref<User | null>(null)

  const isAuthenticated = computed(() => Boolean(token.value))

  function setToken(value: string) {
    token.value = value
    localStorage.setItem(TOKEN_KEY, value)
  }

  function clearSession() {
    token.value = null
    user.value = null
    localStorage.removeItem(TOKEN_KEY)
  }

  async function login(email: string, password: string) {
    const result = await loginRequest(email, password)
    setToken(result.token)
    user.value = result.user
  }

  async function loadUser() {
    if (!token.value) return
    try {
      user.value = await fetchCurrentUser()
    } catch {
      clearSession()
    }
  }

  async function logout() {
    try {
      await logoutRequest()
    } finally {
      clearSession()
    }
  }

  return { token, user, isAuthenticated, login, loadUser, logout, clearSession }
})

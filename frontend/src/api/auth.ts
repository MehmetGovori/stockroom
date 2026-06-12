import { http } from './http'
import type { User } from '../types'

export async function login(email: string, password: string): Promise<{ token: string; user: User }> {
  const { data } = await http.post<{ data: { token: string; user: User } }>('/login', { email, password })
  return data.data
}

export async function fetchCurrentUser(): Promise<User> {
  const { data } = await http.get<{ data: User }>('/user')
  return data.data
}

export async function logout(): Promise<void> {
  await http.post('/logout')
}

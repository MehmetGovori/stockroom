import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import router from './router'
import { i18n } from './i18n'
import { setUnauthorizedHandler } from './api/http'
import { useAuthStore } from './stores/auth'

const app = createApp(App)
app.use(createPinia()).use(router).use(i18n)

const auth = useAuthStore()

setUnauthorizedHandler(() => {
  auth.clearSession()
  if (router.currentRoute.value.name !== 'login') {
    router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } })
  }
})

app.mount('#app')
auth.loadUser()

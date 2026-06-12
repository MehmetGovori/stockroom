<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { AxiosError } from 'axios'
import { useAuthStore } from '../stores/auth'
import LanguageSwitcher from '../components/LanguageSwitcher.vue'
import type { ApiError } from '../types'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const { t } = useI18n()

const email = ref('admin@stockroom.test')
const password = ref('password')
const submitting = ref(false)
const error = ref<string | null>(null)

async function submit() {
  submitting.value = true
  error.value = null
  try {
    await auth.login(email.value, password.value)
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/'
    router.push(redirect)
  } catch (err) {
    const axiosError = err as AxiosError<ApiError>
    const emailErrors = axiosError.response?.data?.errors?.email as string[] | undefined
    error.value = emailErrors?.[0] ?? axiosError.response?.data?.message ?? t('auth.invalid')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="login">
    <div class="login__switcher">
      <LanguageSwitcher />
    </div>

    <div class="login__card card rise">
      <div class="brand">
        <span class="brand__mark" aria-hidden="true"></span>
        <span class="brand__name">Stockroom</span>
      </div>

      <p class="eyebrow">{{ t('auth.eyebrow') }}</p>
      <h1 class="login__title">{{ t('auth.title') }}</h1>
      <p class="login__sub">{{ t('auth.subtitle') }}</p>

      <form class="login__form" @submit.prevent="submit">
        <div class="field">
          <label for="email">{{ t('auth.email') }}</label>
          <input id="email" v-model="email" class="input" type="email" autocomplete="username" required />
        </div>

        <div class="field">
          <label for="password">{{ t('auth.password') }}</label>
          <input
            id="password"
            v-model="password"
            class="input"
            type="password"
            autocomplete="current-password"
            required
          />
        </div>

        <p v-if="error" class="banner banner--bad">{{ error }}</p>

        <button class="btn btn--accent btn--block" type="submit" :disabled="submitting">
          {{ submitting ? t('auth.signingIn') : t('auth.signIn') }}
        </button>
      </form>

      <p class="login__hint">
        {{ t('auth.demoHint') }}
        <span class="mono">admin@stockroom.test / password</span>
      </p>
    </div>
  </div>
</template>

<style scoped>
.login {
  position: relative;
  min-height: calc(100vh - 0px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
}

.login__switcher {
  position: absolute;
  top: 24px;
  right: 24px;
}

.login__card {
  width: 100%;
  max-width: 408px;
  padding: 36px 34px 30px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 26px;
}

.brand__mark {
  width: 22px;
  height: 22px;
  border-radius: 7px;
  background: var(--accent);
  box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.35);
}

.brand__name {
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 21px;
  letter-spacing: -0.035em;
}

.login__title {
  font-size: 30px;
  margin-top: 8px;
}

.login__sub {
  color: var(--ink-soft);
  margin: 8px 0 24px;
}

.login__form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.banner {
  padding: 11px 14px;
  border-radius: var(--radius-sm);
  font-size: 13px;
  margin: 0;
}

.banner--bad {
  background: var(--bad-bg);
  color: var(--bad);
}

.btn--block {
  width: 100%;
  margin-top: 4px;
}

.login__hint {
  margin: 22px 0 0;
  padding-top: 18px;
  border-top: 1px solid var(--line);
  font-size: 12.5px;
  color: var(--ink-faint);
  text-align: center;
}

.login__hint .mono {
  display: block;
  margin-top: 4px;
  color: var(--ink-soft);
  font-size: 12px;
}
</style>

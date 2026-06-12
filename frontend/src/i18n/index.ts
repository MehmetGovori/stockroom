import { createI18n } from 'vue-i18n'
import en from './locales/en'
import sq from './locales/sq'

export const SUPPORTED_LOCALES = [
  { code: 'en', label: 'EN' },
  { code: 'sq', label: 'AL' },
] as const

export type LocaleCode = (typeof SUPPORTED_LOCALES)[number]['code']

const STORAGE_KEY = 'stockroom.locale'

function resolveInitialLocale(): LocaleCode {
  const stored = localStorage.getItem(STORAGE_KEY)
  if (stored && SUPPORTED_LOCALES.some((locale) => locale.code === stored)) {
    return stored as LocaleCode
  }
  return 'en'
}

const initialLocale = resolveInitialLocale()

export const i18n = createI18n({
  legacy: false,
  locale: initialLocale,
  fallbackLocale: 'en',
  messages: { en, sq },
})

document.documentElement.lang = initialLocale

export function setLocale(code: LocaleCode) {
  i18n.global.locale.value = code
  localStorage.setItem(STORAGE_KEY, code)
  document.documentElement.lang = code
}

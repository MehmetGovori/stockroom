<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { SUPPORTED_LOCALES, setLocale, type LocaleCode } from '../i18n'

const { locale, t } = useI18n()

function select(code: LocaleCode) {
  setLocale(code)
}
</script>

<template>
  <div class="lang" role="group" :aria-label="t('language.label')">
    <button
      v-for="option in SUPPORTED_LOCALES"
      :key="option.code"
      type="button"
      class="lang__btn mono"
      :class="{ 'lang__btn--active': locale === option.code }"
      :aria-pressed="locale === option.code"
      @click="select(option.code)"
    >
      {{ option.label }}
    </button>
  </div>
</template>

<style scoped>
.lang {
  display: inline-flex;
  align-items: center;
  border: 1px solid var(--line-strong);
  border-radius: 100px;
  background: var(--paper-raised);
  padding: 3px;
  gap: 2px;
}

.lang__btn {
  border: none;
  background: transparent;
  color: var(--ink-faint);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.06em;
  padding: 5px 11px;
  border-radius: 100px;
  cursor: pointer;
  transition: background 0.15s ease, color 0.15s ease;
}

.lang__btn:hover {
  color: var(--ink);
}

.lang__btn--active {
  background: var(--ink);
  color: var(--paper);
}
</style>

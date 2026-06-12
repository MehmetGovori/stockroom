<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useCartStore } from './stores/cart'
import { money } from './lib/format'
import LanguageSwitcher from './components/LanguageSwitcher.vue'
import { useI18n } from 'vue-i18n'

const cart = useCartStore()
const { count, total } = storeToRefs(cart)
const { t } = useI18n()
</script>

<template>
  <div class="app">
    <header class="topbar">
      <div class="shell topbar__inner">
        <RouterLink to="/" class="brand">
          <span class="brand__mark" aria-hidden="true"></span>
          <span class="brand__name">Stockroom</span>
          <span class="brand__tag mono">INV/ORD</span>
        </RouterLink>

        <nav class="nav">
          <RouterLink to="/" class="nav__link">{{ t('nav.catalog') }}</RouterLink>
          <RouterLink to="/orders" class="nav__link">{{ t('nav.orders') }}</RouterLink>
        </nav>

        <LanguageSwitcher />

        <RouterLink to="/order" class="order-pill" :class="{ 'order-pill--live': count > 0 }">
          <span class="order-pill__label">{{ t('nav.order') }}</span>
          <span class="order-pill__count mono">{{ count }}</span>
          <span class="order-pill__total mono">{{ money(total) }}</span>
        </RouterLink>
      </div>
    </header>

    <main class="shell main">
      <RouterView v-slot="{ Component }">
        <component :is="Component" />
      </RouterView>
    </main>

    <footer class="shell foot">
      <span class="mono">{{ t('nav.footer') }}</span>
    </footer>
  </div>
</template>

<style scoped>
.app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 20;
  background: rgba(245, 241, 232, 0.86);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--line-strong);
}

.topbar__inner {
  height: 68px;
  display: flex;
  align-items: center;
  gap: 28px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand__mark {
  width: 18px;
  height: 18px;
  border-radius: 4px;
  background: var(--accent);
  box-shadow: 3px 3px 0 var(--ink);
}

.brand__name {
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 20px;
  letter-spacing: -0.03em;
}

.brand__tag {
  font-size: 10px;
  letter-spacing: 0.16em;
  color: var(--ink-faint);
  border: 1px solid var(--line-strong);
  border-radius: 100px;
  padding: 2px 8px;
}

.nav {
  display: flex;
  gap: 6px;
  margin-left: auto;
}

.nav__link {
  padding: 8px 14px;
  border-radius: var(--radius);
  font-weight: 600;
  font-size: 14px;
  color: var(--ink-soft);
  transition: background 0.15s ease, color 0.15s ease;
}

.nav__link:hover {
  background: var(--paper-sunk);
  color: var(--ink);
}

.nav__link.router-link-exact-active {
  background: var(--ink);
  color: var(--paper);
}

.order-pill {
  display: flex;
  align-items: center;
  gap: 10px;
  height: 42px;
  padding: 0 8px 0 16px;
  border: 1px solid var(--line-strong);
  border-radius: 100px;
  background: var(--paper-raised);
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.order-pill--live {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(220, 75, 46, 0.12);
}

.order-pill__label {
  font-size: 13px;
  font-weight: 600;
  color: var(--ink-soft);
}

.order-pill__count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 26px;
  height: 26px;
  padding: 0 7px;
  border-radius: 100px;
  background: var(--ink);
  color: var(--paper);
  font-size: 13px;
  font-weight: 700;
}

.order-pill--live .order-pill__count {
  background: var(--accent);
}

.order-pill__total {
  font-size: 14px;
  font-weight: 700;
}

.main {
  flex: 1;
  width: 100%;
  padding-top: 40px;
  padding-bottom: 72px;
}

.foot {
  padding: 26px 28px 40px;
  border-top: 1px solid var(--line);
  color: var(--ink-faint);
  font-size: 12px;
}

@media (max-width: 640px) {
  .brand__tag {
    display: none;
  }
  .order-pill__label {
    display: none;
  }
}
</style>

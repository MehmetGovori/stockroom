<script setup lang="ts">
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useCartStore } from './stores/cart'
import { useAuthStore } from './stores/auth'
import { money } from './lib/format'
import LanguageSwitcher from './components/LanguageSwitcher.vue'
import { useI18n } from 'vue-i18n'

const cart = useCartStore()
const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const { count, total } = storeToRefs(cart)
const { user } = storeToRefs(auth)
const { t } = useI18n()

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="app">
    <header v-if="route.name !== 'login'" class="topbar">
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

        <div class="account">
          <span class="account__email mono" :title="user?.email">{{ user?.email }}</span>
          <button class="btn btn--ghost btn--sm" @click="handleLogout">{{ t('auth.logout') }}</button>
        </div>
      </div>
    </header>

    <main class="shell main" :class="{ 'main--bare': route.name === 'login' }">
      <RouterView v-slot="{ Component }">
        <component :is="Component" />
      </RouterView>
    </main>

    <footer v-if="route.name !== 'login'" class="shell foot">
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
  background: rgba(245, 246, 248, 0.82);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--line);
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
  width: 20px;
  height: 20px;
  border-radius: 6px;
  background: var(--accent);
  box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.35);
}

.brand__name {
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 20px;
  letter-spacing: -0.035em;
}

.brand__tag {
  font-size: 10px;
  letter-spacing: 0.14em;
  color: var(--ink-faint);
  border: 1px solid var(--line-strong);
  border-radius: 100px;
  padding: 2px 9px;
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
  box-shadow: 0 0 0 4px rgba(12, 155, 98, 0.13);
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

.account {
  display: flex;
  align-items: center;
  gap: 10px;
}

.account__email {
  font-size: 12px;
  color: var(--ink-faint);
  max-width: 170px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.main {
  flex: 1;
  width: 100%;
  padding-top: 40px;
  padding-bottom: 72px;
}

.main--bare {
  padding: 0;
}

.foot {
  padding: 26px 28px 40px;
  border-top: 1px solid var(--line);
  color: var(--ink-faint);
  font-size: 12px;
}

@media (max-width: 1000px) {
  .brand__tag {
    display: none;
  }

  .account__email {
    display: none;
  }
}

@media (max-width: 640px) {
  .topbar__inner {
    height: auto;
    min-height: 0;
    flex-wrap: wrap;
    gap: 10px 12px;
    padding-top: 10px;
    padding-bottom: 10px;
  }

  .brand {
    order: 1;
    flex: 1 1 160px;
    min-width: 0;
  }

  .brand__name {
    font-size: 19px;
  }

  .brand__tag {
    display: none;
  }

  .nav {
    order: 3;
    flex: 1 1 auto;
    margin-left: 0;
  }

  .nav__link {
    flex: 1;
    padding: 8px 10px;
    text-align: center;
  }

  :deep(.lang) {
    order: 4;
    flex: 0 0 auto;
  }

  .order-pill {
    order: 2;
    height: 38px;
    gap: 7px;
    margin-left: auto;
    padding: 0 8px 0 10px;
  }

  .order-pill__label {
    display: none;
  }

  .order-pill__total {
    display: none;
  }

  .account__email {
    display: none;
  }

  .main {
    padding-top: 26px;
    padding-bottom: 48px;
  }

  .foot {
    padding: 20px 16px 28px;
  }
}
</style>

import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { public: true },
    },
    {
      path: '/',
      name: 'products',
      component: () => import('../views/ProductsView.vue'),
      meta: { public: true },
    },
    {
      path: '/products/new',
      name: 'product-create',
      component: () => import('../views/ProductFormView.vue'),
    },
    {
      path: '/products/:id/edit',
      name: 'product-edit',
      component: () => import('../views/ProductFormView.vue'),
      props: true,
    },
    {
      path: '/order',
      name: 'order-review',
      component: () => import('../views/ReviewOrderView.vue'),
    },
    {
      path: '/orders',
      name: 'orders',
      component: () => import('../views/OrdersView.vue'),
    },
    {
      path: '/orders/:id',
      name: 'order',
      component: () => import('../views/OrderView.vue'),
      props: true,
    },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (!to.meta.public && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.name === 'login' && auth.isAuthenticated) {
    return { name: 'products' }
  }

  return true
})

export default router

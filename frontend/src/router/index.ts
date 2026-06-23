import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'team-members',
      component: () => import('@/views/TeamMembersPage.vue'),
    },
    {
      path: '/leave-requests',
      name: 'leave-requests',
      component: () => import('@/views/LeaveRequestsPage.vue'),
    },
    {
      path: '/calendar',
      name: 'calendar',
      component: () => import('@/views/CalendarPage.vue'),
    },
    {
      path: '/on-call',
      name: 'on-call',
      component: () => import('@/views/OnCallPage.vue'),
    },
  ],
})

export default router

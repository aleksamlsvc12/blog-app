import { createRouter, createWebHistory } from 'vue-router';

import LandingPageView from '../views/LandingPageView.vue';
import RegisterPageView from '../views/RegisterPageView.vue';
import LoginPageView from '../views/LoginPageView.vue';

const routes = [
  {path: '/', name: 'Landing Page', component: LandingPageView},
  {path: '/login', name: 'Login', component: LoginPageView},
  {path: '/register', name: 'Register', component: RegisterPageView}
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router;
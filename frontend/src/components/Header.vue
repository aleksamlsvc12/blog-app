<script setup>
import { useAuthStore } from "../stores/auth";
import { useRoute } from "vue-router";
import { computed } from "vue";

const route = useRoute();
const auth = useAuthStore();

const isAdmin = computed(() => {
  return auth.user && Number(auth.user.fk_user_type) === 1;
});
</script>

<template>
  <!-- Main navigation bar -->
  <div
    class="lg:h-[10%] p-4 gap-4 h-auto flex flex-col lg:flex-row justify-around items-center font-mono bg-[#111827] text-gray-200"
  >
    <!-- Logo / Home link -->
    <div class="text-2xl cursor-pointer font-bold">
      <RouterLink to="/">Typo</RouterLink>
    </div>

    <!-- Center navigation links -->
    <div
      class="text-sm flex items-center flex-col lg:flex-row justify-center lg:gap-10 gap-2 min-w-[200px]"
    >
      <!-- When user is logged in -->
      <template v-if="auth.isLoggedIn">
        <RouterLink to="/categories">
          <span class="cursor-pointer">Categories</span>
        </RouterLink>

        <RouterLink to="/post">
          <span class="cursor-pointer">Create Post</span>
        </RouterLink>

        <RouterLink to="/admin" v-if="auth.isLoggedIn && isAdmin">
          <span class="cursor-pointer"> Users Management </span>
        </RouterLink>

        <RouterLink to="/manage-category" v-if="auth.isLoggedIn && isAdmin">
          <span class="cursor-pointer"> Category Management </span>
        </RouterLink>
      </template>

      <!-- Placeholder for layout balance -->
      <template v-else>
        <span class="opacity-0 select-none lg:block hidden">placeholder</span>
      </template>
    </div>

    <!-- Right side buttons -->
    <div class="flex gap-2 lg:flex-row flex-col">
      <RouterLink
        to="/profile"
        v-if="
          auth.isLoggedIn &&
          auth.user &&
          (route.path !== '/login' ||
            route.path !== '/register' ||
            route.path !== '/') &&
          route.path !== '/profile'
        "
      >
        <button
          class="border p-1 px-3 cursor-pointer rounded-md active:scale-95"
        >
          <span v-if="auth.user.fk_user_type === 3">
            {{ auth.user.name }} {{ auth.user.surname }}
          </span>
          <span v-else-if="auth.user.fk_user_type === 2"> Modifier </span>
          <span v-else> Admin </span>
        </button>
      </RouterLink>

      <RouterLink to="/" v-if="auth.isLoggedIn && route.path === '/profile'">
        <button
          @click="auth.logout()"
          class="border p-1 px-3 cursor-pointer rounded-md active:scale-95"
        >
          Logout
        </button>
      </RouterLink>

      <RouterLink to="/login" v-if="!auth.isLoggedIn">
        <button
          class="border p-1 px-3 cursor-pointer rounded-md active:scale-95"
        >
          Login
        </button>
      </RouterLink>
    </div>
  </div>
</template>

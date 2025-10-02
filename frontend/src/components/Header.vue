<script setup>
import { useAuthStore } from "../stores/auth";
import { useRoute } from "vue-router";

const route = useRoute();
const auth = useAuthStore();
</script>

<template>
  <div
    class="lg:h-[10%] p-4 gap-4 h-auto flex flex-col lg:flex-row justify-around items-center font-mono"
  >
    <div class="text-2xl cursor-pointer">
      <RouterLink v-if="!auth.isLoggedIn" to="/">Typo</RouterLink>
      <span v-else>Typo</span>
    </div>

    <div
      class="text-sm flex items-center flex-col lg:flex-row justify-center lg:gap-10 gap-2 min-w-[200px]"
    >
      <template v-if="auth.isLoggedIn">
        <RouterLink to="/categories">
          <span class="cursor-pointer">Categories</span>
        </RouterLink>
        <span class="cursor-pointer">Create Post</span>
      </template>
      <template v-else>
        <span class="opacity-0 select-none lg:block hidden">placeholder</span>
      </template>
    </div>

    <div class="flex gap-2 lg:flex-row flex-col">
      <RouterLink to="/profile" v-if="auth.isLoggedIn && route.path === '/categories'">
        <button class="border p-1 px-3 cursor-pointer">Profile</button>
      </RouterLink>

      <RouterLink to="/" v-if="auth.isLoggedIn && route.path === '/profile'">
        <button @click="auth.logout()" class="border p-1 px-3 cursor-pointer">
          Logout
        </button>
      </RouterLink>

      <RouterLink to="/login" v-if="!auth.isLoggedIn">
        <button class="border p-1 px-3 cursor-pointer">Login</button>
      </RouterLink>

      <button
        class="border p-2 px-2 cursor-pointer flex justify-center items-center"
      >
        <i class="pi pi-moon"></i>
      </button>
    </div>
  </div>
</template>

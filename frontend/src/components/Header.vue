<script setup>
import { useAuthStore } from "../stores/auth";
const auth = useAuthStore();
</script>

<template>
  <div
    class="h-[10%] flex justify-between items-center pl-[10%] pr-[10%] font-mono"
  >
    <RouterLink to="/" class="text-2xl cursor-pointer">Typo</RouterLink>

    <div v-if="auth.isLoggedIn" class="text-sm">
      <RouterLink to="/categories">
        <span class="mr-10 cursor-pointer">Categories</span>
      </RouterLink>
      <span class="cursor-pointer">Create Post</span>
    </div>

    <div class="flex gap-2">
      <RouterLink to="/profile">
        <button
          v-if="auth.isLoggedIn"
          class="border-1 p-1 pl-3 pr-3 cursor-pointer"
        >
          Username
        </button>
      </RouterLink>

      <RouterLink v-if="!auth.isLoggedIn" to="/login">
        <button class="border-1 p-1 pl-3 pr-3 cursor-pointer">Login</button>
      </RouterLink>

      <button
        class="border-1 p-1 pl-2 pr-2 cursor-pointer flex justify-center items-center"
      >
        <i class="pi pi-moon"></i>
      </button>
    </div>
  </div>
</template>

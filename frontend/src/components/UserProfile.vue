<script setup>
import { useAuthStore } from "../stores/auth";
const auth = useAuthStore();
</script>

<template>
  <RouterLink to="/">
    <button @click="auth.logout()" class="border-1 cursor-pointer">Logout</button>
  </RouterLink>
</template>

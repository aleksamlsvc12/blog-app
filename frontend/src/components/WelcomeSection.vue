<script setup>
import { useAuthStore } from "../stores/auth";

const auth = useAuthStore();
</script>

<template>
  <div
    class="w-full lg:w-1/2 h-full font-mono flex flex-col justify-center items-center text-gray-100"
  >
    <div
      class="w-[90%] lg:w-[70%] flex flex-col gap-5 lg:items-start items-center"
    >
      <p class="text-5xl font-bold text-center lg:text-left text-white">
        Welcome to Typo!
      </p>
      <p class="text-center lg:text-left">
        Your personal blogging platform to share ideas, stories, and knowledge.
        <span class="block mt-2">Write. Read. Connect with the community.</span>
      </p>

      <RouterLink to="/register" v-if="!auth.isLoggedIn">
        <button
          class="font-bold flex items-center bg-emerald-800 text-gray-100 py-2 px-4 rounded-md cursor-pointer mt-5 transition-all duration-300 hover:bg-emerald-700 hover:shadow-[0_0_18px_rgba(16,185,129,0.7)] active:scale-95"
        >
          Get Started!
          <i class="pi pi-unlock ml-2"></i>
        </button>
      </RouterLink>
    </div>
  </div>
</template>

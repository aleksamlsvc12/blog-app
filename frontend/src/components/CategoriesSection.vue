<script setup>
defineProps({
  categories: {
    type: Array,
    required: true,
  },
});

import Category from "./Category.vue";
</script>

<template>
  <!-- Main container with scrollable area and custom font -->
  <div class="p-10 h-full overflow-y-auto font-mono">
    <!-- Page heading -->
    <p class="mb-10 text-2xl font-bold text-white p-4 text-center lg:text-left pb-2 border-b border-b-gray-600">
      Discover What Interests You
    </p>

    <!-- Responsive grid displaying all categories -->
    <div
      class="grid lg:grid-cols-4 md:grid-cols-3 sm:grid-cols-2 gap-y-10 justify-items-center"
    >
      <Category
        v-for="category in categories"
        :key="category.id"
        :category="category"
      />
    </div>
  </div>
</template>

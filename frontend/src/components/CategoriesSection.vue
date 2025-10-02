<script setup>
defineProps({
  categories:{
    type: Array,
    required: true
  }
});


import Category from "./Category.vue";
</script>

<template>
  <div class="p-10 h-full overflow-y-auto font-mono">
    <p class="mt-5 mb-10 text-2xl font-bold border p-4 text-center lg:text-left">Discover What Interests You</p>
    <div class="grid lg:grid-cols-4 md:grid-cols-3 sm:grid-cols-2 gap-y-10 justify-items-center ">
      <Category 
        v-for="category in categories"
        :key="category.id"
        :category="category"
      />
    </div>
  </div>
</template>

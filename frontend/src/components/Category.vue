<script setup>
import { useRouter } from "vue-router";

const props = defineProps({
  category: {
    type: Object,
    required: true,
  },
});

const router = useRouter();

function goToCategory() {
  router.push(`/category/${props.category.name.toLowerCase()}`);
}
</script>

<template>
  <!-- Category card container -->
  <div
    @click="goToCategory"
    class="w-[230px] h-[220px] p-5 flex flex-col justify-between cursor-pointer gap-4 bg-gray-700 rounded-lg transition-all duration-300 hover:scale-[0.95] hover:shadow-[0_0_15px_rgba(243,244,246,0.5)]"
  >
    <!-- Category title section -->
    <div class="bg-gray-900 rounded-md p-2">
      <p class="text-sm font-bold text-center text-gray-100">
        {{ category.name }}
      </p>
    </div>

    <!-- Category description section -->
    <div
      class="bg-gray-900 rounded-md h-[80%] flex justify-center items-center p-4"
    >
      <p class="text-center text-xs text-gray-100">
        {{ category.description }}
      </p>
    </div>
  </div>
</template>

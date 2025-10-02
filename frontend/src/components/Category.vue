<script setup>
defineProps({
  category: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <div
    class="border-1 w-[230px] h-[220px] p-5 flex flex-col justify-between cursor-pointer hover:scale-[0.95] transition gap-4 bg-white"
  >
    <div class="bg-black text-white p-2">
      <p class="text-sm font-bold text-center">{{ category.name }}</p>
    </div>

    <div class="border h-[80%] flex justify-center items-center p-1">
      <p class="text-center text-xs">
        {{ category.description }}
      </p>
    </div>
  </div>
</template>

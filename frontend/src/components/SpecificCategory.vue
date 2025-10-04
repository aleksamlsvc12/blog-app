<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";

const route = useRoute();

const posts = ref([]);
const loading = ref(true);
const hasNoContent = ref(false);

onMounted(async () => {
  const categoryName = route.params.name?.trim();

  if (!categoryName) {
    hasNoContent.value = true;
    loading.value = false;
    return;
  }

  try {
    const res = await axios.get("http://localhost:8000/api/category.php", {
      params: { category: categoryName },
    });

    const data = res.data;

    if (data.status === "success") {
      posts.value = data.posts;
      hasNoContent.value = posts.value.length === 0;
    } else {
      hasNoContent.value = true;
    }
  } catch (err) {
    hasNoContent.value = true;
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="p-10 font-mono h-full">
    <div v-if="loading" class="text-gray-500">Loading posts...</div>

    <div
      v-else-if="hasNoContent"
      class="flex flex-col gap-4 justify-center items-center h-full w-full"
    >
      <h1 class="text-2xl font-bold mb-2">
        Oops! Looks like there are no blogs on this topic.
      </h1>

      <RouterLink to="/post">
        <button class="bg-black text-white p-3 cursor-pointer w-full">Create one now!</button>
      </RouterLink>
    </div>

    <div v-else>
      <h1 class="text-2xl font-bold mb-4">
        {{ route.params.name }}:
      </h1>

      <div
        v-for="post in posts"
        :key="post.id"
        class="border p-4 mb-4 bg-white"
      >
        <h2 class="text-lg font-bold">{{ post.title }}</h2>
        <p class="text-sm text-gray-700">{{ post.content }}</p>
      </div>
    </div>
  </div>
</template>

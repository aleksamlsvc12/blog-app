<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

const route = useRoute();
const router = useRouter();

const posts = ref([]);
const loading = ref(true);

onMounted(async () => {
  const categoryName = route.params.name?.trim();

  if (!categoryName) {
    router.push("/no-content");
    return;
  }

  try {
    const res = await axios.get("http://localhost:8000/api/category.php", {
      params: { category: categoryName },
    });

    const data = res.data;

    if (data.status === "success") {
      posts.value = data.posts;
    } else {
      router.push("/no-content");
    }
  } catch (err) {
    router.push("/no-content");
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="p-10 font-mono">
    <div v-if="loading" class="text-gray-500">Loading posts...</div>

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

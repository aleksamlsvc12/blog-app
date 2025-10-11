<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";
import { useAuthStore } from "../stores/auth";

const auth = useAuthStore();
const route = useRoute();

const posts = ref([]);
const userName = ref("");
const loading = ref(true);

onMounted(async () => {
  try {
    const userId = route.params.id || auth.user.id;

    const userRes = await axios.get("http://localhost:8000/api/getUser.php", {
      params: { id: userId },
    });

    if (userRes.data.success) {
      userName.value = `${userRes.data.name} ${userRes.data.surname}`;
    }

    const postsRes = await axios.get(
      "http://localhost:8000/api/getUserPosts.php",
      {
        params: { user_id: userId },
      }
    );

    if (
      postsRes.data.status === "success" &&
      Array.isArray(postsRes.data.posts)
    ) {
      posts.value = postsRes.data.posts;
    } else {
      posts.value = [];
    }
  } catch (err) {
    console.error("Greška pri učitavanju postova:", err);
  } finally {
    loading.value = false;
  }
});

const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
  });
};
</script>

<template>
  <div class="w-full h-full p-10 lg:p-20 font-mono overflow-y-auto">
    <div class="w-full border-b pb-4 mb-8 text-center">
      <h1 class="text-3xl font-bold">{{ userName }}'s Posts</h1>
    </div>

    <div v-if="loading" class="text-gray-500 text-center mt-10">
      Loading posts...
    </div>

    <div
      v-else-if="posts.length === 0"
      class="text-gray-500 text-center mt-10 italic"
    >
      No posts yet.
    </div>

    <div v-else class="flex flex-col gap-6 w-full max-w-5xl mx-auto text-left">
      <div v-for="post in posts" :key="post.id" class="border p-6 bg-white">
        <div class="flex lg:justify-between lg:flex-row flex-col mb-3">
          <h2 class="text-xl font-bold mb-2">{{ post.title }}</h2>

          <div v-if="!route.params.id || route.params.id == auth.user.id" class="flex gap-4">
            <button class="cursor-pointer">
              <i class="pi pi-pencil text-sm border p-2 text-blue-600"></i>
            </button>

            <button class="cursor-pointer">
              <i class="pi pi-trash text-sm border p-2 text-red-600"></i>
            </button>
          </div>
        </div>
        <p class="text-xs text-gray-600 italic mb-2">
          Category:
          <span class="font-semibold">{{ post.category }}</span>
        </p>
        <p class="text-sm text-gray-700 mb-3 break-words">
          {{ post.content }}
        </p>
        <p class="text-xs text-gray-500">
          Published: {{ formatDate(post.created_at) }}
        </p>
      </div>
    </div>
  </div>
</template>

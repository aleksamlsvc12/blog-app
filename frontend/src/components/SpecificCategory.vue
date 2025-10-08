<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";
import { useAuthStore } from "../stores/auth";

const route = useRoute();
const auth = useAuthStore();

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
    const res = await axios.get(
      "http://localhost:8000/api/specificCategory.php",
      {
        params: {
          category: categoryName,
          user_id: auth.user.id,
        },
      }
    );

    const data = res.data;

    if (data.status === "success") {
      posts.value = data.posts.map((p) => ({
        ...p,
        likes: p.likes || 0,
        dislikes: p.dislikes || 0,
        user_reaction: p.user_reaction,
      }));
      hasNoContent.value = posts.value.length === 0;
    } else {
      hasNoContent.value = true;
    }
  } catch (err) {
    console.error(err);
    hasNoContent.value = true;
  } finally {
    loading.value = false;
  }
});

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString("en-US", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const reactToPost = async (postId, type, index) => {
  try {
    const res = await axios.post("http://localhost:8000/api/reactions.php", {
      fk_post: postId,
      fk_user: auth.user.id,
      type: type,
    });

    if (res.data.status === "success") {
      posts.value[index].likes = res.data.likes;
      posts.value[index].dislikes = res.data.dislikes;

      if (posts.value[index].user_reaction === type) {
        posts.value[index].user_reaction = null;
      } else {
        posts.value[index].user_reaction = type;
      }
    }
  } catch (err) {
    console.error(err);
  }
};
</script>

<template>
  <div class="p-10 font-mono h-full overflow-y-auto">
    <div v-if="loading" class="text-gray-500">Loading posts...</div>

    <div
      v-else-if="hasNoContent"
      class="flex flex-col gap-4 justify-center items-center h-full w-full"
    >
      <h1 class="text-2xl font-bold mb-2">
        Oops! Looks like there are no blogs on this topic.
      </h1>

      <RouterLink to="/post">
        <button class="bg-black text-white p-3 cursor-pointer w-full">
          Create one now!
        </button>
      </RouterLink>
    </div>

    <div v-else>
      <h1 class="text-2xl font-bold mb-4">
        {{ route.params.name.toUpperCase() }}:
      </h1>

      <div
        v-for="(post, index) in posts"
        :key="post.id"
        class="border p-8 mb-10 bg-white"
      >
        <div class="border p-4">
          <div
            class="flex justify-between items-center lg:flex-row flex-col-reverse gap-2"
          >
            <span class="text-lg font-bold lg:place-self-center place-self-start">
              {{ post.title }}
            </span>
            <span
              class="text-xs text-gray-700 lg:place-self-center place-self-start"
            >
              {{ formatDate(post.created_at) }}
            </span>
          </div>

          <p class="text-sm text-gray-700 mt-5 text-justify">
            {{ post.content }}
          </p>

          <RouterLink
            :to="{ name: 'OtherUserProfile', params: { id: post.fk_user } }"
          >
            <span
              class="text-xs font-bold cursor-pointer flex w-full justify-end p-2 pr-0"
            >
              {{ post.name }} {{ post.surname }}
            </span>
          </RouterLink>
        </div>

        <div class="w-full mt-2 text-sm flex">

          <div class="relative">
            <button
              class="p-2 border mr-2 cursor-pointer flex justify-center items-center"
              :class="{ 'bg-green-300': post.user_reaction === 1 }"
              @click="reactToPost(post.id, 1, index)"
            >
              <i class="pi pi-thumbs-up"></i>
            </button>

            <div
              v-if="post.likes > 0"
              class="w-[20px] h-[20px] absolute bg-green-600 -top-2 right-1 rounded-full flex justify-center items-center"
            >
              <span class="text-[10px] text-white font-bold">
                {{ post.likes }}
              </span>
            </div>
          </div>

          <div class="relative">
            <button
              class="p-2 border mr-2 cursor-pointer flex justify-center items-center"
              :class="{ 'bg-red-300': post.user_reaction === 0 }"
              @click="reactToPost(post.id, 0, index)"
            >
              <i class="pi pi-thumbs-down"></i>
            </button>

            <div
              v-if="post.dislikes > 0"
              class="w-[20px] h-[20px] absolute bg-red-600 -top-2 right-1 rounded-full flex justify-center items-center"
            >
              <span class="text-[10px] text-white font-bold">
                {{ post.dislikes }}
              </span>
            </div>
          </div>

          <div class="relative">
            <button
              class="p-2 border mr-2 cursor-pointer flex justify-center items-center"
            >
              <i class="pi pi-comment"></i>
            </button>

            <div 
              v-if="post.comments > 0"
              class="w-[20px] h-[20px] absolute bg-blue-600 -top-2 right-1 rounded-full flex justify-center items-center"
            >
              <span class="text-[10px] text-white font-bold">0</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

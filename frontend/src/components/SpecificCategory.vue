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
    const res = await axios.get(
      "http://localhost:8000/api/specificCategory.php",
      {
        params: { category: categoryName },
      }
    );

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

const formatDate = (dateString) => {
  const date = new Date (dateString);
  return date.toLocaleString("en-US", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
  });
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
        v-for="post in posts"
        :key="post.id"
        class="border p-8 mb-10 bg-white"
      >
        <div class="border p-4">
          <div
            class="flex justify-between items-center lg:flex-row flex-col-reverse gap-2"
          >
            <span
              class="text-lg font-bold lg:place-self-center place-self-start"
              >{{ post.title }}</span
            >
            <span
              class="text-xs text-gray-700 lg:place-self-center place-self-start"
              >{{ formatDate(post.created_at) }}</span
            >
          </div>
          <p class="text-sm text-gray-700 mt-5 text-justify">
            {{ post.content }}
          </p>

          <RouterLink
            :to="{
              name: 'OtherUserProfile',
              params: { id: post.fk_user },
            }"
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
            >
              <i class="pi pi-thumbs-up"></i>
            </button>

            <div
              class="w-[15px] h-[15px] absolute bg-red-500 -top-1.5 right-1 rounded-full flex justify-center items-center"
            >
              <span class="text-[8px] text-white font-bold">1</span>
            </div>
          </div>

          <div class="relative">
            <button
              class="p-2 border mr-2 cursor-pointer flex justify-center items-cente"
            >
              <i class="pi pi-thumbs-down"></i>
            </button>

            <div
              class="w-[15px] h-[15px] absolute bg-red-500 -top-1.5 right-1 rounded-full flex justify-center items-center"
            >
              <span class="text-[8px] text-white font-bold">1</span>
            </div>
          </div>

          <div class="relative">
            <button
              class="p-2 border mr-2 cursor-pointer flex justify-center items-cente"
            >
              <i class="pi pi-comment"></i>
            </button>

            <div
              class="w-[15px] h-[15px] absolute bg-red-500 -top-1.5 right-1 rounded-full flex justify-center items-center"
            >
              <span class="text-[8px] text-white font-bold">1</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useAuthStore } from "../stores/auth";
import { useRouter } from "vue-router";
import axios from "axios";

const auth = useAuthStore();
const router = useRouter();

const categories = ref([]);
const selectedCategory = ref("");
const title = ref("");
const content = ref("");
const thumbnail = ref(null);
const message = ref("");

onMounted(async () => {
  try {
    const res = await axios.get("http://localhost:8000/api/getCategories.php");
    if (res.data.status === "success") {
      categories.value = res.data.categories;
    }
  } catch (err) {
    console.error("Error loading categories:", err);
  }
});

const handleFileChange = (e) => {
  thumbnail.value = e.target.files[0];
};

const createPost = async (e) => {
  e.preventDefault();

  const formData = new FormData();
  formData.append("title", title.value);
  formData.append("content", content.value);
  formData.append("fk_category", selectedCategory.value);
  formData.append("fk_user", auth.user?.id);
  if (thumbnail.value) {
    formData.append("thumbnail", thumbnail.value);
  }

  try {
    const res = await axios.post(
      "http://localhost:8000/api/createPost.php",
      formData,
      { headers: { "Content-Type": "multipart/form-data" } }
    );

    if (res.data.status === "success") {
      alert("Post created successfully!");
      const category = categories.value.find(
        (c) => Number(c.id) === Number(selectedCategory.value)
      );
      if (category && category.name) {
        router.push(`/category/${category.name.toLowerCase()}`);
      } else {
        router.push("/categories");
      }
      title.value = "";
      content.value = "";
      selectedCategory.value = "";
      thumbnail.value = null;
    } else {
      message.value = res.data.message || "Something went wrong.";
    }
  } catch (err) {
    console.error("Error creating post:", err);
    message.value = "Server error. Try again later.";
  }
};
</script>

<template>
  <div class="w-full h-full p-8 flex justify-center items-center font-mono">
    <form
      @submit.prevent="createPost"
      class="bg-gray-700 text-gray-100 rounded-2xl p-8 lg:w-1/4 w-full h-full flex flex-col justify-around items-center"
    >
      <p class="text-lg font-bold">Create post</p>

      <div class="flex flex-col justify-center items-center gap-4 h-[95%] w-full">
        <div class="w-full">
          <label class="text-sm">Title</label>
          <input v-model="title" type="text" class="auth-inputs w-full border p-2" required />
        </div>

        <div class="w-full">
          <label class="text-sm block">Description</label>
          <textarea
            v-model="content"
            class="border w-full h-[120px] resize-none p-2 text-xs rounded-[5px]"
            required
          ></textarea>
        </div>

        <div class="w-full">
          <label class="text-sm">Thumbnail</label>
          <input
            type="file"
            accept=".png, .jpg, .jpeg"
            @change="handleFileChange"
            class="file:py-1 file:px-3 file:border-1 block text-xs border p-2 file:mr-2 w-full file:rounded-[5px] rounded-[5px]"
          />
          <p class="text-[9px] text-red-500 mt-1">*Allowed formats: .jpg, .jpeg, .png</p>
        </div>

        <div class="w-full">
          <label class="text-sm block">Category</label>
          <select
            v-model="selectedCategory"
            class="w-full h-[30px] border cursor-pointer text-xs p-2 rounded-[5px]"
            required
          >
            <option value="" disabled>Select a category</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>
      </div>

      <button
        type="submit"
        class="bg-purple-700 text-sm text-gray-100 py-3 cursor-pointer w-full mt-4 rounded-md font-bold active:scale-95"
      >
        Post
      </button>
      <p v-if="message" class="mt-4 text-center text-xs">{{ message }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";
import { useAuthStore } from "../stores/auth";

const auth = useAuthStore();
const route = useRoute();

const posts = ref([]);
const categories = ref([]);
const userName = ref("");
const loading = ref(true);

const editingPost = ref(null);
const newTitle = ref("");
const newCategory = ref(null);
const newContent = ref("");

const isMyProfile = computed(
  () => !route.params.id || route.params.id == auth.user.id
);

const loadPosts = async () => {
  try {
    loading.value = true;
    const userId = route.params.id || auth.user.id;

    const catRes = await axios.get("http://localhost:8000/api/getCategories.php");
    if (catRes.data.status === "success") {
      categories.value = catRes.data.categories;
    }

    const userRes = await axios.get("http://localhost:8000/api/getUser.php", {
      params: { id: userId },
    });
    if (userRes.data.success) {
      userName.value = `${userRes.data.name} ${userRes.data.surname}`;
    }

    const postsRes = await axios.get("http://localhost:8000/api/getUserPosts.php", {
      params: { user_id: userId },
    });

    if (postsRes.data.status === "success" && Array.isArray(postsRes.data.posts)) {
      posts.value = postsRes.data.posts.map((p) => ({
        ...p,
        fk_category: Number(p.fk_category),
      }));
    }
  } catch (err) {
    console.error("Error loading data:", err);
  } finally {
    loading.value = false;
  }
};

onMounted(loadPosts);

const deletePost = async (id) => {
  if (!confirm("Are you sure you want to delete this post?")) return;

  try {
    const res = await axios.post("http://localhost:8000/api/editPost.php", {
      action: "delete",
      post_id: id,
    });

    if (res.data.status === "success") {
      alert("Post deleted successfully.");
      posts.value = posts.value.filter((p) => p.id !== id);
    } else {
      alert(res.data.message || "Error deleting post.");
    }
  } catch (err) {
    console.error("Error deleting post:", err);
    alert("An error occurred while deleting the post.");
  }
};

const startEdit = (post) => {
  editingPost.value = post.id;
  newTitle.value = post.title;
  newCategory.value = Number(post.fk_category);
  newContent.value = post.content;
};

const cancelEdit = () => {
  editingPost.value = null;
  newTitle.value = "";
  newCategory.value = null;
  newContent.value = "";
};

const saveEdit = async (id) => {
  if (!newTitle.value.trim() || !newContent.value.trim() || !newCategory.value) {
    alert("All fields are required.");
    return;
  }

  try {
    const res = await axios.post("http://localhost:8000/api/editPost.php", {
      action: "edit",
      post_id: id,
      title: newTitle.value,
      category: Number(newCategory.value),
      content: newContent.value,
    });

    if (res.data.status === "success") {
      alert("Post updated successfully.");
      editingPost.value = null;
      await loadPosts();
    } else {
      alert(res.data.message || "Error updating post.");
    }
  } catch (err) {
    console.error("Error updating post:", err);
    alert("An error occurred while updating the post.");
  }
};

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

    <div v-else-if="posts.length === 0" class="text-gray-500 text-center mt-10 italic">
      No posts found.
    </div>

    <div v-else class="flex flex-col gap-6 w-full max-w-5xl mx-auto text-left">
      <div v-for="post in posts" :key="post.id" class="border p-6 bg-white">
        <div v-if="editingPost === post.id" class="p-4 bg-gray-50">
          <label class="block text-sm mb-2">Title:</label>
          <input v-model="newTitle" class="border p-2 w-full mb-3 text-sm" placeholder="Enter title" />

          <label class="block text-sm mb-2">Category:</label>
          <select v-model.number="newCategory" class="border p-2 w-full mb-3 text-sm">
            <option disabled value="">Select category</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>

          <label class="block text-sm mb-2">Content:</label>
          <textarea
            v-model="newContent"
            class="border p-2 w-full mb-3 h-[200px] resize-none text-sm"
            placeholder="Write something..."
          ></textarea>

          <div class="flex gap-3">
            <button @click="saveEdit(post.id)" class="bg-green-500 text-white px-3 py-1 text-sm cursor-pointer">
              Save
            </button>
            <button @click="cancelEdit" class="bg-gray-400 text-white px-3 py-1 text-sm cursor-pointer">
              Cancel
            </button>
          </div>
        </div>

        <div v-else>
          <div class="flex lg:justify-between lg:flex-row flex-col mb-3">
            <h2 class="text-xl font-bold mb-2">{{ post.title }}</h2>

            <div v-if="isMyProfile" class="flex gap-4">
              <button @click="startEdit(post)" class="cursor-pointer">
                <i class="pi pi-pencil text-sm border p-2 text-blue-600"></i>
              </button>
              <button @click="deletePost(post.id)" class="cursor-pointer">
                <i class="pi pi-trash text-sm border p-2 text-red-600"></i>
              </button>
            </div>
          </div>

          <p class="text-xs text-gray-600 italic mb-2">
            Category: <span class="font-semibold">{{ post.category }}</span>
          </p>
          <p class="text-sm text-gray-700 mb-3 break-words">{{ post.content }}</p>
          <p class="text-xs text-gray-500">Published: {{ formatDate(post.created_at) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

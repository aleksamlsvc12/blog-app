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
const newThumbnail = ref(null);
const removeThumbnail = ref(false);

const handleFileChange = (e) => {
  newThumbnail.value = e.target.files[0];
};

// Determine if the viewed profile belongs to the logged-in user
const isMyProfile = computed(
  () => !route.params.id || route.params.id == auth.user.id
);

// Load all user data (profile, categories, and posts)
const loadPosts = async () => {
  try {
    loading.value = true;
    const userId = route.params.id || auth.user.id;

    // Fetch all available categories
    const catRes = await axios.get(
      "http://localhost:8000/api/getCategories.php"
    );
    if (catRes.data.status === "success") {
      categories.value = catRes.data.categories;
    }

    // Fetch user information
    const userRes = await axios.get("http://localhost:8000/api/getUser.php", {
      params: { id: userId },
    });
    if (userRes.data.success) {
      userName.value = `${userRes.data.name} ${userRes.data.surname}`;
    }

    // Fetch user's posts
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

// Delete post
const deletePost = async (id) => {
  if (!confirm("Are you sure you want to delete this post?")) return;

  try {
    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("post_id", id);

    const res = await axios.post(
      "http://localhost:8000/api/editPost.php",
      formData
    );

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

// Start editing mode and preload current post data
const startEdit = (post) => {
  editingPost.value = post.id;
  newTitle.value = post.title;
  newCategory.value = Number(post.fk_category);
  newContent.value = post.content;
  newThumbnail.value = null;
  removeThumbnail.value = false;
};

// Cancel editing and reset form fields
const cancelEdit = () => {
  editingPost.value = null;
  newTitle.value = "";
  newCategory.value = null;
  newContent.value = "";
  newThumbnail.value = null;
  removeThumbnail.value = false;
};

// Save edited post and refresh data after update
const saveEdit = async (id) => {
  if (
    !newTitle.value.trim() ||
    !newContent.value.trim() ||
    !newCategory.value
  ) {
    alert("All fields are required.");
    return;
  }

  const formData = new FormData();
  formData.append("action", "edit");
  formData.append("post_id", id);
  formData.append("title", newTitle.value);
  formData.append("category", newCategory.value);
  formData.append("content", newContent.value);
  formData.append("remove_image", removeThumbnail.value);
  if (newThumbnail.value) {
    formData.append("thumbnail", newThumbnail.value);
  }

  try {
    const res = await axios.post(
      "http://localhost:8000/api/editPost.php",
      formData,
      {
        headers: { "Content-Type": "multipart/form-data" },
      }
    );

    if (res.data.status === "success") {
      alert("Post updated successfully.");
      editingPost.value = null;
      newThumbnail.value = null;
      removeThumbnail.value = false;
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
    <!-- Page header -->
    <div class="w-full pb-4 mb-8 text-center">
      <h1
        class="text-3xl font-bold text-gray-100 pb-2 border-b border-b-gray-600"
      >
        {{ userName }}'s Posts
      </h1>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="text-gray-400 text-center mt-10">
      Loading posts...
    </div>

    <!-- Empty state -->
    <div
      v-else-if="posts.length === 0"
      class="text-gray-400 text-center mt-10 italic"
    >
      No posts found.
    </div>

    <!-- Posts list -->
    <div v-else class="flex flex-col gap-6 w-full max-w-5xl mx-auto text-left">
      <div
        v-for="post in posts"
        :key="post.id"
        class="p-6 bg-gray-700 rounded-xl text-gray-100"
      >
        <!-- Edit mode -->
        <div v-if="editingPost === post.id" class="p-4 bg-gray-800 rounded-lg">
          <label class="block text-sm mb-1">Title:</label>
          <input
            v-model="newTitle"
            class="auth-inputs mb-4"
            placeholder="Enter title"
          />

          <label class="block text-sm mb-1">Category:</label>
          <select v-model.number="newCategory" class="auth-inputs mb-4">
            <option disabled value="">Select category</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>

          <label class="block text-sm mb-1">Content:</label>
          <textarea
            v-model="newContent"
            class="auth-inputs h-[200px] resize-none mb-4"
            placeholder="Write something..."
          ></textarea>

          <!-- Thumbnail controls -->
          <div class="mb-4">
            <label class="block text-sm mb-1">Thumbnail:</label>

            <!-- If post currently has an image -->
            <div v-if="post.image && !newThumbnail">
              <img
                :src="`http://localhost:8000/${post.image}`"
                alt="Current thumbnail"
                class="w-full max-h-[200px] object-cover rounded-md mb-2"
              />
              <label class="flex items-center gap-2 text-xs mb-2">
                <input type="checkbox" v-model="removeThumbnail" />
                Remove current thumbnail
              </label>
            </div>

            <!-- File input for new upload -->
            <input
              type="file"
              accept=".png, .jpg, .jpeg"
              @change="handleFileChange"
              class="file:py-1 file:px-3 file:border-1 block text-xs border p-2 file:mr-2 w-full file:rounded-[5px] rounded-[5px]"
            />

            <p class="text-[10px] text-gray-400 mt-1">
              *Leave empty to keep existing image
            </p>
          </div>

          <!-- Edit controls -->
          <div class="flex gap-3">
            <button
              @click="saveEdit(post.id)"
              class="bg-green-600 text-white font-bold px-3 py-1 text-sm rounded-md cursor-pointer active:scale-95"
            >
              Save
            </button>
            <button
              @click="cancelEdit"
              class="bg-gray-300 text-black font-bold px-3 py-1 text-sm rounded-md cursor-pointer active:scale-95"
            >
              Cancel
            </button>
          </div>
        </div>

        <!-- View mode -->
        <div v-else>
          <div class="flex lg:justify-between lg:flex-row flex-col mb-3">
            <h2 class="text-xl font-bold mb-2">{{ post.title }}</h2>

            <!-- Edit/delete buttons -->
            <div v-if="isMyProfile" class="flex gap-4">
              <button
                @click="startEdit(post)"
                class="cursor-pointer bg-gray-600 p-2 rounded-md active:scale-95 flex justify-center items-center"
              >
                <i class="pi pi-pencil text-blue-500"></i>
              </button>
              <button
                @click="deletePost(post.id)"
                class="cursor-pointer bg-gray-600 p-2 rounded-md active:scale-95 flex justify-center items-center"
              >
                <i class="pi pi-trash text-red-500"></i>
              </button>
            </div>
          </div>

          <!-- Post metadata -->
          <p class="text-xs text-gray-400 italic mb-2">
            Category:
            <span class="font-semibold text-gray-200">{{ post.category }}</span>
          </p>

          <!-- Thumbnail prikaz -->
          <div v-if="post.image" class="my-3">
            <img
              :src="`http://localhost:8000/${post.image}`"
              alt="Post thumbnail"
              class="w-full max-h-[300px] object-cover rounded-lg"
            />
          </div>

          <!-- Post content -->
          <p class="text-sm text-gray-100 mb-3 break-words">
            {{ post.content }}
          </p>

          <!-- Publication date -->
          <p class="text-xs text-gray-500">
            Published: {{ formatDate(post.created_at) }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

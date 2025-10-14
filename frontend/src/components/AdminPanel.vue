<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const users = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
  try {
    const res = await axios.get("http://localhost:8000/api/getAllUsers.php", {
      withCredentials: true,
    });

    if (res.data.ok) {
      users.value = res.data.users;
    } else {
      error.value = "Failed to fetch users.";
    }
  } catch (err) {
    error.value = err.message || "Network error";
    console.error("Axios error:", err);
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
  });
};
</script>

<template>
  <div class="p-10 h-full font-mono">
    <h1 class="text-2xl font-bold mb-6 text-center text-white">
      Registered Users
    </h1>

    <!-- Loading & error handling -->
    <div v-if="loading" class="text-center text-gray-500">Loading...</div>
    <div v-else-if="error" class="text-red-500 text-center">{{ error }}</div>

    <!-- Users grid -->
    <div
      v-else
      class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 justify-items-center"
    >
      <div
        v-for="user in users"
        :key="user.id"
        class="bg-gray-700 rounded-xl p-5 w-full"
      >
        <div class="text-lg font-bold text-white mb-1 flex justify-between">
          <span> {{ user.name }} {{ user.surname }} </span>
          <div>
            <button class="cursor-pointer border flex justify-center items-center p-2 rounded-lg">
              <i class="pi pi-trash text-sm"></i>
            </button>
          </div>
        </div>
        <div class="text-gray-100 text-sm mb-2">{{ user.email }}</div>

        <div class="text-gray-100 text-sm mb-1">
          <span>User Type: </span>
          <span v-if="user.fk_user_type === 1" class="text-white font-bold">
            Admin</span
          >
          <span
            v-else-if="user.fk_user_type === 2"
            class="text-white font-bold"
          >
            Moderator</span
          >
          <span v-else class="text-white font-bold"> User</span>
        </div>

        <div class="text-gray-400 text-xs">
          Created at: {{ formatDate(user.created_at) }}
        </div>
      </div>
    </div>
  </div>
</template>

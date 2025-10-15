<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const users = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
  await fetchUsers();
});

const fetchUsers = async () => {
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
  } finally {
    loading.value = false;
  }
};

const modifyUser = async (user_id, action) => {
  try {
    const res = await axios.post(
      "http://localhost:8000/api/modifyUsers.php",
      { user_id, action },
      { withCredentials: true }
    );

    if (res.data.ok) {
      await fetchUsers();
    } else {
      alert(res.data.message || "Action failed");
    }
  } catch (err) {
    console.error("Modify user error:", err);
    alert("Server error: " + err.message);
  }
};

const deleteUser = (id) => {
  if (confirm("Are you sure you want to delete this user?")) {
    modifyUser(id, "delete");
    alert("User deleted successfully.");
  }
};

const toggleRole = (user) => {
  const action = user.fk_user_type === 2 ? "demote" : "promote";
  modifyUser(user.id, action);
};

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
  <div class="p-10 h-full font-mono overflow-y-auto">
    <h1 class="text-2xl font-bold mb-6 text-center text-white">Registered Users</h1>

    <div v-if="loading" class="text-center text-gray-500">Loading...</div>
    <div v-else-if="error" class="text-red-500 text-center">{{ error }}</div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 justify-items-center">
      <div v-for="user in users" :key="user.id" class="bg-gray-700 rounded-xl p-5 w-full">
        <div class="text-lg font-bold text-white mb-1 flex justify-between">
          <span>{{ user.name }} {{ user.surname }}</span>
          <div class="flex gap-2">
            <!-- Promote / Demote -->
            <button
              @click="toggleRole(user)"
              class="cursor-pointer border flex justify-center items-center p-2 rounded-lg"
              :title="user.fk_user_type === 2 ? 'Demote to User' : 'Promote to Editor'"
            >
              <i :class="user.fk_user_type === 2 ? 'pi pi-user-minus' : 'pi pi-user-plus'" class="text-sm"></i>
            </button>

            <!-- Delete -->
            <button
              @click="deleteUser(user.id)"
              class="cursor-pointer border flex justify-center items-center p-2 rounded-lg"
              title="Delete User"
            >
              <i class="pi pi-trash text-sm"></i>
            </button>
          </div>
        </div>

        <div class="text-gray-100 text-sm mb-2">{{ user.email }}</div>

        <div class="text-gray-100 text-sm mb-1">
          <span>User Type: </span>
          <span
            :class="{
              'text-black font-bold': user.fk_user_type === 2,
              'text-white font-bold': user.fk_user_type === 3
            }"
          >
            {{ user.fk_user_type === 2 ? 'Moderator' : 'User' }}
          </span>
        </div>

        <div class="text-gray-400 text-xs">
          Created at: {{ formatDate(user.created_at) }}
        </div>
      </div>
    </div>
  </div>
</template>

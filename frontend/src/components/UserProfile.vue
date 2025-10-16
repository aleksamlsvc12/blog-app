<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useAuthStore } from "../stores/auth";

const auth = useAuthStore();
const userName = ref("");
const title = ref("");
const bio = ref("");
const created_at = ref("");
const image = ref("");

onMounted(async () => {
  const res = await axios.get("http://localhost:8000/api/getUser.php", {
    params: { id: auth.user.id },
  });

  if (res.data.success) {
    userName.value = `${res.data.name} ${res.data.surname}`;
    created_at.value = res.data.created_at;
    title.value = res.data.title;
    bio.value = res.data.bio;
    image.value = res.data.image
      ? `http://localhost:8000/${res.data.image}`
      : "";
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
  <!-- Main profile container -->
  <div
    class="w-full h-full lg:p-20 p-10 flex lg:flex-row flex-col justify-center items-center font-mono"
  >
    <!-- Profile layout -->
    <div
      class="flex lg:flex-row flex-col w-full justify-between lg:h-[70%] h-full gap-10"
    >
      <!-- User avatar -->
      <div class="lg:w-1/4 w-full h-full flex justify-center items-center p-5">
        <div
          class="w-[300px] h-[300px] rounded-full overflow-hidden border border-white flex justify-center items-center bg-gray-700"
        >
          <img
            v-if="image"
            :src="image"
            alt="Profile"
            class="object-fit w-full h-full"
          />
          <i v-else class="pi pi-user text-9xl text-gray-300"></i>
        </div>
      </div>

      <!-- Profile information -->
      <div
        class="lg:w-[75%] w-full h-full lg:p-10 flex flex-col overflow-y-auto text-gray-100"
      >
        <!-- Header with username and navigation buttons -->
        <div>
          <div class="flex lg:flex-row flex-col justify-between text-white">
            <span class="text-4xl font-bold">
              {{ userName }}
            </span>

            <div class="flex gap-2">
              <!-- Link to edit profile page -->
              <RouterLink to="/edit">
                <button
                  class="border py-1 px-3 cursor-pointer flex justify-center items-center text-sm mt-2 mb-2 rounded-md active:scale-95"
                >
                  Edit Profile
                </button>
              </RouterLink>

              <!-- Link to user's posts -->
              <RouterLink to="/profile/posts">
                <button
                  class="border py-1 px-3 cursor-pointer flex justify-center items-center text-sm mt-2 mb-2 rounded-md active:scale-95"
                >
                  Posts
                </button>
              </RouterLink>
            </div>
          </div>
        </div>

        <!-- User title  -->
        <p>{{ title }}</p>

        <!-- Account creation date -->
        <div>
          <p class="text-xs mt-2 mb-1 text-gray-400">
            Since: {{ formatDate(created_at) }}
          </p>
          <hr />
        </div>

        <!-- User biography -->
        <div class="w-full mt-4 text-sm text-justify overflow-y-auto pl-4 pr-4">
          {{ bio }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useAuthStore } from "../stores/auth";

const auth = useAuthStore();
const userName = ref("");
const created_at = ref("");

onMounted(async () => {
  const res = await axios.get("http://localhost:8000/api/getUser.php", {
    params: { id: auth.user.id },
  });
  if (res.data.success) {
    userName.value = `${res.data.name} ${res.data.surname}`;

    const date = new Date(res.data.created_at);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1);
    const day = String(date.getDate());

    created_at.value = `${year}-${month}-${day}`;
  }
});
</script>

<template>
  <div class="w-full h-full lg:p-20 p-10 flex lg:flex-row flex-col justify-center items-center">

    <div class="flex lg:flex-row flex-col w-full justify-between lg:h-[70%] h-full gap-10">
      <div class="lg:w-1/4 w-full h-full border flex justify-center items-center p-5">
        <div
          class="w-full h-full bg-gray-400 flex justify-center items-center"
        >
          <i class="pi pi-user text-9xl"></i>
        </div>
      </div>

      <div class="lg:w-[75%] overflow-y-auto w-full h-full border p-10 flex flex-col">
        <div>
          <p class="text-4xl font-bold">{{ userName }}</p>
        </div>

        <div>
          <p class="text-sm">Since: {{ created_at }}</p>
          <hr />
        </div>

        <div class="w-full lg:h-[20%] p-2 flex justify-end">
          <button class="border p-1 pl-2 pr-2 cursor-pointer flex justify-center items-center text-sm">
            Edit Profile
          </button>
        </div>
      </div>

    </div>

  </div>
</template>

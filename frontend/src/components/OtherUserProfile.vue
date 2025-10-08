<script setup>
import { useRoute } from "vue-router";
import { ref, onMounted } from "vue";
import axios from "axios";

const route = useRoute();

const userName = ref("");
const title = ref("");
const bio = ref("");
const created_at = ref("");

onMounted(async () => {
  const res = await axios.get("http://localhost:8000/api/getUser.php", {
    params: { id: route.params.id },
  });
  if (res.data.success) {
    userName.value = `${res.data.name} ${res.data.surname}`;

    const date = new Date(res.data.created_at);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1);
    const day = String(date.getDate());

    created_at.value = `${year}-${month}-${day}`;

    title.value = res.data.title;
    bio.value = res.data.bio;
  }
});

</script>

<template>
  <div
    class="w-full h-full lg:p-20 p-10 flex lg:flex-row flex-col justify-center items-center font-mono"
  >
    <div
      class="flex lg:flex-row flex-col w-full justify-between lg:h-[70%] h-full gap-10"
    >
      <div
        class="lg:w-1/4 w-full h-full border flex justify-center items-center p-5"
      >
        <div class="w-full h-full bg-gray-400 flex justify-center items-center">
          <i class="pi pi-user text-9xl"></i>
        </div>
      </div>

      <div
        class="lg:w-[75%] overflow-y-auto w-full h-full border p-10 flex flex-col"
      >
        <div>
          <div class="flex lg:flex-row flex-col justify-between">
            <span class="text-4xl font-bold"> {{ userName }} </span>
          </div>
        </div>

        <p>{{ title }}</p>

        <div>
          <p class="text-xs mt-2 mb-1">Since: {{ created_at }}</p>
          <hr />
        </div>

        <div class="w-full mt-4 text-sm text-justify">{{ bio }}</div>
      </div>
    </div>
  </div>
</template>

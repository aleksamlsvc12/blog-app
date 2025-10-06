<script setup>
import { ref } from "vue";
import axios from "axios";
import { useAuthStore } from "../stores/auth";
import { useRouter } from "vue-router";

const auth = useAuthStore();
const router = useRouter();

const name = ref("");
const surname = ref("");
const email = ref("");
const password = ref("");
const title = ref("");
const bio = ref("");

const message = ref("");
const error = ref("");

const editUser = async () => {
  message.value = "";
  error.value = "";

  try {
    const res = await axios.post("http://localhost:8000/api/editUser.php", {
      id: auth.user.id,
      name: name.value,
      surname: surname.value,
      email: email.value,
      password: password.value,
      title: title.value,
      bio: bio.value,
    });

    if (res.data.ok) {
      alert("Profile updated!");
      router.push('/profile');
    } else {
      error.value = res.data.message || "Something went wrong.";
    }
  } catch (err) {
    if (err.response && err.response.data && err.response.data.message) {
      error.value = err.response.data.message;
    } 
    else if (err.response && err.response.data && err.response.data.errors) {
      error.value = Object.values(err.response.data.errors).join(", ");
    } 
    else {
      error.value = "Unknown error: " + err.message;
    }
  }
};
</script>

<template>
  <div class="w-full h-full p-10 flex justify-center items-center font-mono">
    <div class="flex w-full justify-center h-full">
      <div
        class="overflow-y-auto w-full h-full border lg:p-10 p-5 flex flex-col"
      >
        <p class="font-bold mb-4 text-2xl lg:text-left text-center">
          Edit your profile
        </p>

        <div class="flex lg:flex-row flex-col gap-2 justify-between mb-2">
          <input
            v-model="name"
            type="text"
            class="border p-2 text-sm lg:w-[48%] w-full"
            placeholder="Change your name"
          />
          <input
            v-model="surname"
            type="text"
            class="border p-2 text-sm lg:w-[48%]"
            placeholder="Change your surname"
          />
        </div>

        <div class="flex lg:flex-row flex-col gap-2 justify-between mt-2 mb-2">
          <input
            v-model="email"
            type="text"
            class="border p-2 text-sm lg:w-[48%] w-full"
            placeholder="Change your email"
          />
          <input
            v-model="password"
            type="text"
            class="border p-2 text-sm lg:w-[48%]"
            placeholder="Change your password"
          />
        </div>

        <div class="flex justify-between mt-2 mb-2">
          <input
            v-model="title"
            type="text"
            class="border p-2 text-sm w-full"
            placeholder="Enter your title"
          />
        </div>

        <div>
          <textarea
            v-model="bio"
            name="bio"
            id="bio"
            placeholder="Enter your biography"
            class="border p-2 text-sm w-full resize-none h-[200px]"
          ></textarea>
        </div>

        <div
          class="w-full pt-2 flex lg:flex-row flex-col justify-between items-center"
        >
          <input
            type="file"
            accept=".png, .jpg, .jpeg"
            disabled
            class="file:px-2 file:py-1 file:border border file:mr-2 file:text-sm cursor-not-allowed text-sm lg:w-auto bg-gray-200 w-full p-2"
          />

          <button
            @click="editUser"
            class="bg-black text-white p-2 cursor-pointer lg:w-auto w-full lg:m-0 mt-4"
          >
            Save Profile
          </button>
        </div>

        <p v-if="error" class="text-red-500 mt-2 text-xs lg:text-left text-center">{{ error }}</p>
      </div>
    </div>
  </div>
</template>

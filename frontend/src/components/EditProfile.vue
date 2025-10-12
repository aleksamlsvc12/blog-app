<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useAuthStore } from "../stores/auth";
import { useRouter } from "vue-router";

const auth = useAuthStore();
const router = useRouter();

const passwordInput = ref(null);

const name = ref("");
const surname = ref("");
const email = ref("");
const password = ref("");
const title = ref("");
const bio = ref("");

const message = ref("");
const error = ref("");

onMounted(async () => {
  try {
    const res = await axios.get("http://localhost:8000/api/getUser.php", {
      params: { id: auth.user.id },
    });

    if (res.data.success) {
      name.value = res.data.name || "";
      surname.value = res.data.surname || "";
      email.value = res.data.email || "";
      title.value = res.data.title || "";
      bio.value = res.data.bio || "";
    } else {
      error.value = "Could not load profile data.";
    }
  } catch (err) {
    error.value = "Error while loading user data";
  }
});

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
      router.push("/profile");
    } else {
      error.value = res.data.message || "Something went wrong.";
    }
  } catch (err) {
    if (err.response?.data?.message) {
      error.value = err.response.data.message;
    } else if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors).join(", ");
    } else {
      error.value = "Unknown error: " + err.message;
    }
  }
};

function passVisible() {
  if (passwordInput.value.type == "text") {
    passwordInput.value.type = "password";
  } else {
    passwordInput.value.type = "text";
  }
}
</script>

<template>
  <div class="w-full h-full p-10 flex justify-center items-center font-mono">
    <div class="flex w-full justify-center h-full">
      <div
        class="overflow-y-auto w-full h-full flex justify-center flex-col text-gray-100"
      >
        <p class="font-bold mb-4 text-2xl lg:text-left text-center text-white">
          Edit your profile
        </p>

        <div class="flex lg:flex-row flex-col gap-2 justify-between mb-2">
          <input
            v-model="name"
            type="text"
            class="border p-2 text-sm lg:w-[48%] w-full rounded-md"
            placeholder="Change your name"
          />
          <input
            v-model="surname"
            type="text"
            class="border p-2 text-sm lg:w-[48%] rounded-md"
            placeholder="Change your surname"
          />
        </div>

        <div class="flex lg:flex-row flex-col gap-2 justify-between mt-2 mb-2">
          <input
            v-model="email"
            type="text"
            class="border p-2 text-sm lg:w-[48%] w-full rounded-md"
            placeholder="Change your email"
          />

          <div class="relative lg:w-[48%] w-full">
            <input
              ref="passwordInput"
              v-model="password"
              type="password"
              class="border p-2 text-sm w-full rounded-md"
              placeholder="Change your password"
            />

            <button
              @click.prevent="passVisible()"
              id="passwordButton"
              class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-100 flex items-center justify-center"
            >
              <i class="pi pi-eye"></i>
            </button>
          </div>
        </div>

        <div class="flex justify-between mt-2 mb-2">
          <input
            v-model="title"
            type="text"
            class="border p-2 text-sm w-full rounded-md"
            placeholder="Enter your title"
          />
        </div>

        <div>
          <textarea
            v-model="bio"
            name="bio"
            id="bio"
            placeholder="Enter your biography"
            class="border p-2 text-sm w-full resize-none h-[200px] rounded-md"
          ></textarea>
        </div>

        <div
          class="w-full pt-2 flex lg:flex-row flex-col justify-between items-center"
        >
          <input
            type="file"
            accept=".png, .jpg, .jpeg"
            disabled
            class="file:px-2 file:py-1 file:border border file:mr-2 file:text-sm cursor-not-allowed text-sm lg:w-auto file:rounded-md w-full p-2 rounded-md"
          />

          <div class="flex gap-3 lg:mt-0 mt-2 lg:w-auto w-full">
            <button
              @click="editUser"
              class="text-xs bg-green-500 font-bold rounded-md text-white px-3 py-2 cursor-pointer active:scale-95"
            >
              Save
            </button>

            <RouterLink to="/profile">
              <button
                class="text-xs text-black bg-gray-300 font-bold rounded-md px-3 py-2 cursor-pointer active:scale-95"
              >
                Cancel
              </button>
            </RouterLink>
          </div>
        </div>

        <p
          v-if="error"
          class="text-red-500 mt-2 text-xs lg:text-left text-center"
        >
          {{ error }}
        </p>
        <p
          v-if="message"
          class="text-green-500 mt-2 text-xs lg:text-left text-center"
        >
          {{ message }}
        </p>
      </div>
    </div>
  </div>
</template>

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
const image = ref(null);
const originalImage = ref(null);
const removeImageFlag = ref(false);

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
      originalImage.value = res.data.image || null;
    } else {
      error.value = "Could not load profile data.";
    }
  } catch {
    error.value = "Error while loading user data";
  }
});

const handleFileChange = (e) => {
  image.value = e.target.files[0];
  removeImageFlag.value = false;
  alert("New profile image uploaded (pending save).");
};

const editUser = async () => {
  message.value = "";
  error.value = "";

  try {
    const formData = new FormData();
    formData.append("id", auth.user.id);
    formData.append("name", name.value);
    formData.append("surname", surname.value);
    formData.append("email", email.value);
    formData.append("password", password.value);
    formData.append("title", title.value);
    formData.append("bio", bio.value);

    formData.append("remove_image", removeImageFlag.value ? "1" : "0");

    if (image.value) formData.append("profile_img", image.value);

    const res = await axios.post(
      "http://localhost:8000/api/editUser.php",
      formData,
      {
        headers: { "Content-Type": "multipart/form-data" },
      }
    );

    if (res.data.ok) {
      alert("Profile updated!");
      router.push("/profile");
    } else {
      error.value = res.data.message || "Something went wrong.";
    }
  } catch (err) {
    error.value = err.response?.data?.message || err.message;
  }
};

const removeProfileImage = () => {
  if (!confirm("Are you sure you want to remove your profile image?")) return;

  removeImageFlag.value = true;
  image.value = null;
  alert("Profile image removed (pending save).");
};

function passVisible() {
  passwordInput.value.type =
    passwordInput.value.type === "text" ? "password" : "text";
}
</script>

<template>
  <!-- Main layout container -->
  <div class="w-full h-full p-10 flex justify-center items-center font-mono">
    <div class="flex w-full justify-center h-full">
      <div
        class="overflow-y-auto w-full h-full flex justify-center flex-col text-gray-100"
      >
        <p class="font-bold mb-4 text-2xl lg:text-left text-center text-white">
          Edit your profile
        </p>

        <!-- Inputs for name and surname -->
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

        <!-- Email and password inputs -->
        <div class="flex lg:flex-row flex-col gap-2 justify-between mt-2 mb-2">
          <input
            v-model="email"
            type="text"
            class="border p-2 text-sm lg:w-[48%] w-full rounded-md"
            placeholder="Change your email"
          />

          <!-- Password field with visibility toggle -->
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

        <!-- Title input -->
        <div class="flex justify-between mt-2 mb-2">
          <input
            v-model="title"
            type="text"
            class="border p-2 text-sm w-full rounded-md"
            placeholder="Enter your title"
          />
        </div>

        <!-- Biography textarea -->
        <div>
          <textarea
            v-model="bio"
            name="bio"
            id="bio"
            placeholder="Enter your biography"
            class="border p-2 text-sm w-full resize-none h-[200px] rounded-md"
          ></textarea>
        </div>

        <!-- File upload and action buttons -->
        <div
          class="w-full pt-2 flex lg:flex-row flex-col justify-between items-center"
        >
          <div class="flex gap-4">
            <input
              type="file"
              accept=".png, .jpg, .jpeg"
              @change="handleFileChange"
              class="file:px-2 file:py-1 file:border border file:mr-2 file:text-sm text-sm lg:w-auto file:rounded-md w-full p-2 rounded-md"
            />

            <button
              @click="removeProfileImage"
              class="text-sm border-gray-100 pl-4 pr-4 border rounded-md cursor-pointer"
            >
              Remove profile image
            </button>
          </div>

          <!-- Save and cancel buttons -->
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

        <!-- Error and success messages -->
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

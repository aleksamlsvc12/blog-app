<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();

const passwordInput = ref(null);

const email = ref("");
const password = ref("");
const message = ref("");
const success = ref(false);

const router = useRouter();

const loginUser = async () => {
  try {
    const res = await axios.post(
      "http://localhost:8000/api/login.php",
      {
        email: email.value,
        password: password.value,
      },
      { headers: { "Content-Type": "application/json" } }
    );

    console.log("✅", res.status, res.data);

    if (res.data && res.data.ok === true) {
      success.value = true;
      auth.login()
    } else {
      success.value = false;
    }

    if (res.data && res.data.message) {
      message.value = res.data.message;
    } else {
      message.value = JSON.stringify(res.data);
    }

    if (success.value) router.push("/categories");
  } catch (err) {
    if (err.response) {
      console.log("❌", err.response.status, err.response.data);
    } else {
      console.log("❌ No response");
    }

    if (err.response && err.response.data && err.response.data.errors) {
      const errorsObj = err.response.data.errors;
      message.value = Object.values(errorsObj).join(" ");
    } else if (err.response && err.response.data && err.response.data.message) {
      message.value = err.response.data.message;
    } else {
      message.value = "Server error";
    }

    success.value = false;
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
  <div class="h-full flex justify-center items-center font-mono">
    <form
      @submit.prevent="loginUser"
      class="flex flex-col justify-between w-[80%] lg:w-1/4 h-[70%] p-10 bg-white"
    >
      <p class="text-lg font-bold text-center">Login</p>

      <div>
        <label for="Email">Email </label>
        <input v-model="email" type="email" class="auth-inputs" />
      </div>

      <div>
        <label for="Password">Password </label>
        <div class="relative">
          <input
            v-model="password"
            ref="passwordInput"
            type="password"
            class="auth-inputs"
          />
          <button
            @click.prevent="passVisible()"
            id="passwordButton"
            class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-600 flex items-center justify-center"
          >
            <i class="pi pi-eye"></i>
          </button>
        </div>
      </div>

      <div>
          <button
            type="submit"
            class="bg-black text-white p-3 cursor-pointer w-full"
          >
            Log in
          </button>

        <p class="text-xs text-center mt-2 text-gray-800">
          Don't have an account?
          <RouterLink to="/register" class="font-bold cursor-pointer"
            >Register</RouterLink
          >
        </p>

        <p v-if="message" class="text-center text-xs text-red-500 mt-4">
          {{ message }}
        </p>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";

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

    console.log(res.status, res.data);

    // Check backend response for success
    if (res.data && res.data.ok === true) {
      success.value = true;
      auth.login(res.data.user); // Save user data in auth store
    } else {
      success.value = false;
    }

    if (res.data && res.data.message) {
      message.value = res.data.message;
    } else {
      message.value = JSON.stringify(res.data);
    }

    // Redirect to categories page on successful login
    if (success.value) router.push("/categories");
  } catch (err) {
    if (err.response) {
      console.log(err.response.status, err.response.data);
    } else {
      console.log("No response");
    }

    // Display validation or generic server errors
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

// Toggle password visibility
function passVisible() {
  if (passwordInput.value.type == "text") {
    passwordInput.value.type = "password";
  } else {
    passwordInput.value.type = "text";
  }
}
</script>

<template>
  <!-- Main login container -->
  <div class="h-full flex justify-center items-center font-mono">
    <form
      @submit.prevent="loginUser"
      class="flex flex-col justify-between w-[80%] lg:w-1/4 h-[70%] p-10 bg-gray-700 text-gray-100 rounded-2xl"
    >
      <p class="text-lg font-bold text-center text-white">Login</p>

      <!-- Email input -->
      <div>
        <label for="Email" class="text-sm">Email </label>
        <input v-model="email" type="email" class="auth-inputs" />
      </div>

      <!-- Password input toggle -->
      <div>
        <label for="Password" class="text-sm">Password </label>
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
            class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-10 flex items-center justify-center"
          >
            <i class="pi pi-eye"></i>
          </button>
        </div>
      </div>

      <!-- Login button and navigation links -->
      <div>
        <button
          type="submit"
          class="w-full bg-blue-800 text-gray-100 text-sm py-3 font-bold rounded-md cursor-pointer active:scale-95"
        >
          Log in
        </button>

        <p class="text-xs text-center mt-2 text-gray-400">
          Don't have an account?
          <RouterLink
            to="/register"
            class="font-bold cursor-pointer text-gray-100"
            >Register</RouterLink
          >
        </p>

        <!-- Display backend messages -->
        <p v-if="message" class="text-center text-xs text-red-500 mt-4">
          {{ message }}
        </p>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const passwordInput = ref(null);

const name = ref("");
const surname = ref("");
const email = ref("");
const password = ref("");
const message = ref("");
const success = ref(false);

const router = useRouter();

const registerUser = async () => {
  try {
    const res = await axios.post(
      "http://localhost:8000/api/register.php",
      {
        name: name.value,
        surname: surname.value,
        email: email.value,
        password: password.value,
      },
      { headers: { "Content-Type": "application/json" } }
    );

    console.log("✅", res.status, res.data);

    if (res.data && res.data.ok === true) {
      success.value = true;
    } else {
      success.value = false;
    }

    if (res.data && res.data.message) {
      message.value = res.data.message;
    } else {
      message.value = JSON.stringify(res.data);
    }

    alert(message.value);

    if (success.value) router.push("/login");
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
      @submit.prevent="registerUser"
      class="flex flex-col justify-between w-[80%] lg:w-1/4 h-[85%] p-10 bg-gray-700 text-gray-100 rounded-2xl "
    >
      <p class="text-lg font-bold text-center">Register</p>

      <div>
        <label for="Name" class="text-sm"> <span class="color-red">*</span>Name </label>
        <input v-model="name" type="text" class="auth-inputs" />
      </div>

      <div>
        <label for="Surname" class="text-sm"> <span class="color-red">*</span>Surname </label>
        <input v-model="surname" type="text" class="auth-inputs" />
      </div>

      <div>
        <label for="Email" class="text-sm"> <span class="color-red">*</span>Email </label>
        <input v-model="email" type="email" class="auth-inputs" />
      </div>

      <div>
        <label for="Password" class="text-sm"> <span class="color-red">*</span>Password </label>
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
            class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-100 flex items-center justify-center"
          >
            <i class="pi pi-eye"></i>
          </button>
        </div>
      </div>

      <div>
        <button
          type="submit"
          class="w-full bg-blue-800 text-gray-100 text-sm py-3 font-bold rounded-md cursor-pointer active:scale-95"
        >
          Register
        </button>
        <p class="text-xs text-center mt-2 text-gray-400">
          Already have an account?
          <RouterLink to="/login" class="font-bold cursor-pointer text-gray-100"
            >Log in</RouterLink
          >
        </p>

        <p v-if="message" class="text-center text-xs text-red-500 mt-4">
          {{ message }}
        </p>
      </div>
    </form>
  </div>
</template>

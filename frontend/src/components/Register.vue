<script setup>
  import { ref } from 'vue';
  const passwordInput = ref(null);

function passVisible() {
  if (passwordInput.value.type == "text") {
    passwordInput.value.type = "password";
  }else{
    passwordInput.value.type = "text";
  }
}
</script>

<template>
  <div class="h-full flex justify-center items-center font-mono">
    <form
      class="flex flex-col justify-between w-[80%] lg:w-1/4 h-[85%] p-10 bg-white"
    >
      <p class="text-lg font-bold text-center">Register</p>

      <div>
        <label for="Name"> <span class="color-red">*</span>Name </label>
        <input type="text" class="auth-inputs" />
      </div>

      <div>
        <label for="Surname"> <span class="color-red">*</span>Surname </label>
        <input type="text" class="auth-inputs" />
      </div>

      <div>
        <label for="Email"> <span class="color-red">*</span>Email </label>
        <input type="email" class="auth-inputs" />
      </div>

      <div>
        <label for="Password"> <span class="color-red">*</span>Password </label>
        <div class="relative">
          <input ref="passwordInput" type="password" class="auth-inputs" />
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
        <button class="bg-black text-white p-3 cursor-pointer w-full">
          Register
        </button>
        <p class="text-xs text-center mt-2 text-gray-800">
          Already have an account?
          <RouterLink to="/login" class="font-bold cursor-pointer"
            >Log in</RouterLink
          >
        </p>
      </div>
    </form>
  </div>
</template>

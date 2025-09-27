<template>
  <div class="h-full flex justify-center items-center font-mono">
    <form class="flex flex-col justify-between w-[80%] lg:w-1/4 h-[85%] p-10 bg-white">
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
        <input type="text" class="auth-inputs" />
      </div>

      <div>
        <label for="Password"> <span class="color-red">*</span>Password </label>
        <input type="text" class="auth-inputs" />
      </div>

      <div>
        <button class="bg-black text-white p-3 cursor-pointer w-full">
          Register
        </button>
        <p class="text-xs text-center mt-2 text-gray-800">
          Already have an account?
          <RouterLink to="/login" class="font-bold cursor-pointer">Log in</RouterLink>
        </p>
      </div>
    </form>
  </div>
</template>

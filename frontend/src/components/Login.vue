<template>
  <div class="h-full flex justify-center items-center font-mono">
    <form class="flex flex-col justify-between w-[80%] lg:w-1/4 h-[70%] p-10 bg-white">
      <p class="text-lg font-bold text-center">Login</p>

      <div>
        <label for="Email">Email </label>
        <input type="text" class="auth-inputs" />
      </div>

      <div>
        <label for="Password">Password </label>
        <input type="text" class="auth-inputs" />
      </div>

      <div>
        <button class="bg-black text-white p-3 cursor-pointer w-full">
          Log in
        </button>
        <p class="text-xs text-center mt-2 text-gray-800">
          Don't have an account?
          <RouterLink to="/register" class="font-bold cursor-pointer">Register</RouterLink>
        </p>
      </div>
    </form>
  </div>
</template>

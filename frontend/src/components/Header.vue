<template>
  <div
    class="h-[10%] flex justify-between items-center pl-[10%] pr-[10%] font-mono"
  >
    <RouterLink to="/" class="text-2xl cursor-pointer">Typo</RouterLink>

    <div class="flex gap-2">
      <RouterLink to="/login">
        <button class="border-1 p-1 pl-3 pr-3 cursor-pointer">Login</button>
      </RouterLink>

      <button
        class="border-1 p-1 pl-2 pr-2 cursor-pointer flex justify-center items-center"
      >
        <i class="pi pi-moon"></i>
      </button>
    </div>
  </div>
</template>
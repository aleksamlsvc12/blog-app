<template>
  <div
    class="w-full lg:w-1/2 h-full font-mono flex flex-col justify-center items-center"
  >
    <div
      class="w-[90%] lg:w-[70%] flex flex-col gap-5 lg:items-start items-center"
    >
      <p class="text-5xl font-bold text-center lg:text-left">
        Welcome to Typo!
      </p>
      <p class="text-center lg:text-left">
        Your personal blogging platform to share ideas, stories, and knowledge.
        <span class="block mt-2">Write. Read. Connect with the community.</span>
      </p>

      <RouterLink to="/register">
        <button
          class="self-start flex items-center border-1 p-3 cursor-pointer mt-5"
        >
          Get Started!
          <i class="pi pi-unlock ml-1"></i>
        </button>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>

</script>

<template>
  <div
    class="w-full h-full p-10 flex justify-center items-center font-mono"
  >
    <div
      class="flex w-full justify-center h-full"
    >
  

      <div
        class="overflow-y-auto w-full h-full border lg:p-10 p-5 flex flex-col"
      >
      <p class="font-bold mb-4 text-2xl lg:text-left text-center">Edit your profile</p>

        <div class="flex lg:flex-row flex-col gap-2 justify-between mb-2">
          <input type="text" class="border p-2 text-sm lg:w-[48%] w-full" placeholder="Change your name">
          <input type="text" class="border p-2 text-sm lg:w-[48%]" placeholder="Change your surname"></input>
        </div>
       
        
        <div class="flex lg:flex-row flex-col gap-2 justify-between mt-2 mb-2">
          <input type="text" class="border p-2 text-sm lg:w-[48%] w-full" placeholder="Change your email">
          <input type="text" class="border p-2 text-sm lg:w-[48%]" placeholder="Change your password"></input>
        </div>

        <div class="flex justify-between mt-2 mb-2">
          <input type="text" class="border p-2 text-sm w-full" placeholder="Enter your title">
        </div>

        <div>
          <textarea name="bio" id="bio" placeholder="Enter your biography" class="border p-2 text-sm w-full resize-none h-[200px]"></textarea>
        </div>


        <div class="w-full pt-2 flex lg:flex-row flex-col justify-between items-center">
          <input
            type="file"
            accept=".png, .jpg, .jpeg"
            disabled
            class="file:px-2 file:py-1 file:border border file:mr-2 file:text-sm cursor-not-allowed text-sm lg:w-auto bg-gray-200 w-full p-2"
          />

          <RouterLink to="/profile" class="lg:w-auto w-full">
            <button
              class="bg-black text-white p-2 cursor-pointer lg:w-auto w-full lg:m-0 mt-4"
            >
              Save Profile
            </button>
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

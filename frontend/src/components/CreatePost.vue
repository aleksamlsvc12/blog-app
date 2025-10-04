<template>
  <div class="w-full h-full p-8 flex justify-center items-center font-mono">
    <form
      class="bg-white p-8 lg:w-1/4 w-full h-full flex flex-col justify-around items-center"
    >
      <p class="text-lg font-bold">Create post</p>

      <div class="flex flex-col justify-center items-center gap-4 h-[95%] w-full">
        <div class="w-full">
          <label for="Title" class="text-sm">Title</label>
          <input type="text" class="auth-inputs" />
        </div>

        <div class="w-full">
          <label for="Description" class="text-sm block">Description</label>
          <textarea name="post-description" id="post-description" class="border w-full h-[200px] resize-none p-2 text-xs"></textarea>
        </div>

        <div class="w-full">
          <label for="Thumbnail" class="text-sm">Thumbnail</label>
          <input
            type="file"
            accept=".png, .jpg, .jpeg"
            class="file:py-1 file:px-3 file:border-1 file:cursor-pointer block text-xs border p-2 file:mr-2 w-full"
          />
          <p class="text-[9px] text-red-500 mt-1 ">&ast;Allowed file formats: .png, .jpg, .jpeg</p>
        </div>
      </div>
    </form>
  </div>
</template>

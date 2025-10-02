<script setup>
import { useAuthStore } from "../stores/auth";
const auth = useAuthStore();
</script>

<template>
  <div class="w-full h-full p-20 flex justify-center items-center">
    <div class="flex w-full justify-between h-[70%] gap-10">
      <div class="w-1/4 h-full border flex justify-center items-center p-5">
        <div class="w-full h-full bg-gray-400"></div>
      </div>
      <div class="w-[75%] h-full border p-10 flex flex-col justify-between">
        <div>
          <p class="text-4xl font-bold">Aleksa Milosevic</p>
          <p class="text-xl">Frontend developer</p>
        </div>

        <div>
          <p class="mt-2 text-sm">Since: 2025</p>
          <hr />
        </div>

        <p class="text-xs mt-2">
          Lorem ipsum dolor sit amet consectetur adipisicing elit. Vero magnam
          suscipit, esse iste natus architecto explicabo beatae cumque? Quae
          atque sint repudiandae non tenetur tempora ratione totam dicta ab?
          Quisquam? Lorem, ipsum dolor sit amet consectetur adipisicing elit. Ex
          magnam debitis voluptatibus dolore nam perspiciatis fugit tempora eum
          consequuntur delectus, autem quisquam eos molestiae odio soluta,
          veniam quod maiores quo? Lorem, ipsum dolor sit amet consectetur
          adipisicing elit. Dignissimos, placeat mollitia autem, necessitatibus
          consectetur commodi magni modi vitae voluptas veritatis architecto
          optio. Perspiciatis, at adipisci. Doloremque accusantium quo eum
          molestias.
        </p>
        <div class="w-full h-[20%] p-2 flex justify-end">
          <button class="border p-1 pl-2 pr-2 cursor-pointer">Edit Profile</button>
        </div>
      </div>
    </div>
  </div>
</template>

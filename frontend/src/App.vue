<script setup>
import Header from "./components/Header.vue";
import Main from "./components/Main.vue";
import Footer from "./components/Footer.vue";
import WelcomeSection from "./components/WelcomeSection.vue";
</script>

<template>
  <div class="flex flex-col h-screen w-full">
    <Header/>
    <Main>
      <WelcomeSection></WelcomeSection>
    </Main>
    <Footer/>
  </div>
</template>

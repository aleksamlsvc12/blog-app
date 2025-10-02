<script setup>
import { ref, onMounted } from "vue";
import axios from 'axios';

const categories = ref([]);

onMounted(async () => {
  const res = await axios.get('http://localhost:8000/api/categories.php');
  categories.value = res.data;
});

import Header from "../components/Header.vue";
import Main from "../components/Main.vue";
import Footer from "../components/Footer.vue";
import CategoriesSection from "../components/CategoriesSection.vue";
</script>

<template>
  <Header />
  <Main>
    <CategoriesSection :categories="categories"/>
  </Main>
  <Footer />
</template>

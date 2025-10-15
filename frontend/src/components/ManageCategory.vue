<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const categories = ref([]);
const loading = ref(true);
const error = ref(null);
const message = ref("");

const newName = ref("");
const newDesc = ref("");

const editing = ref(null);
const editName = ref("");
const editDesc = ref("");

onMounted(async () => {
  await fetchCategories();
});

const fetchCategories = async () => {
  try {
    const res = await axios.get(
      "http://localhost:8000/api/manageCategories.php",
      {
        withCredentials: true,
      }
    );
    if (res.data.ok) {
      categories.value = res.data.categories;
    } else {
      error.value = "Failed to fetch categories.";
    }
  } catch (err) {
    error.value = err.message || "Network error";
  } finally {
    loading.value = false;
  }
};

const createCategory = async () => {
  try {
    const res = await axios.post(
      "http://localhost:8000/api/manageCategories.php",
      {
        action: "create",
        name: newName.value,
        description: newDesc.value,
      },
      { withCredentials: true }
    );

    if (res.data.ok) {
      message.value = "Category created successfully!";
      newName.value = "";
      newDesc.value = "";
      fetchCategories();
      alert(message.value);
    } else {
      message.value = res.data.message;
    }
  } catch (err) {
    message.value = "Error: " + err.message;
  }
};

const deleteCategory = async (id) => {
  if (
    !confirm(
      "Are you sure you want to delete this category? That includes deleting all the posts for selected category."
    )
  ) {
    return;
  }

  try {
    const res = await axios.post(
      "http://localhost:8000/api/manageCategories.php",
      { action: "delete", id },
      { withCredentials: true }
    );

    if (res.data.ok) {
      await fetchCategories();
      alert("Category deleted successfully!");
    } else {
      alert(res.data.message);
    }
  } catch (err) {
    alert("Error: " + err.message);
  }
};

const startEdit = (cat) => {
  editing.value = cat.id;
  editName.value = cat.name;
  editDesc.value = cat.description;
};

const cancelEdit = () => {
  editing.value = null;
  editName.value = "";
  editDesc.value = "";
};

const saveEdit = async (id) => {
  try {
    const res = await axios.post(
      "http://localhost:8000/api/manageCategories.php",
      {
        action: "update",
        id,
        name: editName.value,
        description: editDesc.value,
      },
      { withCredentials: true }
    );

    if (res.data.ok) {
      editing.value = null;
      alert("Category updated successfully!");
      fetchCategories();
    } else {
      alert(res.data.message);
    }
  } catch (err) {
    alert("Error: " + err.message);
  }
};
</script>

<template>
  <div class="p-10 h-full font-mono overflow-y-auto">
    <h1 class="text-2xl font-bold mb-6 text-center text-white pb-2 border-b border-b-gray-600">
      Add New Category
    </h1>

    <div
      class="bg-gray-700 rounded-xl p-6 mb-10 w-full lg:w-1/2 mx-auto flex flex-col gap-4"
    >
      <input
        v-model="newName"
        type="text"
        placeholder="Category name"
        class="auth-inputs text-white"
      />
      <textarea
        v-model="newDesc"
        placeholder="Category description"
        class="auth-inputs text-white resize-none h-[200px]"
      ></textarea>

      <button
        @click="createCategory"
        class="bg-purple-700 text-white py-2 rounded font-bold active:scale-95 cursor-pointer text-sm"
      >
        Add Category
      </button>
    </div>

    <div v-if="loading" class="text-center text-gray-500">Loading...</div>
    <div v-else-if="error" class="text-red-500 text-center">{{ error }}</div>
    <div v-else-if="categories.length === 0" class="text-center text-gray-500">
      No categories found.
    </div>

    <h1
      v-else
      class="text-2xl font-bold text-white text-center mb-6 pb-2 border-b border-b-gray-600"
    >
      Existing Categories
    </h1>

    <div
      v-if="categories.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 justify-items-center"
    >
      <div
        v-for="cat in categories"
        :key="cat.id"
        class="bg-gray-700 rounded-xl p-5 w-full"
      >

        <template v-if="editing !== cat.id">
          <div class="text-lg font-bold text-white mb-4 flex justify-between">
            <span>{{ cat.name }}</span>
            <div class="flex gap-2">
              <button
                @click="startEdit(cat)"
                class="cursor-pointer border flex justify-center items-center p-2 rounded-lg active:scale-95"
                title="Edit"
              >
                <i class="pi pi-pencil text-sm"></i>
              </button>

              <button
                @click="deleteCategory(cat.id)"
                class="cursor-pointer border flex justify-center items-center p-2 rounded-lg active:scale-95"
                title="Delete"
              >
                <i class="pi pi-trash text-sm"></i>
              </button>
            </div>
          </div>

          <div class="text-gray-100 text-sm mb-2">{{ cat.description }}</div>
        </template>

        <template v-else>
          <input
            v-model="editName"
            class="auth-inputs mb-2 text-white"
            placeholder="Edit name"
          />
          <textarea
            v-model="editDesc"
            class="auth-inputs mb-2 text-white resize-none h-[100px]"
            placeholder="Edit description"
          ></textarea>

          <div class="flex justify-between">
            <button
              @click="saveEdit(cat.id)"
              class="bg-green-600 text-white px-3 py-1 rounded text-sm font-bold active:scale-95 cursor-pointer"
            >
              Save
            </button>
            <button
              @click="cancelEdit"
              class="bg-gray-300 text-black px-3 py-1 rounded text-sm font-bold active:scale-95 cursor-pointer"
            >
              Cancel
            </button>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

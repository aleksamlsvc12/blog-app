<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";
import { useAuthStore } from "../stores/auth";

const route = useRoute();
const auth = useAuthStore();

const posts = ref([]);
const loading = ref(true);
const hasNoContent = ref(false);

const showComments = ref({});
const newComment = ref({});
const comments = ref({});
const editingComment = ref({});
const editedContent = ref({});

onMounted(async () => {
  const categoryName = route.params.name?.trim();
  if (!categoryName) {
    hasNoContent.value = true;
    loading.value = false;
    return;
  }

  try {
    const res = await axios.get("http://localhost:8000/api/specificCategory.php", {
      params: {
        category: categoryName,
        user_id: auth.user.id,
      },
    });

    if (res.data.status === "success") {
      posts.value = res.data.posts.map((p) => ({
        ...p,
        likes: p.likes || 0,
        dislikes: p.dislikes || 0,
        user_reaction: p.user_reaction,
        comments: 0,
      }));

      await Promise.all(
        posts.value.map(async (post) => {
          const commentsRes = await axios.get("http://localhost:8000/api/comments.php", {
            params: { fk_post: post.id },
          });

          if (commentsRes.data.status === "success") {
            comments.value[post.id] = commentsRes.data.comments;
            post.comments = commentsRes.data.count;
          } else {
            comments.value[post.id] = [];
          }
        })
      );

      hasNoContent.value = posts.value.length === 0;
    } else {
      hasNoContent.value = true;
    }
  } catch (err) {
    console.error("Greška pri učitavanju:", err);
    hasNoContent.value = true;
  } finally {
    loading.value = false;
  }
});

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString("en-US", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const reactToPost = async (postId, type, index) => {
  try {
    const res = await axios.post("http://localhost:8000/api/reactions.php", {
      fk_post: postId,
      fk_user: auth.user.id,
      type: type,
    });

    if (res.data.status === "success") {
      posts.value[index].likes = res.data.likes;
      posts.value[index].dislikes = res.data.dislikes;
      posts.value[index].user_reaction =
        posts.value[index].user_reaction === type ? null : type;
    }
  } catch (err) {
    console.error(err);
  }
};

const toggleCommentInput = (postId) => {
  showComments.value[postId] = !showComments.value[postId];
};

const submitComment = async (postId) => {
  if (!newComment.value[postId] || newComment.value[postId].trim() === "") return;

  try {
    const res = await axios.post("http://localhost:8000/api/comments.php", {
      fk_user: auth.user.id,
      fk_post: postId,
      content: newComment.value[postId],
    });

    if (res.data.status === "success") {
      const refresh = await axios.get("http://localhost:8000/api/comments.php", {
        params: { fk_post: postId },
      });

      if (refresh.data.status === "success") {
        comments.value[postId] = refresh.data.comments;
        posts.value.find((p) => p.id === postId).comments = refresh.data.count;
      }
      
      newComment.value[postId] = "";
    }
  } catch (err) {
    console.error("Greška pri dodavanju komentara:", err);
  }
};


const startEditing = (comment) => {
  editingComment.value[comment.id] = true;
  editedContent.value[comment.id] = comment.content;
};

const saveEdit = async (comment, postId) => {
  try {
    const res = await axios.put("http://localhost:8000/api/comments.php", {
      id: comment.id,
      fk_user: auth.user.id,
      content: editedContent.value[comment.id],
    });

    if (res.data.status === "success") {
      const refreshed = await axios.get("http://localhost:8000/api/comments.php", {
        params: { fk_post: postId },
      });
      comments.value[postId] = refreshed.data.comments;
      editingComment.value[comment.id] = false;
    }
  } catch (err) {
    console.error("Greška pri izmeni komentara:", err);
  }
};

const cancelEdit = (commentId) => {
  editingComment.value[commentId] = false;
  editedContent.value[commentId] = "";
};

const deleteComment = async (commentId, postId) => {
  if (!confirm("Are you sure you want to delete this comment?")) return;

  try {
    const res = await axios.delete("http://localhost:8000/api/comments.php", {
      data: { id: commentId, fk_user: auth.user.id },
    });

    if (res.data.status === "success") {
      const refreshed = await axios.get("http://localhost:8000/api/comments.php", {
        params: { fk_post: postId },
      });
      comments.value[postId] = refreshed.data.comments;
      posts.value.find((p) => p.id === postId).comments = refreshed.data.count;
    }
  } catch (err) {
    console.error("Greška pri brisanju komentara:", err);
  }
};
</script>


<template>
  <div class="p-10 font-mono h-full overflow-y-auto">
    <div v-if="loading" class="text-gray-500">Loading posts...</div>

    <div v-else-if="hasNoContent" class="flex flex-col gap-4 justify-center items-center h-full w-full">
      <h1 class="text-2xl font-bold mb-2">Oops! Looks like there are no blogs on this topic.</h1>

      <RouterLink to="/post">
        <button class="bg-black text-white p-3 cursor-pointer w-full">Create one now!</button>
      </RouterLink>
    </div>

    <div v-else>
      <h1 class="text-2xl font-bold mb-4">{{ route.params.name.toUpperCase() }}:</h1>

      <div v-for="(post, index) in posts" :key="post.id" class="border p-8 mb-10 bg-white">
        <div class="border p-4">
          <div class="flex justify-between items-center lg:flex-row flex-col-reverse gap-2">
            <span class="text-lg font-bold">{{ post.title }}</span>
            <span class="text-xs text-gray-700">{{ formatDate(post.created_at) }}</span>
          </div>

          <p class="text-sm text-gray-700 mt-5 text-justify">{{ post.content }}</p>

          <RouterLink :to="{ name: 'OtherUserProfile', params: { id: post.fk_user } }">
            <span class="text-xs font-bold cursor-pointer flex w-full justify-end p-2 pr-0">
              {{ post.name }} {{ post.surname }}
            </span>
          </RouterLink>
        </div>

        <div class="w-full mt-2 text-sm flex">

          <div class="relative">
            <button class="p-2 border mr-2 cursor-pointer" :class="{ 'bg-green-300': post.user_reaction === 1 }" @click="reactToPost(post.id, 1, index)">
              <i class="pi pi-thumbs-up"></i>
            </button>
            <div v-if="post.likes > 0" class="w-[20px] h-[20px] absolute bg-green-600 -top-2 right-1 rounded-full flex justify-center items-center">
              <span class="text-[10px] text-white font-bold">{{ post.likes }}</span>
            </div>
          </div>

          <div class="relative">
            <button class="p-2 border mr-2 cursor-pointer" :class="{ 'bg-red-300': post.user_reaction === 0 }" @click="reactToPost(post.id, 0, index)">
              <i class="pi pi-thumbs-down"></i>
            </button>
            <div v-if="post.dislikes > 0" class="w-[20px] h-[20px] absolute bg-red-600 -top-2 right-1 rounded-full flex justify-center items-center">
              <span class="text-[10px] text-white font-bold">{{ post.dislikes }}</span>
            </div>
          </div>

          <div class="relative">
            <button class="p-2 border mr-2 cursor-pointer" @click="toggleCommentInput(post.id)">
              <i class="pi pi-comment"></i>
            </button>
            <div v-if="post.comments > 0" class="w-[20px] h-[20px] absolute bg-blue-600 -top-2 right-1 rounded-full flex justify-center items-center">
              <span class="text-[10px] text-white font-bold">{{ post.comments }}</span>
            </div>
          </div>
        </div>

        <div class="mt-4 border-t pt-4">
          <div v-for="c in comments[post.id]" :key="c.id" class="mb-2 flex justify-between items-start">
            <div class="flex-1">
              <p class="text-xs text-gray-700">
                <strong>{{ c.name }} {{ c.surname }}:</strong>
              </p>

              <div v-if="editingComment[c.id]">
                <input v-model="editedContent[c.id]" class="border p-1 text-xs w-full mt-1" />
                <div class="mt-1 flex gap-2">
                  <button @click="saveEdit(c, post.id)" class="text-xs bg-green-500 text-white px-2 py-1">Save</button>
                  <button @click="cancelEdit(c.id)" class="text-xs bg-gray-300 px-2 py-1">Cancel</button>
                </div>
              </div>
              <div v-else>
                <p class="text-sm">{{ c.content }}</p>
              </div>

              <p class="text-[10px] text-gray-500">{{ formatDate(c.created_at) }}</p>
            </div>

            <div v-if="c.fk_user === auth.user.id" class="flex gap-2 text-xs ml-2">
              <button @click="startEditing(c)" class="text-blue-600 hover:underline">Edit</button>
              <button @click="deleteComment(c.id, post.id)" class="text-red-600 hover:underline">Delete</button>
            </div>
          </div>

          <div v-if="showComments[post.id]" class="mt-3 flex">
            <input v-model="newComment[post.id]" type="text" placeholder="Write a comment..." class="border p-2 w-full text-sm" />
            <button @click="submitComment(post.id)" class="bg-blue-600 text-white p-2 ml-2 text-sm cursor-pointer">
              Post
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


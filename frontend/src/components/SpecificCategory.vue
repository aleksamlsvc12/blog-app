<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import { useAuthStore } from "../stores/auth";

const router = useRouter();
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
    // Request posts for the selected category
    const res = await axios.get(
      "http://localhost:8000/api/specificCategory.php",
      {
        params: {
          category: categoryName,
          user_id: auth.user.id,
        },
      }
    );

    // If data is successfully received, initialize posts with default values
    if (res.data.status === "success") {
      posts.value = res.data.posts.map((p) => ({
        ...p,
        likes: p.likes || 0,
        dislikes: p.dislikes || 0,
        user_reaction: p.user_reaction,
        comments: 0,
      }));

      // Fetch comments for each post in parallel
      await Promise.all(
        posts.value.map(async (post) => {
          const commentsRes = await axios.get(
            "http://localhost:8000/api/comments.php",
            {
              params: { fk_post: post.id },
            }
          );

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
    console.error(err);
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
    const currentReaction = posts.value[index].user_reaction;

    // Remove reaction if the same type is clicked again
    if (currentReaction === type) {
      const res = await axios.post("http://localhost:8000/api/reactions.php", {
        fk_post: postId,
        fk_user: auth.user.id,
        type: "remove",
      });

      if (res.data.status === "success") {
        posts.value[index].likes = res.data.likes;
        posts.value[index].dislikes = res.data.dislikes;
        posts.value[index].user_reaction = null;
      }
      return;
    }

    // Add or switch reaction
    const res = await axios.post("http://localhost:8000/api/reactions.php", {
      fk_post: postId,
      fk_user: auth.user.id,
      type: type,
    });

    if (res.data.status === "success") {
      posts.value[index].likes = res.data.likes;
      posts.value[index].dislikes = res.data.dislikes;
      posts.value[index].user_reaction = type;
    }
  } catch (err) {
    console.error(err);
  }
};

// Toggles visibility of comment section for each post
const toggleCommentInput = (postId) => {
  showComments.value[postId] = !showComments.value[postId];
};

// Sends a new comment and updates the comment list
const submitComment = async (postId) => {
  if (!newComment.value[postId] || newComment.value[postId].trim() === "")
    return;

  try {
    const res = await axios.post("http://localhost:8000/api/comments.php", {
      fk_user: auth.user.id,
      fk_post: postId,
      content: newComment.value[postId],
    });

    // Refresh comment list after successful submission
    if (res.data.status === "success") {
      const refresh = await axios.get(
        "http://localhost:8000/api/comments.php",
        {
          params: { fk_post: postId },
        }
      );

      if (refresh.data.status === "success") {
        comments.value[postId] = refresh.data.comments;
        posts.value.find((p) => p.id === postId).comments = refresh.data.count;
      }

      newComment.value[postId] = "";
    }
  } catch (err) {
    console.error(err);
  }
};

// Starts editing a comment by storing its original content
const startEditing = (comment) => {
  editingComment.value[comment.id] = true;
  editedContent.value[comment.id] = comment.content;
};

// Saves edited comment and refreshes the list
const saveEdit = async (comment, postId) => {
  try {
    const res = await axios.put("http://localhost:8000/api/comments.php", {
      id: comment.id,
      fk_user: auth.user.id,
      content: editedContent.value[comment.id],
    });

    if (res.data.status === "success") {
      const refreshed = await axios.get(
        "http://localhost:8000/api/comments.php",
        {
          params: { fk_post: postId },
        }
      );
      comments.value[postId] = refreshed.data.comments;
      editingComment.value[comment.id] = false;
    }
  } catch (err) {
    console.error(err);
  }
};

// Cancels edit mode and resets the input
const cancelEdit = (commentId) => {
  editingComment.value[commentId] = false;
  editedContent.value[commentId] = "";
};

// Deletes a comment after confirmation and refreshes the list
const deleteComment = async (commentId, postId) => {
  if (!confirm("Are you sure you want to delete this comment?")) return;

  try {
    const res = await axios.delete("http://localhost:8000/api/comments.php", {
      data: { id: commentId, fk_user: auth.user.id },
    });

    if (res.data.status === "success") {
      const refreshed = await axios.get(
        "http://localhost:8000/api/comments.php",
        {
          params: { fk_post: postId },
        }
      );
      comments.value[postId] = refreshed.data.comments;
      posts.value.find((p) => p.id === postId).comments = refreshed.data.count;
    }
  } catch (err) {
    console.error(err);
  }
};

const deletePost = async (postId) => {
  if (!confirm("Are you sure you want to delete this post?")) return;

  try {
    const res = await axios.delete("http://localhost:8000/api/editPost.php", {
      data: {
        action: "delete",
        post_id: postId,
        fk_user: auth.user.id,
      },
    });

    if (res.data.status === "success") {
      posts.value = posts.value.filter((p) => p.id !== postId);
      alert("Post deleted successfully.");
      router.go(0);
    } else {
      alert(res.data.message || "Failed to delete post.");
    }
  } catch (err) {
    console.error(err);
    alert("Error deleting post.");
  }
};
</script>

<template>
  <div class="p-10 font-mono h-full overflow-y-auto">
    <!-- Loading state -->
    <div v-if="loading" class="text-gray-500">Loading posts...</div>

    <div
      v-else-if="hasNoContent"
      class="flex flex-col gap-4 justify-center items-center h-full w-full"
    >
      <h1 class="text-2xl text-white text-center font-bold mb-2">
        Oops! Looks like there are no blogs on this topic.
      </h1>

      <RouterLink to="/post">
        <button
          class="bg-purple-700 text-sm text-gray-100 p-3 cursor-pointer w-full rounded-md font-bold active:scale-95 transition-all duration-300 hover:shadow-[0_0_15px_rgba(168,85,247,0.6)]"
        >
          Create one now!
        </button>
      </RouterLink>
    </div>

    <!-- Main section showing posts and their comments -->
    <div v-else>
      <h1
        class="text-2xl font-bold text-white mb-4 pb-2 border-b border-b-gray-600"
      >
        {{ route.params.name.toUpperCase() }}:
      </h1>

      <div
        v-for="(post, index) in posts"
        :key="post.id"
        class="rounded-2xl p-8 pb-4 mb-10 bg-gray-700"
      >
        <!-- Post header -->
        <div class="bg-gray-900 rounded-xl p-4">
          <div
            class="flex justify-between items-center lg:flex-row flex-col-reverse gap-2"
          >
            <span class="text-lg text-white font-bold">{{ post.title }}</span>
            <span class="text-xs text-gray-400">{{
              formatDate(post.created_at)
            }}</span>
          </div>

          <!-- Post content -->
          <p class="text-sm text-gray-100 mt-4 text-justify">
            {{ post.content }}
          </p>

          <!-- Author info and profile link -->
          <div class="flex justify-between pt-2">
            <div v-if="auth.user.fk_user_type === 2">
              <button
                @click="deletePost(post.id)"
                class="bg-red-800 text-white text-xs font-bold px-3 py-1 rounded-md cursor-pointer active:scale-95"
              >
                Delete
              </button>
            </div>

            <RouterLink
              :to="{ name: 'OtherUserProfile', params: { id: post.fk_user } }"
              class="text-xs text-gray-100 font-bold cursor-pointer flex items-center gap-2"
            >
              <div
                class="w-[30px] h-[30px] rounded-[15px] overflow-hidden bg-gray-400 flex justify-center items-center"
              >
                <img
                  v-if="post.profile_img"
                  :src="`http://localhost:8000/${post.profile_img}`"
                  alt="User image"
                  class="object-cover w-full h-full"
                />
                <i v-else class="pi pi-user text-black"></i>
              </div>
              <span> {{ post.name }} {{ post.surname }} </span>
            </RouterLink>
          </div>
        </div>

        <!-- Reactions -->
        <div class="w-full mt-2 text-sm flex">
          <!-- Like button -->
          <div class="relative">
            <button
              class="p-2 rounded-lg mr-2 cursor-pointer text-white flex justify-center items-center bg-gray-600 active:scale-95"
              :class="{ 'bg-green-500': post.user_reaction === 1 }"
              @click="reactToPost(post.id, 1, index)"
            >
              <i class="pi pi-thumbs-up"></i>
            </button>
            <div
              v-if="post.likes > 0"
              class="w-[15px] h-[15px] absolute bg-green-700 -top-2 right-1 rounded-full flex justify-center items-center"
            >
              <span class="text-[10px] text-white font-bold">{{
                post.likes
              }}</span>
            </div>
          </div>

          <!-- Dislike button -->
          <div class="relative">
            <button
              class="p-2 rounded-lg mr-2 cursor-pointer text-white flex justify-center items-center bg-gray-600 active:scale-95"
              :class="{ 'bg-red-500': post.user_reaction === 0 }"
              @click="reactToPost(post.id, 0, index)"
            >
              <i class="pi pi-thumbs-down"></i>
            </button>
            <div
              v-if="post.dislikes > 0"
              class="w-[15px] h-[15px] absolute bg-red-700 -top-2 right-1 rounded-full flex justify-center items-center"
            >
              <span class="text-[10px] text-white font-bold">{{
                post.dislikes
              }}</span>
            </div>
          </div>

          <!-- Comment toggle button -->
          <div class="relative">
            <button
              class="p-2 rounded-lg mr-2 cursor-pointer text-white flex justify-center items-center bg-gray-600 active:scale-95"
              @click="toggleCommentInput(post.id)"
            >
              <i class="pi pi-comment"></i>
            </button>
            <div
              v-if="post.comments > 0"
              class="w-[15px] h-[15px] absolute bg-blue-700 -top-2 right-1 rounded-md flex justify-center items-center"
            >
              <span class="text-[10px] text-white font-bold">{{
                post.comments
              }}</span>
            </div>
          </div>
        </div>

        <!-- Comment section -->
        <div v-if="showComments[post.id]">
          <!-- Display comments -->
          <div
            v-for="c in comments[post.id]"
            :key="c.id"
            class="mb-4 pb-5 bg-gray-600 rounded-2xl p-4 break-words mt-4"
          >
            <!-- Comment header  -->
            <div
              class="flex flex-col sm:flex-row sm:items-center sm:justify-between"
            >
              <RouterLink
                :to="{ name: 'OtherUserProfile', params: { id: c.fk_user } }"
                class="text-sm font-bold cursor-pointer"
              >
                <div class="flex gap-2 items-center">
                  <div
                    class="w-[30px] h-[30px] rounded-[15px] bg-gray-400 flex justify-center items-center"
                  >
                    <i class="pi pi-user"></i>
                  </div>
                  <span class="text-white">{{ c.name }} {{ c.surname }}</span>
                </div>
              </RouterLink>
              <p class="text-[10px] text-gray-400 mt-1 sm:mt-0">
                {{ formatDate(c.created_at) }}
              </p>
            </div>

            <!-- Comment content -->
            <div
              v-if="!editingComment[c.id]"
              class="text-sm text-gray-100 whitespace-pre-wrap break-words mt-3 leading-snug"
            >
              {{ c.content }}
            </div>

            <!-- Edit mode -->
            <div v-else class="mt-3">
              <div class="flex gap-4">
                <input
                  v-model="editedContent[c.id]"
                  class="auth-inputs text-gray-100"
                />
                <div class="flex gap-2">
                  <button
                    @click="saveEdit(c, post.id)"
                    class="text-xs bg-green-500 font-bold rounded-md text-white p-2 cursor-pointer active:scale-95"
                  >
                    Save
                  </button>
                  <button
                    @click="cancelEdit(c.id)"
                    class="text-xs bg-gray-300 font-bold rounded-md px-3 py-1 cursor-pointer active:scale-95"
                  >
                    Cancel
                  </button>
                </div>
              </div>
            </div>

            <!-- Edit/Delete buttons  -->
            <div
              v-if="c.fk_user === auth.user.id"
              class="flex flex-wrap gap-3 mt-3"
            >
              <button
                @click="startEditing(c)"
                class="text-white bg-blue-500 rounded-md flex justify-center items-center p-2 cursor-pointer active:scale-95"
              >
                <i class="pi pi-pencil"></i>
              </button>
              <button
                @click="deleteComment(c.id, post.id)"
                class="text-white bg-red-500 flex rounded-md justify-center items-center p-2 cursor-pointer active:scale-95"
              >
                <i class="pi pi-trash"></i>
              </button>
            </div>
          </div>

          <!-- Add new comment input -->
          <div class="mt-4 flex flex-row items-center gap-2">
            <input
              v-model="newComment[post.id]"
              type="text"
              placeholder="Write a comment..."
              class="auth-inputs text-gray-100"
            />
            <button
              @click="submitComment(post.id)"
              class="bg-blue-600 font-bold text-white px-4 py-[6px] text-sm cursor-pointer rounded-md active:scale-95"
            >
              Post
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

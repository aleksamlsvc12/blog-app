import { createRouter, createWebHistory } from "vue-router";

import LandingPageView from "../views/LandingPageView.vue";
import RegisterPageView from "../views/RegisterPageView.vue";
import LoginPageView from "../views/LoginPageView.vue";
import CategoriesView from "../views/CategoriesView.vue";
import UserProfileView from "../views/UserProfileView.vue";
import CreatePostView from "../views/CreatePostView.vue";
import SpecificCategoryView from "../views/SpecificCategoryView.vue";
import EditProfileView from "../views/EditProfileView.vue";
import OtherUserProfileView from "../views/OtherUserProfileView.vue";
import UserPostsView from "../views/UserPostsView.vue";

const routes = [
  { path: "/", name: "LandingPage", component: LandingPageView },
  { path: "/login", name: "Login", component: LoginPageView },
  { path: "/register", name: "Register", component: RegisterPageView },
  { path: "/categories", name: "Categories", component: CategoriesView },
  { path: "/profile", name: "UserProfile", component: UserProfileView },
  { path: "/post", name: "CreatePost", component: CreatePostView },
  { path: "/category/:name", name: "Category", component: SpecificCategoryView },
  { path: "/edit", name: 'EditProfile', component: EditProfileView },
  { path: "/profile/:id", name: 'OtherUserProfile', component: OtherUserProfileView },
  { path: "/profile/:id/posts", name: 'UserPosts', component: UserPostsView },
  { path: "/profile/posts", name: "MyPosts", component: UserPostsView },
];

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router;
<template>
    <div class="container">
        <section class="bg-white dark:bg-gray-900 py-8 lg:py-16">
            <div class="max-w-2xl mx-auto px-4">
                <div class="flex justify-between items-center mb-6">
                    <h2
                        class="text-lg lg:text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        Discussion ({{ commentsAmount }})
                    </h2>
                </div>
                <FormComponent
                    @preview="commentPreview"
                    @commented="fetch()" />
                <div class="text-right my-3">
                    <button @click.prevent="sort(name)" type="button" class="mx-1 px-3 py-2 text-xs font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        {{ name.label }} 
                        <i v-if="name.order_by === 'asc'" class="fa-solid fa-arrow-down-z-a"></i>
                        <i v-else class="fa-solid fa-arrow-up-a-z"></i> </button>
                    <button @click.prevent="sort(email)" type="button" class="mx-1 px-3 py-2 text-xs font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        {{ email.label }} 
                        <i v-if="email.order_by === 'asc'" class="fa-solid fa-arrow-down-z-a"></i>
                        <i v-else class="fa-solid fa-arrow-up-a-z"></i></button>
                    <button @click.prevent="sort(date)" type="button" class="mx-1 px-3 py-2 text-xs font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        {{ date.label }} 
                        <i v-if="date.order_by === 'asc'" class="fa-solid fa-arrow-down-z-a"></i>
                        <i v-else class="fa-solid fa-arrow-up-a-z"></i></button>
                </div>
                <CommentComponent
                    @preview="commentPreview"
                    @commented="fetch()"
                    v-for="comment in this.comments"
                    :key="comment.id"
                    :comment="comment"
                />
            </div>
        </section>
        <div class="flex justify-center mt-4">
              <button
                class="px-3 py-2 rounded-lg text-gray-600 hover:text-gray-800"
                :disabled="currentPage === 1"
                @click="currentPage--"
              >
                  Previous
              </button>
          <template v-for="page in totalPages" :key="page" >
              <button
                class="mx-2 px-3 py-2 rounded-lg text-gray-600 hover:text-gray-800"
                :class="{ 'bg-gray-200': currentPage === page }"
                @click="currentPage = page"
              >
                {{ page }}
              </button>
          </template>
              <button
                class="px-3 py-2 rounded-lg text-gray-600 hover:text-gray-800"
                :disabled="currentPage === totalPages"
                @click="currentPage++"
              >
                Next
              </button> 
        </div>
    </div>
</template>

<script>
import { fetchComments, clearCache } from "../api.js";
import CommentComponent from "./CommentComponent.vue";
import FormComponent from "./FormComponent.vue";

export default {
    data() {
        return {
            currentPage: 1,
            totalPages: 1,
            commentsAmount: 1,
            comments: [],
            name: {
                order_by: "desc",
                sort_by: "name",
                label: "Username",
            },
            email: {
                order_by: "desc",
                sort_by: "email",
                label: "Email",
            },
            date: {
                order_by: "asc",
                sort_by: "created_at",
                label: "Date",
            },
        };
    },
    components: {
        CommentComponent,
        FormComponent,
    },
    created() {
        window.Echo.channel('new-comment').listen('NewRecordCreated', async (data) => {
            if (data.message === "new_comment_posted") {
                await clearCache('/api/clearCache');
                await this.fetch();
            }
        });
    },
    mounted() {
        this.fetch();
    },
    methods: {
        async fetch(sort_by, order_by) {
            let url = `/api/comments?page=${this.currentPage}`;

            if (sort_by != null || order_by != null) {
                url += `&sort_by=${sort_by}&order_by=${order_by}`;
            }

            await fetchComments(url)
                .then((response) => {
                    this.comments = response.comments;
                    this.totalPages = response.totalPages;
                    this.commentsAmount = response.commentsAmount.length;
                })
                .catch((err) => {
                    console.log(err);
                });
        },
        sort(field) {
            this.fetch(field.sort_by, field.order_by);
            field.order_by = field.order_by === "asc" ? "desc" : "asc";
        },
        async commentPreview(comment) {
            await this.fetch();
            this.comments.unshift(comment);
        }
    },
    watch: {
        currentPage() {
            this.fetch();
        }
    }
};
</script>

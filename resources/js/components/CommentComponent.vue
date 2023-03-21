<template>
    <div>
  <article class="p-3 text-base bg-white rounded-lg dark:bg-gray-900 border border-gray-200 mb-4">
    <footer class="flex justify-between items-center mb-2">
            <div class="flex justify-between w-100">
                <div>
                    <p class="inline-flex items-center mr-3 text-sm text-gray-900 dark:text-white">
                        <img
                            class="mr-2 w-6 h-6 rounded-full"
                            src="/images/person.jpg"
                            alt="Michael Gough">

                            {{ comment.name }}

                            <a class="text-blue-600 ms-3" :href="`mailto:${comment.email}`">
                                {{comment.email}}
                            </a>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 text-right"><time pubdate>{{ new Date(comment.created_at).toLocaleString('en-US', this.options) }}</time></p>
                </div>
            </div>
        </footer>
        <p v-html="comment.text" class="text-gray-500 dark:text-gray-400 max-w break-words"></p>
        <div v-if="comment.files.length > 0">
            <hr class="border-gray-500 border-t-2 my-4">
            <div class="flex flex-row flex-wrap gap-4">
                <div v-for="(file, index) in txtFiles" :key="index">
                    <a v-if="file.type == 'txt'"
                    :href="file.file_path" 
                    class="cursor-pointer text-blue-600"
                    target="_blank">{{ file.namePreview }}</a>
                    <a v-if="!file.type"
                    :href="file.file_path" 
                    class="cursor-pointer text-blue-600"
                    target="_blank">{{ file.name }}</a>
                </div>
            </div> 
            <div class="flex flex-row flex-wrap gap-4">
                <div v-for="(image, index) in imageFiles" :key="index">
                    <img v-if="image.type == 'image'"
                    @click.prevent="preview(index)" 
                    :src="image.file_path" 
                    class="cursor-pointer border-3 border border-gray-600 rounded w-24 h-24"
                    alt="image" />
                    <img v-if="!image.type"
                    @click.prevent="preview(index)" 
                    :src="image.file_path" 
                    class="cursor-pointer border-3 border border-gray-600 rounded w-24 h-24"
                    alt="image" />
                </div>
            </div>          
        </div>
        <div class="flex items-center mt-4 space-x-4">
            <button 
                @click.prevent="replyComment(); $event.stopPropagation();"
                type="button"
                class="flex items-center text-sm text-gray-500 hover:underline dark:text-gray-400"
                :disabled="!comment.id">
                <svg aria-hidden="true" class="mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                Reply 
            </button>
        </div>

        <div 
            v-show="reply"
            ref="formDiv">
             <FormComponent 
                v-if="reply"
                @preview="commentPreview"
                @commented="this.fetchReplies(); reply = false;"
                :replyId="comment.id"/>
        </div>

        <button 
            @click="show = !show"
            v-if="this.replies.length > 0"
            class="text-center w-100">
             {{ show ? 'Close' : 'Show' }} replys ({{ this.replies.length }})
        </button>
        <div :class="{ 'hidden' : !show }">
                <CommentComponent 
                    @preview="commentPreview"
                    v-for="comment in this.replies" 
                    :key="comment.id" 
                    :comment="comment" />
            <div v-if="this.replies.length > 0">
                <button 
                    v-if="this.page < this.totalPages"
                    @click="this.fetchReplies(); this.page++"
                    class="text-center w-100">
                     Load more...
                </button>
            </div>
        </div>
    </article>
    <vue-easy-lightbox
        :visible="visibleRef"
        :imgs="images"
        :index="indexRef"
        @hide="onHide"
      ></vue-easy-lightbox>
</div>
</template>

<script>
import { fetchReplyComments } from "../api.js";

import FormComponent from "./FormComponent.vue";
import VueEasyLightbox from 'vue-easy-lightbox'

export default {
    components: {
        FormComponent,
        VueEasyLightbox,
    },
    props: {
        comment: Object
    },
    data() {
      return {
        options: { 
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            second: 'numeric'
            },
        replies: [],
        show: true,
        reply: false,
        images: [],
        toggler: false,
        slide: 1,
        visibleRef: false,
        indexRef: 0,
        page: 1,
        totalPages: 1,
      };
    },
    computed: {
        imageFiles() {
            let imageFiles = this.comment.files.filter(file => !file.file_path.endsWith('.txt'));
            imageFiles.map(file => {
                if (!file.file_path.startsWith('blob:')) {
                    file.file_path = '/uploads/' + file.file_path;
                }
            });

            this.images = [];

            for (const image of imageFiles) {
                this.images.push(image.file_path);
            }
            
            return imageFiles;
        },
        txtFiles() {
            let txtFiles = this.comment.files.filter(file => file.file_path.endsWith('.txt') || (file.file_path.startsWith('blob') && file.type === 'txt'));

            txtFiles.map(file => {
                if (!file.file_path.startsWith('blob:')) {
                    file.name = file.file_path
                    file.file_path = '/uploads/' + file.file_path;
                }
            });

            return txtFiles;
        }
    },
    mounted() {
        document.addEventListener('click', this.handleClickOutside);
        this.fetchReplies();
    },
    methods: {
        async fetchReplies() {
            let url = `/api/replyComments?page=${this.page}&id=${this.comment.id}`;
            
            await fetchReplyComments(url)
                .then((response) => {
                    let responseJson = JSON.parse(response)

                    let comments = responseJson.replyComments.data
                    this.totalPages = responseJson.totalPages;

                    for (let comment of comments) {
                        this.replies.push(comment);
                    }
                })
                .catch((err) => {
                    console.log(err);
                });
        },
        replyComment() {
            this.reply = true;
        },
        handleClickOutside(event) {
            if (this.$refs.formDiv) {
                if (!this.$refs.formDiv.contains(event.target)) {
                    this.reply = false;
                }
            }
        },
        async commentPreview(comment) {
            for (let i = 0; i < this.replies.length; i++) {
                if (!this.replies[i].id) {
                    this.replies.splice(i, 1);
                }
            }
            this.replies.unshift(comment);
        },
        preview(index) {
            this.indexRef = index;
            this.visibleRef = true
        },
        onHide() {
            this.visibleRef = false
        }
    }
};
</script>

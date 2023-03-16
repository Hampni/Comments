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
                    <p class="text-xs text-gray-600 dark:text-gray-400 text-right"><time pubdate>{{ comment.created_at }}</time></p>
                </div>
            </div>
        </footer>
        <p v-html="comment.text" class="text-gray-500 dark:text-gray-400 max-w break-words"></p>
        <div class="flex items-center mt-4 space-x-4">
            <button 
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
                :replyId="comment.id"/>
        </div>

        <button 
            @click="show = !show"
            v-if="this.comment.replies.length > 0"
            class="text-center w-100">
             {{ show ? 'Close' : 'Show' }} replys ({{ this.comment.replies.length }})
        </button>
        <div class="" :class="{ 'hidden' : !show }">
                <CommentComponent 
                    v-for="comment in this.comment.replies" 
                    :key="comment.id" 
                    :comment="comment" />
        </div>
    </article>
</div>
</template>

<script>
import FormComponent from "./FormComponent.vue";


export default {
    components: {
        FormComponent,
    },
    props: {
        comment: Object
    },
    data() {
      return {
        show: true,
        reply: false,
        comments: [],
      };
    },
    computed: {
    mounted() {
        document.addEventListener('click', this.handleClickOutside);
    },
    methods: {
        replyComment() {
            this.reply = true;
        },
    },
    }
};
</script>

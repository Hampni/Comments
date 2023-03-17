<template>
    <div>
        <form
            @submit.prevent="post()"
            :id="'commentForm-' + replyId"
            class="mb-6"
        >
            <div class="mb-4">
                <label
                    class="block text-gray-700 text-sm font-bold mb-2"
                    for="username"
                >
                    Username
                </label>
                <i
                    class="error mt-2 text-sm text-red-600"
                    id="username-error"
                ></i>
                <input
                    class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="username"
                    name="username"
                    type="text"
                    placeholder="Your username"
                    v-model="username"
                    required
                />
            </div>
            <div class="mb-4">
                <label
                    class="block text-gray-700 text-sm font-bold mb-2"
                    for="email"
                >
                    Email
                </label>
                <i class="error mt-2 text-sm text-red-600" id="email-error"></i>
                <input
                    class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="email"
                    name="email"
                    type="text"
                    placeholder="Your email"
                    v-model="email"
                    required
                />
            </div>
            <div class="mb-4">
                <label
                    class="block text-gray-700 text-sm font-bold mb-2"
                    for="homepage"
                >
                    Home page
                </label>
                <i
                    class="error mt-2 text-sm text-red-600"
                    id="homepage-error"
                ></i>
                <input
                    class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="homepage"
                    name="homepage"
                    type="link"
                    v-model="homepage"
                    placeholder="Your home page"
                />
            </div>
            <div class="mb-4">
                <label
                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                    for="file"
                >
                    Upload file
                    <i class="mt-2 text-sm text-red-600" id="file-error"></i>
                </label>
                <input
                    @change="arrangeFiles()"
                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                    type="file"
                    name="file"
                    id="file"
                    ref="myFiles"
                    accept="image/jpg,image/png,image/gif,text/plain"
                    multiple
                />
                <p
                    class="mt-1 text-sm text-gray-500 dark:text-gray-300"
                    id="file_input_help"
                >
                    SVG, PNG, JPG or GIF (MAX. 320х240px), 100kb.
                </p>
                <div class="preview"></div>
            </div>
            <div
                class="py-2 px-4 mb-4 bg-white rounded-lg rounded-t-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700"
            >
                <hr class="border-gray-500 border-t-2 py-1" />

                <label for="comment" class="sr-only">Your comment</label>
                <textarea
                    ref="myTextarea"
                    id="comment"
                    name="comment"
                    rows="6"
                    class="px-0 w-full text-sm text-gray-900 border-0 focus:ring-0 focus:outline-none dark:text-white dark:placeholder-gray-400 dark:bg-gray-800"
                    placeholder="Write a comment..."
                    required
                    v-model="comment"
                ></textarea>
            </div>
            <button
                type="submit"
                class="inline-flex items-center py-2.5 px-4 text-xs font-medium text-center text-white bg-primary-700 rounded-lg focus:ring-4 focus:ring-primary-200 dark:focus:ring-primary-900 hover:bg-primary-800"
            >
                Post comment
            </button>
            <button
                @click.prevent="commentPreview()"
                type="submit"
                class="inline-flex items-center py-2.5 px-4 mx-2 text-xs font-medium text-center text-white bg-primary-700 rounded-lg focus:ring-4 focus:ring-primary-200 dark:focus:ring-primary-900 hover:bg-primary-800"
            >
                Preview
            </button>
        </form>
    </div>
</template>

<script>
import { postComment } from "../api.js";

export default {
    props: {
        replyId: {
            type: Number,
        },
    },
    data() {
        return {
            username: "",
            email: "",
            homepage: "",
            comment: "",
        };
    },
    mounted() {
        this.fetchLocalStorage();
    },
    methods: {
        fetchLocalStorage() {
            const formData = JSON.parse(localStorage.getItem("form_data"));
            if (formData) {
                this.username = formData.name;
                this.email = formData.email;
                this.homepage = formData.homepage;
            }
        },
        arrangeFiles() {
            if (this.$refs.myFiles.files.length > 0) {
              this.images.forEach((image) => {
                URL.revokeObjectURL(image);
              });
              this.images = [];
              this.files = [];
              for (let i = 0; i < this.$refs.myFiles.files.length; i++) {
                let file = this.$refs.myFiles.files[i];
                if (file.type.startsWith('image/')) {
                  let src = URL.createObjectURL(file);
                  this.images.push(src);
                } else {
                    this.files.push(file);
                }
              }
            }
        },
        async post() {
            const formElement = document.getElementById(
                `commentForm-${this.replyId}`
            );
            let postForm = new FormData(formElement);

            if (this.replyId) {
                postForm.append("replyId", this.replyId);
            }

            const elements = document.querySelectorAll('[id$="-error"]');
            elements.forEach((element) => {
                element.innerHTML = "";
            });

            const response = await postComment(postForm);

            if (response) {
                this.comment = "";
                this.$emit("commented");

                localStorage.setItem(
                    "form_data",
                    JSON.stringify({
                        name: postForm.get("username"),
                        email: postForm.get("email"),
                        homepage: postForm.get("homepage"),
                    })
                );
            }
        },
        commentPreview() {
            const string = this.comment;
            const strippedString = string.replace(/^[\r\n]+|[\r\n]+$/g, "");
            if (/^\s*$/.test(strippedString)) {
                this.comment = "";
                this.$refs.myTextarea.value = "";
            } else {
                this.comment = strippedString;
            }

            const formElement = document.getElementById(
                `commentForm-${this.replyId}`
            );

            if (!formElement.reportValidity()) {
                return;
            }

            const formData = new FormData(formElement);

            const date = new Date();
            const created_at = `${date.getFullYear()}-${(date.getMonth() + 1)
                .toString()
                .padStart(2, "0")}-${date
                .getDate()
                .toString()
                .padStart(2, "0")} in ${date
                .getHours()
                .toString()
                .padStart(2, "0")}:${date
                .getMinutes()
                .toString()
                .padStart(2, "0")}`;

            const textWithLineBreaks = formData.get("comment");
            const textWithBreakTags = textWithLineBreaks.replace(
                /(?:\r\n|\r|\n)/g,
                "<br>"
            );
            const textWithSingleBreakTags = textWithBreakTags.replace(
                /(?:<br>\s*){2,}/g,
                "<br>"
            );

            const data = {
                name: formData.get("username"),
                email: formData.get("email"),
                text: textWithSingleBreakTags,
                replies: [],
                created_at: created_at,
            };

            this.$emit("preview", data);
        },
        preview(index) {
            this.indexRef = index;
            this.visibleRef = true;
        },
    },
};
</script>

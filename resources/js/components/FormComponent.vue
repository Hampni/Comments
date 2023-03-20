<template>
    <div>
        <form @submit.prevent="post()" :id="'commentForm-' + replyId" class="mb-6">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                Username
            </label>
            <i class="error mt-2 text-sm text-red-600" id="username-error"></i>
            <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="username" name="username" type="text" placeholder="Your username" v-model="username" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                Email
            </label>
            <i class="error mt-2 text-sm text-red-600" id="email-error"></i>
            <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="text" placeholder="Your email" v-model="email" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="homepage">
                Home page
            </label>
            <i class="error mt-2 text-sm text-red-600" id="homepage-error"></i>
            <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="homepage" name="homepage" type="link" v-model="homepage" placeholder="Your home page">
        </div>
        <div class="mb-4">
            <img id="captchaImage" :src="captchaImage" alt="captcha image" />
            <input :id="`captcha-${this.replyId}`" class="appearance-none border rounded w-full mt-3 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" v-model="captchaInput" placeholder="Captcha">
        </div>
        <div class="mb-4">
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file">
                Upload file
                <i class=" mt-2 text-sm text-red-600" id="file-error"></i>
            </label>
            <input @change="arrangeFiles()"
             class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" type="file" name="file" id="file" ref="myFiles" accept="image/jpg,image/png,image/gif,text/plain" multiple>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_input_help">SVG, PNG, JPG or GIF (MAX. 320х240px), 100kb.</p>
            <div class="preview">

                <div class="flex flex-column">
                    <div>
                         <a v-for="(file, index) in files" :key="index" 
                            :href="getFileUrl(file)"
                            class="m-1 object-contain cursor-pointer text-blue-400"
                            :class="`file-preview-${index}`"
                            target="_blank"
                        >{{ file.name }}</a>
                    </div>
                    <div class="flex flex-row flex-wrap gap-4">
                        <img 
                        v-for="(image, index) in images"
                        :key="index"
                        @click.prevent="preview(index)" 
                        :src="image" 
                        class="cursor-pointer border-3 border border-gray-600 rounded w-24 h-24"
                        alt="image" />
                    </div>
                </div>
            </div>
        </div>
        <div class="py-2 px-4 mb-4 bg-white rounded-lg rounded-t-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <button @click.prevent="insertTag('b')" type="button" class="text-purple-700 hover:text-white border border-purple-700 hover:bg-purple-800 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-sm px-2 py-1 text-center mr-2 mb-2 dark:border-purple-400 dark:text-purple-400 dark:hover:text-white dark:hover:bg-purple-500 dark:focus:ring-purple-900">b</button>
            <button @click.prevent="insertTag('i')" type="button" class="text-purple-700 hover:text-white border border-purple-700 hover:bg-purple-800 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-sm px-2 py-1 text-center mr-2 mb-2 dark:border-purple-400 dark:text-purple-400 dark:hover:text-white dark:hover:bg-purple-500 dark:focus:ring-purple-900">i</button>
            <button @click.prevent="insertTag('code')" type="button" class="text-purple-700 hover:text-white border border-purple-700 hover:bg-purple-800 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-sm px-2 py-1 text-center mr-2 mb-2 dark:border-purple-400 dark:text-purple-400 dark:hover:text-white dark:hover:bg-purple-500 dark:focus:ring-purple-900">code</button>
            <button @click.prevent="insertTag('a')" type="button" class="text-purple-700 hover:text-white border border-purple-700 hover:bg-purple-800 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-sm px-2 py-1 text-center mr-2 mb-2 dark:border-purple-400 dark:text-purple-400 dark:hover:text-white dark:hover:bg-purple-500 dark:focus:ring-purple-900">link</button>
            <hr class="border-gray-500 border-t-2 py-1">

            <label for="comment" class="sr-only">Your comment</label>
            <textarea ref="myTextarea" id="comment" name="comment" rows="6"
                class="px-0 w-full text-sm text-gray-900 border-0 focus:ring-0 focus:outline-none dark:text-white dark:placeholder-gray-400 dark:bg-gray-800"
                placeholder="Write a comment..." required v-model="comment"></textarea>
                
        </div>
        <button type="submit"
            class="inline-flex items-center py-2.5 px-4 text-xs font-medium text-center text-white bg-primary-700 rounded-lg focus:ring-4 focus:ring-primary-200 dark:focus:ring-primary-900 hover:bg-primary-800">
            Post comment
        </button>
        <button 
            @click.prevent="commentPreview()"
            type="submit"
            class="inline-flex items-center py-2.5 px-4 mx-2 text-xs font-medium text-center text-white bg-primary-700 rounded-lg focus:ring-4 focus:ring-primary-200 dark:focus:ring-primary-900 hover:bg-primary-800">
            Preview
        </button>
    </form> 
    <vue-easy-lightbox
        :visible="visibleRef"
        :imgs="images"
        :index="indexRef"
        @hide="onHide"
      ></vue-easy-lightbox>
</div>  
</template>

<script>
import VueEasyLightbox from 'vue-easy-lightbox'
import { postComment } from "../api.js";

export default {
    components: { 
        VueEasyLightbox,
    },
    props: {
        replyId: {
            type: Number,
        }
    },
    data() {
      return {
        username: "",
        email: "",
        homepage: "",
        comment: "",
        files: [],
        images: [],
        toggler: false,
        slide: 1,
        captchaImage: "",
        captchaText: "",
        captchaInput: "",
        visibleRef: false,
        indexRef: 0,
      };
    },
    mounted() {
        this.fetchLocalStorage();
        this.$nextTick(() => {
            this.getCaptcha();
        })
    },
    methods: {
        async getCaptcha() {
            await axios
                .get("/getCapthca", { responseType: "arraybuffer" })
                .then((response) => {
                    let imageBase64 = btoa(
                        new Uint8Array(response.data).reduce(
                            (data, byte) => data + String.fromCharCode(byte),
                            ""
                        )
                    );
                    this.captchaImage = "data:image/png;base64," + imageBase64;
                    this.captchaText = response.headers["x-captcha"];
                    
                    if (this.captchaText == undefined) {
                        this.getCaptcha();
                    }
                    
                });
        },
        commentPreview() {
            const string = this.comment;
            const strippedString = string.replace(/^[\r\n]+|[\r\n]+$/g, ''); 
            if (/^\s*$/.test(strippedString)) { 
                this.comment = ''; 
                this.$refs.myTextarea.value = ''; 
            } else {
                this.comment = strippedString;
            }

            const formElement = document.getElementById(`commentForm-${this.replyId}`);
            
            if (!formElement.reportValidity()) {
                return;
            }

            const formData = new FormData(formElement);

            const date = new Date();
            const created_at = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getDate().toString().padStart(2, '0')} in ${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}`;

            const files = Array.from(formData.getAll('file')).map(file => ({
                file_path: URL.createObjectURL(file),
                size: file.size,
                type: file.name.endsWith('.txt') ? 'txt' : 'image',
                namePreview: file.name
                }))

            for(let i=0; i < files.length; i++) {
                if (files[i].size ==0) {
                    files.splice(i, 1);
                }
            }

            const textWithLineBreaks = formData.get('comment');
            const textWithBreakTags = textWithLineBreaks.replace(/(?:\r\n|\r|\n)/g, '<br>');
            const textWithSingleBreakTags = textWithBreakTags.replace(/(?:<br>\s*){2,}/g, '<br>');
            
            const data = {
              name: formData.get('username'),
              email: formData.get('email'),
              text: textWithSingleBreakTags,
              replies: [],
              created_at: created_at,
              files: files,
            };

            this.$emit('preview', data);
        },
        async post() {
            const captcha = document.getElementById(`captcha-${this.replyId}`);
            captcha.classList.remove('bg-red-100');

            if (this.captchaText != this.captchaInput) {
                captcha.classList.add('bg-red-100');
                document.getElementById('homepage').scrollIntoView({ behavior: 'smooth' });
                this.captchaInput = '';
                this.getCaptcha()
                return;
            }

            const formElement = document.getElementById(`commentForm-${this.replyId}`);
            let postForm = new FormData(formElement);
            
            if (this.replyId) {
                postForm.append('replyId', this.replyId);
            }

            const files = formElement.querySelector('#file').files;
            for (let i = 0; i < files.length; i++) {
              const file = files[i];
              if (file) {
                postForm.append('file[]', file);
              }
            }

                const elements = document.querySelectorAll('[id$="-error"]');
                elements.forEach(element => {
                  element.innerHTML = '';
                });
                
                const response = await postComment(postForm);
            
                if (response) {

                    if (response?.response?.status == 422) {
                        let element;
                        let errorMessage;
                        Object.keys(response.response.data.errors).forEach(prop => {
                            if (prop.includes('file.')) {
                                let propId = 'file'
                                element = document.getElementById(propId + '-error')
                                errorMessage = 'The files must not be greater than 100 kilobytes.'
                                element.innerHTML = errorMessage
                                element.scrollIntoView({ behavior: 'smooth' })
                            } else {
                                    prop.replace(/file\.\d+/, 'file');
                                    element = document.getElementById(prop + '-error')
                                    errorMessage = response.response.data.errors[prop][0]
                                    element.innerHTML = errorMessage
                                    element.scrollIntoView({ behavior: 'smooth' })

                            }
                        })
                        return;
                    }

                    this.getCaptcha();
                    this.captchaInput = ''
                    this.comment = '';
                    this.$refs.myFiles.value = '';
                    this.images = [];
                    this.files = [];
                    this.$emit('commented');
                
                    localStorage.setItem('form_data', JSON.stringify({
                      name: postForm.get('username'),
                      email: postForm.get('email'),
                      homepage: postForm.get('homepage'),
                    }));
                }

        },
        fetchLocalStorage() {
            const formData = JSON.parse(localStorage.getItem('form_data'));
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
        insertTag(tagName) {
            const textarea = this.$refs.myTextarea;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            let replacement;
            if (tagName === 'a') {
                const link = prompt("Enter the URL of the link:");
                if (link) {
                  replacement = `<a href="${link}" class="link">${selectedText}</a>`;
                } else {
                    return;
                }
            } else {
                replacement = `<${tagName}>${selectedText}</${tagName}>`;
            }
            textarea.value =
              textarea.value.substring(0, start) +
              replacement +
              textarea.value.substring(end);
              this.comment = textarea.value
        },
        preview(index) {
            this.indexRef = index;
            this.visibleRef = true
        },
        getFileUrl(file) {
          return URL.createObjectURL(file);
        },
        onHide() {
            this.visibleRef = false
        }

    }
};
</script>
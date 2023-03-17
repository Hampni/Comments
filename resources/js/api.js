import axios from "axios";

export const fetchComments = async (url) => {
    try {
        const response = await axios.get(url);
        return response.data;
    } catch (e) {
        console.error(e);
    }
};

export const clearCache = async (url) => {
    try {
        await axios.get(url);
    } catch (e) {
        console.error(e);
    }
};

export const postComment = async (formData) => {
    try {
        const files = formData.getAll("file");

        for (let i = 0; i < files.length; i++) {
            const file = files[i];

            if (file.type.startsWith("image/")) {
                const resizedFile = await resizeImage(file);
                formData.set(`file[${i}]`, resizedFile, file.name);
            }
        }

        let res = await axios
            .post("/api/comments/store", formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            })
            .catch((error) => {
                return error;
            });

        return res;
    } catch (e) {
        return e;
    }
};

function resizeImage(file) {
    return new Promise((resolve, reject) => {
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");
        const img = new Image();
        img.onload = () => {
            const { width, height } = img;
            if (width > 320 || height > 240) {
                const ratio = Math.min(320 / width, 240 / height);
                canvas.width = width * ratio;
                canvas.height = height * ratio;
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                canvas.toBlob((blob) => resolve(blob), file.type, 0.8);
            } else {
                resolve(file);
            }
        };
        img.onerror = reject;
        img.src = URL.createObjectURL(file);
    });
}

import axios from "axios";

export const fetchComments = async (url) => {
    try {
        const response = await axios.get(url);
        return response.data;
    } catch (e) {
        console.error(e);
    }
};

export const postComment = async (formData) => {
    try {
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


import axios from "axios";

export const fetchComments = async (url) => {
    try {
        const response = await axios.get(url);
        return response.data;
    } catch (e) {
        console.error(e);
    }
};



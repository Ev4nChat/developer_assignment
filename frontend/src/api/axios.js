import axios from "axios";

const api = axios.create({
    baseURL: "http://localhost:8000",
});

// Attach Bearer token manually
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => Promise.reject(error)
);

// Handle expired or invalid token globally, but exclude /login
api.interceptors.response.use(
    (response) => response,
    (error) => {
        const originalRequest = error.config;

        // If 401 and NOT a login request, redirect to /login
        if (
            error.response &&
            error.response.status === 401 &&
            !originalRequest.url.includes('/login')
        ) {
            localStorage.removeItem('token');
            localStorage.removeItem('role');
            window.location.href = "/login";
        }

        return Promise.reject(error);
    }
);

export default api;

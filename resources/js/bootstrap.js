import axios from 'axios';
window.axios = axios;

const ACCESS_TOKEN_KEY = 'access_token';
const REFRESH_TOKEN_KEY = 'refresh_token';
let refreshPromise = null;

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const getAccessToken = () => window.localStorage.getItem(ACCESS_TOKEN_KEY);
const getRefreshToken = () => window.localStorage.getItem(REFRESH_TOKEN_KEY);

const storeTokens = ({ access_token: accessToken, refresh_token: refreshToken }) => {
    if (accessToken) {
        window.localStorage.setItem(ACCESS_TOKEN_KEY, accessToken);
        window.axios.defaults.headers.common.Authorization = `Bearer ${accessToken}`;
    }

    if (refreshToken) {
        window.localStorage.setItem(REFRESH_TOKEN_KEY, refreshToken);
    }
};

const clearTokens = () => {
    window.localStorage.removeItem(ACCESS_TOKEN_KEY);
    window.localStorage.removeItem(REFRESH_TOKEN_KEY);
    delete window.axios.defaults.headers.common.Authorization;
};

const existingAccessToken = getAccessToken();
if (existingAccessToken) {
    window.axios.defaults.headers.common.Authorization = `Bearer ${existingAccessToken}`;
}

window.authTokens = {
    clear: clearTokens,
    getAccessToken,
    getRefreshToken,
    set: storeTokens,
};

window.axios.interceptors.request.use((config) => {
    const accessToken = getAccessToken();

    if (accessToken) {
        config.headers.Authorization = `Bearer ${accessToken}`;
    }

    return config;
});

window.axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const originalRequest = error.config;

        if (
            error.response?.status !== 401 ||
            ! originalRequest ||
            originalRequest._retry ||
            originalRequest.url?.includes('/api/auth/refresh')
        ) {
            return Promise.reject(error);
        }

        const refreshToken = getRefreshToken();
        if (! refreshToken) {
            clearTokens();

            return Promise.reject(error);
        }

        originalRequest._retry = true;

        try {
            refreshPromise ??= window.axios.post('/api/auth/refresh', {
                refresh_token: refreshToken,
            });

            const { data } = await refreshPromise;
            storeTokens(data);

            originalRequest.headers.Authorization = `Bearer ${data.access_token}`;

            return window.axios(originalRequest);
        } catch (refreshError) {
            clearTokens();

            return Promise.reject(refreshError);
        } finally {
            refreshPromise = null;
        }
    },
);

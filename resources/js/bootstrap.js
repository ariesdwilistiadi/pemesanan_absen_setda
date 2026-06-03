import axios from 'axios';
window.axios = axios;

const ACCESS_TOKEN_KEY = 'access_token';
const REFRESH_TOKEN_KEY = 'refresh_token';
let refreshPromise = null;

// Mengambil token CSRF bawaan Laravel saat pertama kali load
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

// Request Interceptor: Menyisipkan Access Token ke setiap request
window.axios.interceptors.request.use((config) => {
    const accessToken = getAccessToken();

    if (accessToken) {
        config.headers.Authorization = `Bearer ${accessToken}`;
    }

    return config;
});

// Response Interceptor: Menangani eror token
window.axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const originalRequest = error.config;

        // --- 1. PENANGANAN 419 (CSRF BASI) SECARA DIAM-DIAM ---
        if (error.response?.status === 419 && !originalRequest._retryCsrf) {
            originalRequest._retryCsrf = true;

            try {
                // Meminta cookie CSRF baru ke server secara diam-diam (bawaan Laravel)
                await window.axios.get('/sanctum/csrf-cookie');
                
                // Hapus token lama dari header agar Axios otomatis menggunakan Cookie baru
                delete window.axios.defaults.headers.common['X-CSRF-TOKEN'];
                if (originalRequest.headers) {
                    delete originalRequest.headers['X-CSRF-TOKEN'];
                }

                // Langsung ulangi request yang tadinya gagal (user tidak akan sadar ada eror)
                return window.axios(originalRequest);
            } catch (csrfError) {
                return Promise.reject(csrfError);
            }
        }

        // --- 2. PENANGANAN 401 (UNAUTHORIZED / JWT BASI) ---
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
            // Meminta Access Token baru menggunakan Refresh Token
            refreshPromise ??= window.axios.post('/api/auth/refresh', {
                refresh_token: refreshToken,
            });

            const { data } = await refreshPromise;
            storeTokens(data);

            originalRequest.headers.Authorization = `Bearer ${data.access_token}`;

            // Ulangi request yang tadinya gagal
            return window.axios(originalRequest);
        } catch (refreshError) {
            clearTokens();
            return Promise.reject(refreshError);
        } finally {
            refreshPromise = null;
        }
    },
);
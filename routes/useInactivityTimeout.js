import { onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

export function useInactivityTimeout(timeoutMinutes = 15) {
    let timeoutId;
    // Konversi menit ke milidetik
    const timeoutMs = timeoutMinutes * 60 * 1000;

    const resetTimer = () => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            logout();
        }, timeoutMs);
    };

    const logout = () => {
        cleanup(); // Bersihkan event listener
        
        // Kirim request logout menggunakan Inertia
        router.post(route('logout'), {}, {
            onSuccess: () => alert('Sesi Anda telah berakhir karena tidak ada aktivitas.'),
        });
    };

    const cleanup = () => {
        clearTimeout(timeoutId);
        window.removeEventListener('mousemove', resetTimer);
        window.removeEventListener('mousedown', resetTimer);
        window.removeEventListener('keypress', resetTimer);
        window.removeEventListener('touchstart', resetTimer);
    };

    onMounted(() => {
        window.addEventListener('mousemove', resetTimer);
        window.addEventListener('mousedown', resetTimer);
        window.addEventListener('keypress', resetTimer);
        window.addEventListener('touchstart', resetTimer);
        
        resetTimer(); // Mulai timer saat komponen dimuat
    });

    onUnmounted(() => cleanup());
}

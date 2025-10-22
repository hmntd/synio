import { ref } from 'vue';

const token = ref<string | null>(localStorage.getItem('token'));
const tokenReady = ref<boolean>(!!token.value);

let tokenPromise: Promise<string> | null = null;

export const useAuthToken = () => {
    async function ensureToken(): Promise<string> {
        if (tokenReady.value && token.value) return token.value;

        if (tokenPromise) return tokenPromise;

        tokenPromise = fetch('/api/create-token', {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
        })
            .then(async (res) => {
                if (!res.ok) throw new Error('Failed to create token');
                const data = await res.json();
                if (!data.token) throw new Error('No token in response');
                localStorage.setItem('token', data.token);
                token.value = data.token;
                tokenReady.value = true;
                return data.token;
            })
            .finally(() => {
                tokenPromise = null;
            });

        return tokenPromise;
    }

    return { token, tokenReady, ensureToken };
}

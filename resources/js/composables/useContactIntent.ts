import { ref } from 'vue';

/** The service a visitor picked on a service card, handed to the contact form. */
const service = ref<string | null>(null);
const requests = ref(0);

export function useContactIntent() {
    return {
        service,
        requests,
        ask(slug: string) {
            service.value = slug;
            requests.value++;
        },
    };
}

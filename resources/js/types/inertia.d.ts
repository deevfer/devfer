import type { SiteProps } from '@/composables/useSite';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: SiteProps;
    }
}

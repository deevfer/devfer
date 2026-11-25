import type { SiteProps } from '@/composables/useI18n';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: SiteProps;
    }
}

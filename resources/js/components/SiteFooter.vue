<script setup lang="ts">
import { ArrowUp } from '@lucide/vue';
import Monogram from './Monogram.vue';
import { useSite } from '@/composables/useSite';

const { props, t, href, anchor } = useSite();
const year = new Date().getFullYear();
</script>

<template>
    <footer class="container-site pb-10">
        <div class="flex flex-col gap-8 border-t border-line pt-8 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <Monogram />
                <p class="text-sm text-muted">© {{ year }} {{ props.profile.name }}. {{ t('footer.rights') }}</p>
            </div>
            <nav :aria-label="t('nav.label')">
                <ul class="flex flex-wrap gap-x-5 gap-y-2 text-sm">
                    <li v-for="item in props.nav" :key="item.target"><a :href="href(item.target)" class="footer-link">{{ item.label }}</a></li>
                    <li>
                        <a :href="`#${anchor('top')}`" class="footer-link inline-flex items-center gap-1.5">{{ t('footer.top') }} <ArrowUp :size="14" aria-hidden="true" /></a>
                    </li>
                </ul>
            </nav>
        </div>
    </footer>
</template>

<style scoped>
.footer-link { display: inline-flex; min-height: 44px; align-items: center; color: var(--color-muted); transition: color 160ms ease; }
@media (hover: hover) and (pointer: fine) { .footer-link:hover { color: var(--color-ink); } }
</style>

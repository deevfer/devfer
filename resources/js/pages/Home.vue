<script setup lang="ts">
import SiteHeader from '@/components/SiteHeader.vue';
import Hero from '@/components/Hero.vue';
import About from '@/components/About.vue';
import Numbers from '@/components/Numbers.vue';
import Industries from '@/components/Industries.vue';
import Capabilities from '@/components/Capabilities.vue';
import Evolution from '@/components/Evolution.vue';
import Contact from '@/components/Contact.vue';
import { useSmoothScroll } from '@/composables/useSmoothScroll';

useSmoothScroll();
</script>

<template>
    <SiteHeader />
    <main id="main" tabindex="-1" class="outline-none">
        <Hero />
        <About />
        <Numbers />
        <Industries />
        <Capabilities />
        <Evolution />
        <Contact />
    </main>
</template>

<script setup lang="ts">
import SiteHeader from '@/components/SiteHeader.vue';
import HeroSection from '@/components/HeroSection.vue';
import ServicesSection from '@/components/ServicesSection.vue';
import ProfileSection from '@/components/ProfileSection.vue';
import TechnologiesSection from '@/components/TechnologiesSection.vue';
import CvSection from '@/components/CvSection.vue';
import ContactSection from '@/components/ContactSection.vue';
import SiteFooter from '@/components/SiteFooter.vue';
</script>

<template>
    <SiteHeader />
    <main id="main" tabindex="-1" class="outline-none">
        <HeroSection />
        <ProfileSection />
        <ServicesSection />
        <TechnologiesSection />
        <CvSection />
        <ContactSection />
    </main>
    <SiteFooter />
</template>

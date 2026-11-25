<script setup lang="ts">
import { computed, type Component } from 'vue';
import {
    Bot, Cloud, CodeXml, Database, Gauge, LayoutTemplate, Link, Mail, MessageCircle, MonitorSmartphone,
    Palette, PenTool, Plug, Server, ShieldCheck, Smartphone, Sparkles, Workflow, Wrench, Globe,
} from '@lucide/vue';

/** Icons chosen in the panel by name (services and links). Unknown names fall back to a neutral symbol. */
const ICONS: Record<string, Component> = {
    'code-xml': CodeXml, 'pen-tool': PenTool, smartphone: Smartphone, 'monitor-smartphone': MonitorSmartphone,
    server: Server, cloud: Cloud, workflow: Workflow, bot: Bot, sparkles: Sparkles, 'shield-check': ShieldCheck,
    'layout-template': LayoutTemplate, palette: Palette, database: Database, gauge: Gauge, wrench: Wrench, plug: Plug,
    mail: Mail, whatsapp: MessageCircle, link: Link, linkedin: Globe, github: Globe, gitlab: Globe, instagram: Globe, x: Globe,
};

const props = withDefaults(defineProps<{ name: string; size?: number; strokeWidth?: number }>(), { size: 20, strokeWidth: 1.75 });
const icon = computed(() => ICONS[props.name] ?? Sparkles);
</script>

<template>
    <component :is="icon" :size="size" :stroke-width="strokeWidth" aria-hidden="true" />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from '@/composables/useI18n';
import { ANCHORS } from '@/lib/scenes';

const ITEMS = ['websites', 'webapps', 'mobile', 'interfaces', 'architecture', 'infrastructure'] as const;
type Item = (typeof ITEMS)[number];

const { t, raw, locale } = useI18n('capabilities');
const open = ref<Item | null>('websites');
const stack = raw<{ where: string; tech: string[] }[]>('stack');
const tech = (item: Item) => raw<string[]>(`items.${item}.tech`);
</script>

<template>
    <section :id="ANCHORS[locale].capabilities" data-scene="capabilities" aria-labelledby="capabilities-title" class="py-28 lg:py-40">
        <div class="frame">
            <div class="grid-12 gap-y-6">
                <h2 id="capabilities-title" class="display col-span-4 text-display-xl md:col-span-6 lg:col-span-7">
                    {{ t('title') }}
                </h2>
                <p class="col-span-4 max-w-[38ch] self-end text-lead text-graphite md:col-span-5 lg:col-span-4 lg:col-start-9">
                    {{ t('lead') }}
                </p>
            </div>

            <ul class="mt-16 border-t border-ink lg:mt-24">
                <li v-for="item in ITEMS" :key="item" class="border-b border-hairline">
                    <h3>
                        <button
                            type="button"
                            :aria-expanded="open === item"
                            :aria-controls="`cap-${item}`"
                            class="cap-row flex w-full items-center justify-between gap-6 py-5 text-left lg:py-7"
                            @click="open = open === item ? null : item"
                        >
                            <span class="cap-name display text-display-l">{{ t(`items.${item}.name`) }}</span>
                            <span
                                aria-hidden="true"
                                class="cap-plus grid size-11 shrink-0 place-items-center rounded-full border border-hairline text-xl leading-none"
                            >
                                +
                            </span>
                        </button>
                    </h3>
                    <div :id="`cap-${item}`" class="cap-panel" :data-open="open === item">
                        <div>
                            <div class="grid-12 gap-y-6 pb-10 lg:pb-12" :inert="open !== item">
                                <p class="col-span-4 max-w-[46ch] text-lead md:col-span-5 lg:col-span-5">{{ t(`items.${item}.body`) }}</p>
                                <div v-if="tech(item).length > 0" class="col-span-4 md:col-span-3 lg:col-span-5 lg:col-start-8">
                                    <p class="label text-graphite">{{ t('builtWith') }}</p>
                                    <ul class="mt-3 flex flex-wrap gap-2">
                                        <li v-for="name in tech(item)" :key="name" class="chip">{{ name }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>

            <!-- The stack as a sentence, grouped by where each tool runs -->
            <div class="mt-28 lg:mt-40">
                <h3 class="label text-graphite">{{ t('stackTitle') }}</h3>
                <dl class="mt-8 space-y-6 lg:space-y-5">
                    <div v-for="row in stack" :key="row.where" class="flex flex-col gap-3 lg:flex-row lg:items-baseline lg:gap-8">
                        <dt class="display shrink-0 text-display-l text-graphite lg:w-[34%]">{{ row.where }}</dt>
                        <dd class="flex flex-wrap gap-2">
                            <span v-for="name in row.tech" :key="name" class="chip text-ink">{{ name }}</span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</template>

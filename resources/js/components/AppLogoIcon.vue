<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/images/logo.png"
        alt="Cheku Left"
        :class="[className, 'object-contain']"
        v-bind="$attrs"
    />
</template>

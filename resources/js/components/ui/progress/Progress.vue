<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue?: number;
    max?: number;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: 0,
    max: 100,
});

const percentage = computed(() => {
    return Math.min(100, Math.max(0, (props.modelValue / props.max) * 100));
});
</script>

<template>
    <div class="relative h-2 w-full overflow-hidden rounded-full bg-secondary">
        <div
            class="h-full bg-primary transition-all"
            :style="{ width: `${percentage}%` }"
        />
    </div>
</template>

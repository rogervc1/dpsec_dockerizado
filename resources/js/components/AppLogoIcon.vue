<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import dpsecLogo from '../../../assets/logo_dpsec.png';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img :src="dpsecLogo" alt="DPSEC" :class="className" v-bind="$attrs" />
</template>

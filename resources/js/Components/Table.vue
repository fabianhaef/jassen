<script setup>
import Card from '@/Components/Card.vue';

const props = defineProps({
    playedCards: Array,
});
</script>

<template>
    <ul class="flex gap-2">
        <li v-for="card in playedCards" :key="card">
            <Card :card="card" />
        </li>
    </ul>
</template>
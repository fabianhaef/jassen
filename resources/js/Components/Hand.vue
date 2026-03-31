<script setup>
import Card from '@/Components/Card.vue';
const props = defineProps({
    hand: Object,
    is_my_turn: Boolean,
});

const playCard = (card) => {
    emit('playCard', card);
};

const emit = defineEmits(['playCard']);
</script>

<template>
    <ul class="hand">
        <li class="hand-card" v-for="card in hand.cards" :key="card">
            <button @click="playCard(card)" :disabled="!is_my_turn">
                <Card :card="card" />
            </button>
        </li>
    </ul>
</template>

<style>
.hand {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 10px;
}

.hand-card {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
}
</style>
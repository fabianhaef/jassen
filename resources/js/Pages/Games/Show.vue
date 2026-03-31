<script setup>
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import Table from '@/Components/Table.vue';

const props = defineProps({
    game_id: Number,
    hand: Object,
    currentTrick: Object,
    playedCards: Array,
    round: Object,
    variation: String,
    trump: String,
    team_score: Number,
    current_player: String,
    is_my_turn: Boolean,
    opponent_score: Number,
    teamMate: Object,
    opponent1: Object,
    opponent2: Object,
});

const playCard = (card) => {
    router.post(`/games/${props.game_id}/play`, {
        played_card_id: card
    });
};
const playedCards = computed(() => {
    return props.playedCards.map(card => card.card.suit + '-' + card.card.rank);
});
</script>

<template>
    <div class="flex flex-col items-center justify-center">
        <h1>Jassen Game</h1>
        <p>Variation: {{ variation }}</p>
        <p>Team Score: {{ team_score }}</p>
        <p>Opponent Score: {{ opponent_score }}</p>
        <p>Game ID: {{ game_id }}</p>
    </div>
    <Table :playedCards="playedCards" :ownCards="hand.cards" @playCard="playCard"></Table>
</template>
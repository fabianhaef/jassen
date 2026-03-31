<script setup>
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import Hand from '@/Components/Hand.vue';
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
console.log(playedCards.value);
</script>

<template>
    <div class="flex flex-col items-center justify-center">
        <h1>Jassen Game</h1>
        <p>Variation: {{ variation }}</p>
        <p>Team Score: {{ team_score }}</p>
        <p>Opponent Score: {{ opponent_score }}</p>
        <p>Game ID: {{ game_id }}</p>
    </div>

    <template v-if="is_my_turn">
        <p>It is your turn to play</p>
    </template>

    <template v-else>
        <p>It is not your turn to play</p>
    </template>
    <template v-if="hand">
        <Hand :hand="hand" :is_my_turn="is_my_turn" @playCard="playCard" />
    </template>

    <template v-if="playedCards.length > 0">
        <Table :playedCards="playedCards" />
    </template>

    <template v-if="opponent1">
        <p>Opponent 1: {{ opponent1.name }}</p>
        <p>Cards Remaining: {{ opponent1.cards_remaining }}</p>
    </template>

    <template v-if="teamMate">
        <p>Team Mate: {{ teamMate.name }}</p>
        <p>Cards Remaining: {{ teamMate.cards_remaining }}</p>
    </template>


    <template v-if="opponent2">
        <p>Opponent 2: {{ opponent2.name }}</p>
        <p>Cards Remaining: {{ opponent2.cards_remaining }}</p>
    </template>

    <template v-else>
        <p>No hand found</p>
    </template>
</template>
<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    game_id: Number,
    hand: Object,
    currentTrick: Object,
    playedCards: Array,
    round: Object,
    variation: String,
    team_score: Number,
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
</script>


<template>
    <h1>Jassen Game</h1>
    <p>Variation: {{ variation }}</p>
    <p>Team Score: {{ team_score }}</p>
    <p>Opponent Score: {{ opponent_score }}</p>
    <p>Game ID: {{ game_id }}</p>
    <template v-if="hand">
        <ul>
            <li v-for="card in hand.cards" :key="card">
                <button @click="playCard(card)">Play Card</button>
                {{ card }}
            </li>
        </ul>
    </template>

    <template v-if="playedCards.length > 0">
        <ul>
            <li v-for="card in playedCards" :key="card">
                <p>{{ card }}</p>
            </li>
        </ul>
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
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
});

console.log(props);

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

    <template v-else>
        <p>No hand found</p>
    </template>
</template>
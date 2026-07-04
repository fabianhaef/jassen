<script setup>
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import Table from '@/Components/Table.vue';
import GameInfo from '@/Components/GameInfo.vue';

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

const selectTrump = (trump) => {
    console.log('selecting trump', trump);
    router.post(`/games/${props.game_id}/select-trump`, {
        trump: trump
    });
};
</script>

<template>
    <GameInfo :game="{ name: 'Jassen Game', variation: variation, team_score: team_score, opponent_score: opponent_score }"></GameInfo>
    <Table :playedCards="playedCards" :ownCards="hand.cards" :teamMate="teamMate" :opponent1="opponent1" :opponent2="opponent2" :trump="trump || ''" :is_my_turn="is_my_turn" @playCard="playCard" @selectTrump="selectTrump"></Table>
</template>
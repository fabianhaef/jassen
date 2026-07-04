<script setup>
const props = defineProps({
    game: Object,
});
</script>

<template>
    <div class="game-info">
        <h1>{{ game.name }}</h1>
        <p>Variation: {{ game.variation }}</p>
        <p>Team Score: {{ game.team_score }}</p>
        <p>Opponent Score: {{ game.opponent_score }}</p>    
        <p>Game ID: {{ game.game_id }}</p>
        <p>Current Player: {{ game.current_player }}</p>
        <p>Is My Turn: {{ game.is_my_turn }}</p>
        <p>Current Trick: {{ game.current_trick }}</p>
        <p>Played Cards: {{ game.played_cards }}</p>
        <p>Round: {{ game.round }}</p>
        <p>Trump: {{ game.trump }}</p>
        <p>Team Mate: {{ game.team_mate }}</p>
        <p>Opponent 1: {{ game.opponent1 }}</p>
        <p>Opponent 2: {{ game.opponent2 }}</p>
    </div>
</template>
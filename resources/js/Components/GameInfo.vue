<script setup>
const props = defineProps({
    game: Object,
});
</script>

<template>
    <div class="game-info">
        <h1>{{ game.name }}</h1>
        <p> Variation: {{ game.variation }}
            Team Score: {{ game.team_score }}
            Opponent Score: {{ game.opponent_score }}
            Game ID: {{ game.game_id }}
            Current Player: {{ game.current_player }}
            Is My Turn: {{ game.is_my_turn }}
            Current Trick: {{ game.current_trick }}
            Played Cards: {{ game.played_cards }}
            Round: {{ game.round }}
            Trump: {{ game.trump }}
            Team Mate: {{ game.team_mate }}
            Opponent 1: {{ game.opponent1 }}
            Opponent 2: {{ game.opponent2 }}</p>
    </div>
</template>
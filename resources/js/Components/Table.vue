<script setup>
import HiddenCard from '@/Components/HiddenCard.vue';
import Hand from '@/Components/Hand.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
    ownCards: Array,
    playedCards: Array,
});

const playCard = (card) => {
    emit('playCard', card);
};

const emit = defineEmits(['playCard']);

console.log(props);
</script>

<template>
    <div class="table">
        <!-- Teammate (top) -->
        <div class="teammate">
            <HiddenCard v-for="i in ownCards.length" :key="'tm-' + i" />
        </div>

        <!-- Opponent 1 (left) -->
        <div class="opponent-left">
            <HiddenCard v-for="i in ownCards.length" :key="'op1-' + i" />
        </div>

        <!-- Played cards (center) -->
        <div class="center">
            <Card v-for="card in playedCards" :key="card" :card="card" />
        </div>

        <!-- Opponent 2 (right) -->
        <div class="opponent-right">
            <HiddenCard v-for="i in ownCards.length" :key="'op2-' + i" />
        </div>

        <!-- Own hand (bottom) -->
        <div class="own-hand">
            <Hand :hand="{ cards: ownCards }" :is_my_turn="true" @playCard="playCard" />
        </div>
    </div>
</template>

<style>
.table {
    display: grid;
    grid-template-columns: 100px 1fr 100px;
    grid-template-rows: 100px 1fr 120px;
    gap: 8px;
    padding: 16px;
    height: 100vh;
    max-height: 100vh;
    overflow: hidden;
    box-sizing: border-box;
}

.teammate {
    grid-column: 2;
    grid-row: 1;
    display: flex;
    justify-content: center;
    gap: -20px;
    overflow: hidden;
}

.teammate :deep(.card) {
    width: 40px;
    height: 60px;
    margin-left: -15px;
}

.opponent-left {
    grid-column: 1;
    grid-row: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: -10px;
    overflow: hidden;
}

.opponent-left :deep(.card) {
    width: 40px;
    height: 60px;
    margin-top: -30px;
}

.opponent-left :deep(.card:first-child) {
    margin-top: 0;
}

.center {
    grid-column: 2;
    grid-row: 2;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
}

.center :deep(.card) {
    width: 80px;
    height: 120px;
}

.opponent-right {
    grid-column: 3;
    grid-row: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    overflow: hidden;
}

.opponent-right :deep(.card) {
    width: 40px;
    height: 60px;
    margin-top: -30px;
}

.opponent-right :deep(.card:first-child) {
    margin-top: 0;
}

.own-hand {
    grid-column: 1 / -1;
    grid-row: 3;
    display: flex;
    justify-content: center;
    overflow: hidden;
}

.own-hand :deep(.card) {
    width: 70px;
    height: 105px;
}
</style>
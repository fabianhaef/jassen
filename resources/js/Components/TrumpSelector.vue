<script setup>
const props = defineProps({
    trump: String,
});

const selectTrump = (trump) => {
    emit('selectTrump', trump);
};

const emit = defineEmits(['selectTrump']);
</script>

<template>
    <div class="trump-selector">
        <h1>Wähle den Trumpf</h1>
        <button @click="selectTrump('obeabe')">Obeabe</button>
        <button @click="selectTrump('undeufe')">Undeufe</button>
        <button @click="selectTrump('schellen')">Schellen</button>
        <button @click="selectTrump('rosen')">Rosen</button>
        <button @click="selectTrump('schilten')">Schilten</button>
        <button @click="selectTrump('eicheln')">Eicheln</button>
    </div>
</template>

<style scoped>
.trump-selector {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}
</style>
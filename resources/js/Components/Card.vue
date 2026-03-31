<script setup>
const props = defineProps({
    card: String,
});
</script>

<template>
    <img :src="'/images/cards/' + card + '.svg'" :alt="card" class="card" />
</template>


<style>
.card {
    width: 100px;
    height: 100px;
}
</style>
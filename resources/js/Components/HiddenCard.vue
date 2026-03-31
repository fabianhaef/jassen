<script setup>
const backImage = '/images/cards/back.svg';
</script>

<template>
    <img :src="backImage" alt="Hidden Card" class="card" />
</template>


<style>
.card {
    width: 100px;
    height: 100px;
}
</style>
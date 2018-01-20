<template>
    <div class="my-3">
        <input
         v-model="search.query_text"
         class="form-control form-control-lg"
         type="text"
         placeholder="Search...">
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>

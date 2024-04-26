<template>
  <h1>{{ text }}</h1>
</template>
<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "DokoHeader",
  props: {
    text: String,
  },
});
</script>
<style scoped>
h1 {
  width: 100%;
  text-align: center;
}
</style>

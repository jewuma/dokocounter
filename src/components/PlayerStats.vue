<template>
  <td>{{ player.name }}</td>
  <td
    :key="key"
    v-for="(val, key) in stats"
    :class="[
      key == 'ReProzent' ||
      key == 'Gewonnen' ||
      key == 'Soli' ||
      key == 'Soli gewonnen'
        ? 'notshown'
        : '',
    ]"
  >
    {{ val }}
  </td>
</template>
<script lang="ts">
//import Header from "./Header.vue";
import { defineComponent } from "vue";
export default defineComponent({
  name: "PlayerStats",
  props: {
    player: { type: Object, required: true },
    stats: Object,
  },
});
</script>
<style scoped>
table {
  margin-left: auto;
  margin-right: auto;
  display: table;
}

td {
  min-width: 30;
  text-align: right;
  font-family: Arial, Helvetica, sans-serif;
  font-variant-numeric: lining-nums;
}

@media (max-width: 480px) {
  td.notshown {
    display: none;
  }
}
</style>

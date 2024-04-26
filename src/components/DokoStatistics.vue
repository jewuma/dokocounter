<template>
  <div>
    <table>
      <tr>
        <th :key="key" v-for="key in head" :class="[
          key == 'Re %' ||
            key == 'Gew.' ||
            key == 'Soli' ||
            key == 'Soli gew.'
            ? 'notshown'
            : key,
        ]">
          {{ key }}
        </th>
      </tr>
      <tr :key="player.id" v-for="player in players">
        <PlayerStats :player="player" :stats="statistic[player.id]" />
      </tr>
    </table>
    <p>
      <DokoButton text="Zurück" color="blue" @click="$emit('back')" />
    </p>
  </div>
</template>
<script lang="ts">
import { defineComponent } from "vue";
import PlayerStats from "./PlayerStats.vue";
import DokoButton from "./DokoButton.vue";
import { backend, type player, type statistic } from "../backend";
const back = new backend();
export default defineComponent({
  name: "DokoStatistics",
  data() {
    return {
      players: [] as player[],
      statistic: [] as statistic[],
      head: [] as string[],
    };
  },
  components: {
    PlayerStats,
    DokoButton,
  },
  methods: {},

  async created() {
    this.players = await back.fetchPlayers();
    this.statistic = await back.fetchStatistics();
    this.head = [
      "Spieler",
      "Gespielt",
      "Re %",
      "Gew.",
      "Soli",
      "Soli gew.",
      "Ges-punkte",
      "Schnitt",
    ];
  },
});
</script>
<style scoped>
@media (max-width: 480px) {
  th.notshown {
    display: none;
  }
}

th {
  background-color: darkorange;
}
</style>

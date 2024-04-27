<template>
  <div>
    <DokoHeader text="Neue Liste" />
    <DokoButton @btn-click="toggleAddPlayer" :text="showAddPlayer ? 'Abbruch' : 'Neuer Spieler'"
      :color="showAddPlayer ? 'red' : 'green'" />
    <DokoButton v-if="!showAddPlayer" text="zurück" color="blue" @click="$emit('back')" />
    <DokoButton v-if="!showAddPlayer" text="Neue Liste starten" color="purple" @click="$emit('new-list')"
      :class="[startOK ? 'active' : '', 'save']" :disabled="startOK === false" />
    <AddPlayer v-show="showAddPlayer" @add-player="newPlayer" />

    <div :key="player.id" v-for="player in players">
      <DokoPlayer @toggle-active="toggleActive(player.id)" @delete-player="deletePlayer(player.id)"
        @dropped="droppedPlayer($event, player.id)" :player="player" />
    </div>
  </div>
</template>
<script lang="ts">
import { defineComponent } from "vue";
import DokoPlayer from "./DokoPlayer.vue";
import DokoHeader from "./DokoHeader.vue";
import DokoButton from "./DokoButton.vue";
import AddPlayer from "./AddPlayer.vue";
import { backend } from "../backend";
import type { player } from "../backend"
const back = new backend();
export default defineComponent({
  name: "DokoPlayers",
  components: {
    DokoPlayer,
    DokoHeader,
    DokoButton,
    AddPlayer,
  },
  data() {
    return {
      showAddPlayer: false,
      startOK: false,
      players: [] as player[],
    };
  },
  methods: {
    toggleAddPlayer(): void {
      this.showAddPlayer = !this.showAddPlayer;
    },
    async newPlayer(player: player) {
      const data: player = await back.newPlayer(player);
      this.players = [...this.players, data];
    },
    async deletePlayer(id: number) {
      if (confirm("Spieler löschen?")) {
        const deleted = await back.deletePlayer(id);
        deleted
          ? (this.players = this.players.filter((player) => player.id !== id))
          : alert("Error deleting player");
      }
    },
    async toggleActive(id: number) {
      const playerToToggle = this.players.find((player) => {
        return player.id === id;
      });
      const active = !playerToToggle?.active;
      const data = await back.setPLayerActive(id, active);
      this.players = this.players.map((player) =>
        player.id === id ? { ...player, active: data.active } : player,
      );
      this.checkStartable();
    },
    checkStartable() {
      let activePlayers = 0;
      this.players.forEach((player) => {
        if (player.active) {
          activePlayers++;
        }
      });
      if (activePlayers === 4 || activePlayers === 5) this.startOK = true;
      else this.startOK = false;
    },
    droppedPlayer(e: Event, playerId: number) {
      const playerId1 = Number(e);
      const index1: number = this.players.findIndex(
        (player) => player.id == playerId1,
      );
      const index2: number = this.players.findIndex(
        (player) => player.id == playerId,
      );
      const merkSort = this.players[index1].sort;
      this.players[index1].sort = this.players[index2].sort;
      this.players[index2].sort = merkSort;
      back.swapPlayers(Number(e), playerId);
      this.players.sort((a, b) => {
        if (a.sort < b.sort) {
          return -1;
        }
        if (a.sort > b.sort) {
          return 1;
        }
        return 0;
      });
    },
    async fetchPlayers() {
      return await back.fetchPlayers();
    },
    async fetchPlayer(id: number) {
      return back.fetchPlayer(id);
    },
  },
  async created() {
    this.players = await this.fetchPlayers();
    this.checkStartable();
  },
  emits: ["back", "new-list"],
});
</script>
<style scoped>
.save {
  cursor: not-allowed;
  opacity: 40%;
}

.active {
  opacity: 100;
  cursor: pointer;
}
</style>

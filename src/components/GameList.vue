<template>
  <table class="games">
    <tr>
      <th class="gameHead">Spiel</th>
      <th v-for="player in players" :key="player.id" class="gameHead">
        {{ player.name }}
      </th>
    </tr>
    <tr v-for="(game, index) in games" :key="game.gameId">
      <td class="gameTable">{{ index }}</td>
      <td v-for="player in players" :key="player.id" class="gameTable">
        {{
          game.players[
            game.players.findIndex(
              (singlePlayer) => singlePlayer.playerId === player.id,
            )
          ].points
        }}
      </td>
    </tr>
    <tr>
      <td class="gameHead">Summen</td>
      <td v-for="player in players" :key="player.id" class="gameHead">
        {{ sums[player.id] }}
      </td>
    </tr>
    <tr>
      <td class="gameHead">Spieler</td>
      <td v-for="player in players" :key="player.id" class="gameHead">
        {{ player.name }}
      </td>
    </tr>
  </table>
  <div class="poinbuttons">
    <button @click="$emit('back')" class="btn btn-block" style="background: blue">
      Zurück
    </button>
  </div>
</template>
<script lang="ts">
interface playersums {
  [key: number]: number;
}
import { defineComponent } from "vue";
import { type player, type game, backend } from "../backend";
const back = new backend();
export default defineComponent({
  data() {
    return {
      games: [] as game[],
      players: [] as player[],
      index: Number,
      sums: {} as playersums,
    };
  },
  async created() {
    this.refreshState();
  },
  emits: ["back"],
  methods: {
    async refreshState() {
      this.games = await back.fetchActiveList();
      Object.values(this.games).forEach((game) => {
        game.players.forEach((player) => {
          if (this.sums[player.playerId] === undefined)
            this.sums[player.playerId] = 0;
          this.sums[player.playerId] += player.points * 1;
        });
      });
      this.players = await back.fetchPlayers();
      this.players = this.players
        .filter((player) => player.active == true)
        .sort(
          (playerA, playerB) => playerA.sort-playerB.sort                                 //this.sums[playerA.id] - this.sums[playerB.id],
        );
    },
  },
});
</script>
<style scoped>
.games {
  width: 100%;
}

.gameHead {
  text-align: right;
  background-color: yellow;
  font-weight: bold;
}

.gameTable {
  text-align: right;
  background-color: azure;
}
</style>

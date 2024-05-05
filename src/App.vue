<template>
  <div class="container">
    <DokoMenu v-if="selected === 'menu'" @item-selected="changeMenuItem" />
    <DokoPlayers v-if="selected === 'playerList'" @back="changeMenuItem('menu')" @new-list="startNewList" />
    <DokoGame v-if="selected === 'game'" @back="changeMenuItem('menu')" />
    <GameList v-if="selected === 'gameList'" @back="changeMenuItem('menu')" />
    <DokoStatistics v-if="selected === 'statistics'" @back="changeMenuItem('menu')" />
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import DokoPlayers from "./components/DokoPlayers.vue";
import DokoMenu from "./components/DokoMenu.vue";
import DokoStatistics from "./components/DokoStatistics.vue";
import DokoGame from "./components/DokoGame.vue";
import GameList from "./components/GameList.vue";
import { backend } from "./backend";
import type { player } from "./backend";
const back = new backend();
export default defineComponent({
  name: "App",
  components: {
    DokoMenu,
    DokoPlayers,
    DokoStatistics,
    GameList,
    DokoGame,
  },
  players: [],
  host: "",
  data() {
    return {
      players: [] as player[],
      showAddPlayer: false as boolean,
      selected: "menu" as string,
    };
  },
  methods: {
    changeMenuItem(item: string) {
      this.selected = item;
    },
    async startNewList() {
      await back.startNewList();
      this.changeMenuItem("game");
    },
  },
  async created() {
    this.selected = "menu";
  },
});
</script>

<style>
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400&display=swap");

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: "Poppins", sans-serif;
}

.container {
  max-width: 500px;
  margin: 30px auto;
  overflow: auto;
  min-height: 300px;
  border: 1px solid steelblue;
  padding: 30px;
  border-radius: 5px;
}

.btn {
  display: inline-block;
  background: #000;
  color: #fff;
  border: none;
  padding: 10px 20px;
  margin: 5px;
  border-radius: 5px;
  cursor: pointer;
  text-decoration: none;
  font-size: 15px;
  font-family: inherit;
}

.btn:focus {
  outline: none;
}

.btn:active {
  transform: scale(0.98);
}

.btn-block {
  display: block;
  width: 100%;
}
</style>

<template>
  <div>
    <p>
      <DokoButton text="Neue Liste" color="blue" @click="select('playerList')" />
    </p>
    <p>
      <DokoButton text="Aktuelle Spielliste" color="brown" @click="select('gameList')" />
    </p>
    <p>
      <DokoButton text="Aktuelles Spiel" color="green" @click="select('game')" />
    </p>
    <p>
      <DokoButton text="Statistik" color="DeepPink" @click="select('statistics')" />
    </p>
  </div>
</template>
<script lang="ts">
import { defineComponent } from "vue";
//import Header from "./Header.vue";
import DokoButton from "./DokoButton.vue";
export default defineComponent({
  name: "DokoMenu",
  components: {
    //Header,
    DokoButton,
  },
  data: function () {
    return {
      selection: "menu",
    };
  },
  methods: {
    select(selection: string) {
      this.selection = selection;
      this.$emit("item-selected", selection);
    },
  },
});
</script>

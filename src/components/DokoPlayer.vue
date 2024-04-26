<template>
  <div @click="$emit('toggle-active', player?.id)" draggable="true" @drop="dropped" @dragover.prevent @dragenter.prevent
    @dragstart="startDrag($event, player!.id)" :class="[player?.active ? 'active' : '', 'player']">
    <h3>
      {{ player?.name }} {{ player?.surname }}
      <i @click="$emit('delete-player', player?.id)" class="fas fa-times"></i>
    </h3>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import type { player } from "@/backend";
export default defineComponent({
  name: "DokoPlayer",
  props: {
    player: Object as () => player,
  },
  emits: ["delete-player", "toggle-active", "dropped"],
  methods: {
    dropped(e: DragEvent) {
      this.$emit("dropped", e.dataTransfer?.getData("playerId"));
    },
    startDrag(evt: DragEvent, playerId: number) {
      if (evt.dataTransfer) {
        evt.dataTransfer.dropEffect = "move";
        evt.dataTransfer.effectAllowed = "move";
        evt.dataTransfer.setData("playerId", playerId.toString());
      }
    },
  },
});
</script>
<style scoped>
.fas {
  color: red;
}

.player {
  background: #f4f4f4;
  margin: 5px;
  padding: 10px 20px;
  cursor: pointer;
}

.player.active {
  border-left: 5px solid green;
  opacity: 100;
}

.player h3 {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
</style>

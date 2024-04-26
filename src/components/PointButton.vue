<template>
  <button :class="[buttonData?.state ? 'active' : '', buttonData?.party]" @click="pclick(buttonId)">
    {{ buttonData?.text }}
  </button>
</template>
<script lang="ts">
import { defineComponent } from "vue";
import type { buttonState } from "./DokoGame.vue";
export default defineComponent({
  name: "PointButton",
  props: {
    buttonId: Number,
    buttonData: Object as () => buttonState,
  },
  methods: {
    pclick(buttonId: number | undefined) {
      this.$emit("pointbutton-clicked", buttonId);
    },
  },
});
</script>
<style scoped>
button {
  color: azure;
  width: 10%;
  border: none;
  padding-top: 10px;
  padding-bottom: 10px;
  opacity: 0.5;
  cursor: pointer;
}

.re {
  background-color: red;
}

.ko {
  background-color: green;
}

.ne {
  background-color: gray;
}

.active {
  opacity: 1;
}
</style>

<template>
  <button @click="onClick()" @mousedown="startHold()" @mouseup="stopHold()" :style="{ background: color }" class="btn btn-block">
    <span v-if="textl !== ''" style="float: left">{{ textl }}</span>
    <span style="clear: both">{{ text }}</span>
    <span v-if="textr !== ''" style="float: right">{{ textr }}</span>
  </button>
</template>

<script lang="ts">
import { defineComponent } from "vue";
export default defineComponent({
  name: "DokoButton",
  props: {
    textl: String,
    text: String,
    textr: String,
    color: String,
  },
  data() {return {
    mTimer: 0 as number
  }
},

  methods: {
    onClick(): void {
      this.$emit("btn-click");
    },
    startHold(): void {
      this.mTimer=setTimeout(()=>{
        this.$emit("double-click");
      },500);
      
    },
    stopHold() {
      if (this.mTimer!==0) {
        clearTimeout(this.mTimer);
      }
    }
  },
});
</script>

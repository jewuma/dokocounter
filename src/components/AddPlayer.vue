<template>
  <form @submit="onSubmit" class="add-form">
    <div class="form-control">
      <label>Vorname</label>
      <input type="text" v-model="name" name="name" placeholder="Vorname" />
    </div>
    <div class="form-control">
      <label>Name</label>
      <input type="text" v-model="surname" name="surname" placeholder="Nachname" />
    </div>
    <div class="form-control form-control-check">
      <label>Aktiv in diesem Spiel</label>
      <input type="checkbox" v-model="active" name="active" />
    </div>

    <input type="submit" value="Spieler speichern" class="btn btn-block" />
  </form>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import type { player } from "../backend";
export default defineComponent({
  name: "AddPlayer",
  data(): player {
    return {
      id: 0 as number,
      name: "" as string,
      surname: "" as string,
      active: false as boolean,
      sort: 0 as number,
    };
  },
  methods: {
    onSubmit(e: Event): void {
      e.preventDefault();
      if (!this.name || !this.surname) {
        alert("Bitte Vorname und Name eingeben");
        return;
      }
      const newPlayer = {
        name: this.name,
        surname: this.surname,
        active: this.active,
      };
      this.$emit("add-player", newPlayer);
      this.name = "";
      this.surname = "";
      this.active = false;
    },
  },
});
</script>

<style scoped>
.add-form {
  margin-bottom: 40px;
}

.form-control {
  margin: 20px 0;
}

.form-control label {
  display: block;
}

.form-control input {
  width: 100%;
  height: 40px;
  margin: 5px;
  padding: 3px 7px;
  font-size: 17px;
}

.form-control-check {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.form-control-check label {
  flex: 1;
}

.form-control-check input {
  flex: 2;
  height: 20px;
}
</style>

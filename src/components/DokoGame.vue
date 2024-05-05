<template>
  <div>
    <h3 style="padding-top: 0">Spiel Nr. {{ gameNumber }}</h3>
    <DokoButton :key="key" v-for="(player, key) in playerStates" :text="player.name" :textr="player.score.toString()"
      :textl="player.lastScore.toString()" :color="getPlayerColor(player.party)" @click="toggleParty(key)"
      @double-click="setGeber(key)" />
    <h3>Ansagen</h3>
    <div class="pointbuttons">
      <PointButton :buttonData="button" :buttonId="index" :key="index" v-for="(button, index) in PronounceButtons"
        @pointbutton-clicked="pronounceClick" />
    </div>
    <h3>Ergebnis</h3>
    <div class="pointbuttons">
      <PointButton :buttonData="button" :buttonId="index" :key="index" v-for="(button, index) in ResultButtons"
        @pointbutton-clicked="resultClick" />
    </div>
    <h3>Zusatzpunkte</h3>
    <div class="pointbuttons">
      <button :class="{
        extrapointbutton: true,
        passive: button.state == false,
        re: button.party == 're',
        ko: button.party == 'ko',
        ne: button.party == 'ne',
      }" :key="index" v-for="(button, index) in ExtrapointButtons" @click="extrapointClick(index)">
        {{ button.text }}
      </button>
    </div>
    <div class="poinbuttons">
      <button class="back" @click="$emit('back')">Zurück</button>
      <button @click="saveLastGame" :class="[saveOK ? 'active' : '', 'saveLast']">
        Letztes Spiel ändern
      </button>
      <button @click="saveGame" :class="[saveOK ? 'active' : '', 'save']">
        Speichern
      </button>
    </div>
  </div>
</template>
<script lang="ts">
import { defineComponent } from "vue";
import { backend, kontraRe } from "../backend";
import type { player, playerState, game } from "../backend"
import DokoButton from "./DokoButton.vue";
import PointButton from "./PointButton.vue";
const back = new backend();

export interface buttonState {
  text: string;
  party: string;
  state: boolean;
  value: number;
}
export default defineComponent({
  name: "DokoGame",
  data() {
    return {
      kontraRe,
      games: [] as game[],
      players: [] as player[],
      PronounceButtons: [] as buttonState[],
      ResultButtons: [] as buttonState[],
      ExtrapointButtons: [] as buttonState[],
      playerStates: [] as playerState[],
      saveOK: false as boolean,
      result: 0 as number,
      extrapoints: 0 as number,
      changeLastGame: false as boolean,
      gameNumber: 1 as number,
    };
  },
  async created() {
    this.refreshState();
  },
  components: {
    DokoButton,
    PointButton,
  },
  methods: {
    async refreshState() {
      this.games = await back.fetchActiveList();
      this.players = await back.fetchActivePlayers();
      this.PronounceButtons = [
        { text: "Sw", party: "re", state: false, value: -5 },
        { text: "U3", party: "re", state: false, value: -4 },
        { text: "U6", party: "re", state: false, value: -3 },
        { text: "U9", party: "re", state: false, value: -2 },
        { text: "Re", party: "re", state: false, value: -1 },
        { text: "Ko", party: "ko", state: false, value: 1 },
        { text: "U9", party: "ko", state: false, value: 2 },
        { text: "U6", party: "ko", state: false, value: 3 },
        { text: "U3", party: "ko", state: false, value: 4 },
        { text: "Sw", party: "ko", state: false, value: 5 },
      ];
      this.ResultButtons = [
        { text: "Sw", party: "re", state: false, value: -4 },
        { text: "U3", party: "re", state: false, value: -3 },
        { text: "U6", party: "re", state: false, value: -2 },
        { text: "U9", party: "re", state: false, value: -1 },
        { text: "Re", party: "re", state: true, value: 0 },
        { text: "Ko", party: "ko", state: false, value: 1 },
        { text: "U9", party: "ko", state: false, value: 2 },
        { text: "U6", party: "ko", state: false, value: 3 },
        { text: "U3", party: "ko", state: false, value: 4 },
        { text: "Sw", party: "ko", state: false, value: 5 },
      ];
      this.ExtrapointButtons = [
        { text: "5", party: "re", state: false, value: -5 },
        { text: "4", party: "re", state: false, value: -4 },
        { text: "3", party: "re", state: false, value: -3 },
        { text: "2", party: "re", state: false, value: -2 },
        { text: "1", party: "re", state: false, value: -1 },
        { text: "0", party: "ne", state: true, value: 0 },
        { text: "1", party: "ko", state: false, value: 1 },
        { text: "2", party: "ko", state: false, value: 2 },
        { text: "3", party: "ko", state: false, value: 3 },
        { text: "4", party: "ko", state: false, value: 4 },
        { text: "5", party: "ko", state: false, value: 5 },
      ];
      let gibt = 0;
      this.gameNumber = 1;
      this.playerStates = [];
      const playerCount = this.players.length
      Object.values(this.games).forEach((singleGame) => {
        let reCount = 0;
        Object.values(singleGame.players).forEach((singlePlayer) => {
          const playerId = singlePlayer.playerId;
          const singleScore = singlePlayer.points;
          if (singlePlayer.party == 0) {
            reCount++;
          }
          let stateIndex = this.playerStates.findIndex(
            (player) => player.playerId === playerId,
          );
          if (stateIndex == -1) {
            const pIndex = this.players.findIndex(
              (player) => player.id === playerId,
            );
            this.playerStates.push({
              playerId,
              name: this.players[pIndex].name,
              score: 0,
              party: kontraRe.Kontra,
              lastScore: "0",
              sort: this.players[pIndex].sort,
            });
            stateIndex = this.playerStates.length - 1;
          }
          this.playerStates[stateIndex].lastScore =
            singleScore > 0 ? "+" + singleScore : singleScore.toString();
          this.playerStates[stateIndex].score += +singleScore;
        });
        if (reCount === 2) {
          gibt++;
          if (gibt === playerCount) {
            gibt = 0;
          }
        }
        if (reCount > 0) this.gameNumber++;
      });
      this.playerStates.sort((a, b) => {
        return a.sort - b.sort;
      });
      for (let i = 0; i < playerCount; i++) {
        if (i == gibt) {
          this.playerStates[i].name += " (G)";
          if (playerCount === 5) this.playerStates[i].party = kontraRe.Geber
        }
      }
      this.saveOK = false;
    },
    toggleParty(playerId: number) {
      if (this.playerStates[playerId].party !== kontraRe.Geber) {
        if (this.playerStates[playerId].party === kontraRe.Kontra) {
          this.playerStates[playerId].party = kontraRe.Re;
        } else {
          this.playerStates[playerId].party = kontraRe.Kontra;
        }
        let reCount = 0;
        Object.values(this.playerStates).forEach((player) => {
          if (player.party === kontraRe.Re) {
            reCount++;
          }
        });
        this.saveOK = false;
        if (reCount === 1 || reCount === 2) {
          this.saveOK = true;
        }
      }
    },
    pronounceClick(buttonId: number) {
      this.PronounceButtons[buttonId].state =
        !this.PronounceButtons[buttonId].state;
      const party = this.PronounceButtons[buttonId].party;
      if (this.PronounceButtons[buttonId].state === true) {
        this.PronounceButtons = this.PronounceButtons.map((button, index) => {
          if (index !== buttonId && button.party === party) {
            button.state = false;
          }
          return button;
        });
      }
    },
    resultClick(buttonId: number) {
      if (this.ResultButtons[buttonId].state == false) {
        this.ResultButtons[buttonId].state = true;
        this.result = this.ResultButtons[buttonId].value;
        this.ResultButtons = this.ResultButtons.map((button, index) => {
          if (index !== buttonId) {
            button.state = false;
          }
          return button;
        });
      }
    },
    extrapointClick(buttonId: number) {
      if (this.ExtrapointButtons[buttonId].state == false) {
        this.ExtrapointButtons[buttonId].state = true;
        this.extrapoints = this.ExtrapointButtons[buttonId].value;
        this.ExtrapointButtons = this.ExtrapointButtons.map((button, index) => {
          if (index !== buttonId) {
            button.state = false;
          }
          return button;
        });
      }
    },
    getPlayerColor(party: kontraRe) {
      if (party === kontraRe.Kontra) {
        return 'green'
      } else if (party === kontraRe.Re) {
        return 'red'
      } else {
        return 'grey'
      }
    },
    async saveGame() {
      if (this.saveOK) {
        //result=-4:Re schwarz,-3=Re3 -2=Re6 -1=Re9 0=re Gewonnen, 1=Kontra gewonnen, 5=Kontra schwarz gewonnen
        //extrapoints -5 bis -1 =Re-Extrapunkte, 1 bis 5 = Kontra-Extrapunkte
        let kontraAnsage = 0;
        let reAnsage = 0;
        this.PronounceButtons.forEach((button) => {
          if (button.state === true) {
            if (button.value < 0) reAnsage = button.value;
            else kontraAnsage = button.value;
          }
        });
        const gameObject = {
          playerStates: this.playerStates,
          kontraAnsage,
          reAnsage,
          result: this.result,
          extrapoints: this.extrapoints,
          changeLastGame: this.changeLastGame,
        };
        await back.saveGame(gameObject);
        this.result = 0;
        this.extrapoints = 0;
        this.playerStates.map((playerState) => {
          playerState.party = kontraRe.Kontra;
        });
        this.refreshState();
      }
    },
    saveLastGame() {
      this.changeLastGame = true;
      this.saveGame();
      this.changeLastGame = false;
    },
    setGeber(playerId: number) {
      if (this.players.length===5) {

        this.playerStates.forEach((playerState,index)=>{
          if (playerState.party===kontraRe.Geber && playerState.playerId!==playerId) {
            this.playerStates[index].party=kontraRe.Kontra;
            this.playerStates[index].name=this.playerStates[index].name.slice(0,-4);
          }
        });
        this.playerStates[playerId].party=kontraRe.Geber;
        this.playerStates[playerId].name=this.playerStates[playerId].name+" (G)";
      }
    }
  },
});
</script>
<style scoped>
.pointbuttons {
  text-align: center;
  width: 100%;
  margin: 5px;
}

.pointInput {
  clear: both;
  margin-left: 15px;
  width: 95%;
}

.ranges {
  width: 100%;
}

h3 {
  text-align: center;
  padding-top: 20px;
}

.zp {
  float: left;
  width: 9%;
  border: none;
  padding-top: 10px;
  padding-bottom: 10px;
}

.back {
  cursor: pointer;
  margin-top: 10px;
  border-radius: 5px;
  float: left;
  width: 31%;
  border: none;
  height: 40px;
  background-color: blue;
  color: azure;
}

.save {
  cursor: not-allowed;
  margin-top: 10px;
  border-radius: 5px;
  float: right;
  width: 31%;
  border: none;
  height: 40px;
  background-color: darkorange;
  color: azure;
  opacity: 0.4;
}

.saveLast {
  margin-left: 10px;
  margin-right: 10px;
  cursor: not-allowed;
  margin-top: 10px;
  border-radius: 5px;
  width: 31%;
  border: none;
  height: 40px;
  background-color: indigo;
  color: azure;
  opacity: 0.4;
}

.active {
  opacity: 1;
  cursor: pointer;
}

.passive {
  opacity: 0.5;
}

.extrapointbutton {
  color: azure;
  width: 9%;
  border: none;
  padding-top: 10px;
  padding-bottom: 10px;
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
</style>

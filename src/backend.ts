export interface player {
  id: number;
  name: string;
  surname: string;
  active: boolean;
  sort: number;
}
export interface playerState {
  playerId: number;
  name: string;
  score: number;
  party: kontraRe;
  lastScore: string;
  sort: number;
}
export interface gameState {
  playerId: number;
  points: number;
  party: kontraRe;
}

export interface statistic {
  gamesplayed: number;
  gamesre: number;
  gameskontra: number;
  gameswon: number;
  gamessolo: number;
  solowon: number;
  totalpoints: number;
  averagepoints: number;
}
export interface game {
  gameId: number;
  players: gameState[];
}
export interface saveGame {
  playerStates: playerState[];
  kontraAnsage: number;
  reAnsage: number;
  result: number;
  extrapoints: number;
  changeLastGame: boolean;
}
export enum kontraRe {
  Re,
  Kontra,
}
const HOST = import.meta.env.VITE_BACKEND as string;
export class backend {
  public async newPlayer(player: player): Promise<player> {
    const res = await fetch(`${HOST}players.php`, {
      method: "POST",
      headers: {
        "Content-type": "application/json",
      },
      body: JSON.stringify(player),
    });
    const data = await res.json();
    return data;
  }
  async deletePlayer(id: number): Promise<boolean> {
    const res = await fetch(`${HOST}players.php/${id}`, {
      method: "DELETE",
    });
    return res.status === 200;
  }
  async setPLayerActive(id: number, active: boolean): Promise<player> {
    const playerToToggle = await this.fetchPlayer(id);
    playerToToggle.active = active;
    const res = await fetch(`${HOST}players.php/${id}`, {
      method: "PUT",
      headers: {
        "Content-type": "application/json",
      },
      body: JSON.stringify(playerToToggle),
    });
    return await res.json();
  }
  async fetchPlayers(): Promise<player[]> {
    const res = await fetch(`${HOST}players.php`);
    const data = await res.json();
    return data;
  }
  async fetchActivePlayers(): Promise<player[]> {
    const res = await fetch(`${HOST}players.php/activeOnly`);
    const data = await res.json();
    return data;
  }
  async fetchPlayer(id: number): Promise<player> {
    const res = await fetch(`${HOST}players.php/${id}`);
    const data = await res.json();
    return data;
  }
  async fetchStatistics(): Promise<statistic[]> {
    const res = await fetch(`${HOST}statistics.php`);
    const data = await res.json();
    return data;
  }
  async fetchActiveList(): Promise<game[]> {
    const res = await fetch(`${HOST}games.php`);
    const data = await res.json();
    return data;
  }
  async swapPlayers(playerId1: number, playerId2: number): Promise<boolean> {
    const res = await fetch(
      `${HOST}players.php/swapSorts;` + playerId1 + ";" + playerId2,
    );
    if (res.status == 200) return true;
    else return false;
  }
  async saveGame(gameObject: saveGame): Promise<game[]> {
    const body = JSON.stringify(gameObject);
    const res = await fetch(`${HOST}games.php`, {
      method: "POST",
      headers: {
        "Content-type": "application/json",
      },
      body,
    });
    const newStates = await res.json();
    return newStates;
  }
  async startNewList(): Promise<void> {
    await fetch(`${HOST}games.php/startgame`);
    //const data = await res.json();
    return;
  }
}

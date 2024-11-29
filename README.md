# QUARTO BOARD GAME PROJECT

This project was implemented as part of the course *Development of Web Systems and Applications* at the Department of Information and Electronic Engineering, International Hellenic University (IHU).

---

## Demo Page
You can visit the page:  [Quarto Demo](https://users.iee.ihu.gr/~it174881/ADISE21_174881/)  
to play the latest version of the game.  

**Note**: You must click the "Start" button before logging into the game to begin.  
**Caution**: The "Start" button should only be clicked once and from one browser. Afterward, players can log in and join the game.

<img src="/quarto_demo.gif">

---

## Installation

### Requirements
* Apache2  
* MySQL Server  
* PHP  

### Installation Instructions
1. Clone the project into a directory:  
   ```bash
   $ git clone https://github.com/iee-ihu-gr-course1941/ADISE21_174881.git
   ```
2. Ensure the directory is accessible by the Apache server. You might need to set the following permissions.

3. Create a MySQL database named `adise_quarto_db` and import the data from the `schema.sql` file.

4. Create the file `lib/config_local.php` with the following content:
   ```php
   <?php
   $DB_PASS = 'your_password';
   $DB_USER = 'your_username';
   ?>
   ```

---

## Game Description

**Quarto** is a board game for two players, invented by Swiss mathematician Blaise Müller.

### Gameplay:
The game is played on a 4×4 board. There are 16 unique pieces, each with four binary attributes:  
* **Height**: Tall or Short  
* **Color**: Light or Dark (or any two chosen colors)  
* **Shape**: Square or Round  
* **Top**: Hollow or Solid  

Players take turns selecting a piece for their opponent, who must place it on the board. A player wins by forming a horizontal, vertical, or diagonal row of four pieces that share a common attribute (e.g., all tall, all round, etc.).

---

## Contributors
- **Moisidis Georgios 174881**: Jquery, PHP API, MySQL design

---

## API Documentation

### Methods

#### **Board**

##### Read Board
```
GET /board/
```
Returns the current [Board](#board) state.

##### Initialize Board
```
POST /board/
```
Initializes the board, resetting the game state. Returns the [Board](#board) state.

#### **Piece**

##### Select Piece for Opponent
```
PUT /board/piece/pick/
```
**JSON Data**:  
| Field      | Description             | Required |
|------------|-------------------------|----------|
| `piece_id` | Unique ID of the piece  | Yes      |

Selects a piece that the opponent must place. Updates the game status for `current_piece`.

##### Get Unused Pieces
```
GET /board/piece/pick
```
Returns data from [Pieces](#pieces), listing `piece_id` for pieces that can still be played.

##### Place Piece on Board
```
PUT /board/piece/place/
```
**JSON Data**:  
| Field      | Description           | Required |
|------------|-----------------------|----------|
| `x`        | X coordinate on board | Yes      |
| `y`        | Y coordinate on board | Yes      |
| `piece_id` | Unique ID of the piece| Yes      |

#### **Player**

##### Get Player Details
```
GET /players/
```
Returns the details of all players in the game.

##### Get Player Login
```
GET /players/login/
```
Returns the details of the logged-in player.

##### Set Player Details
```
PUT /players/login/
```
**JSON Data**:  
| Field      | Description       | Required |
|------------|-------------------|----------|
| `username` | Player's username | Yes      |

Returns the player's details and a token. The token must be used by the player for all actions during the game.

#### **Status**

##### Get Game Status
```
GET /status/
```
Returns the [Game_status](#game_status).

---

## Entities

### **Board**
The board is a grid where each square has the following attributes:  

| Attribute | Description                   | Values   |
|-----------|-------------------------------|----------|
| `x`       | X coordinate of the square    | 1..4     |
| `y`       | Y coordinate of the square    | 1..4     |
| `piece`   | Piece placed on the square    | 1..16 or null |

### **Pieces**
Each piece has the following attributes:  

| Attribute   | Description                           | Values       |
|-------------|---------------------------------------|--------------|
| `piece_id`  | Unique ID                             | 1..16        |
| `is_light`  | Whether the piece is light or dark    | TRUE, FALSE  |
| `is_round`  | Whether the piece is round or square  | TRUE, FALSE  |
| `is_short`  | Whether the piece is short or tall    | TRUE, FALSE  |
| `is_solid`  | Whether the top is solid or hollow    | TRUE, FALSE  |
| `available` | Whether the piece can be played       | TRUE, FALSE  |

### **Players**
Each player has the following attributes:  

| Attribute     | Description                          | Values        |
|---------------|--------------------------------------|---------------|
| `player_id`   | Unique ID                            | INT INCREMENT |
| `username`    | Username of the player               | String        |
| `token`       | The player's hidden token            | HEX           |
| `role`        | The current action for the player    | 'pick', 'place' |
| `last_action` | Timestamp of the player's last move  | timestamp     |

### **Game_status**
The game status includes the following attributes:  

| Attribute       | Description                                      | Values                              |
|------------------|--------------------------------------------------|-------------------------------------|
| `status`        | Current game state                               | 'not active', 'initialized', 'started', 'ended', 'aborted' |
| `p_turn`        | Token of the player whose turn it is             | HEX                                |
| `current_piece` | ID of the currently selected piece               | 1..16                              |
| `result`        | Indicates the outcome of the game (win/draw)     | 'W', 'D', null                     |
| `win_direction` | Direction of the winning row on the board        | 'not set', 'vertical+y', 'horizontal+x', 'left diagonal', 'right diagonal' (x, y = last placement coordinates) |
| `last_change`   | Timestamp of the last game state update          | timestamp                          |

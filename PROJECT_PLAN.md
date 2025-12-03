# Project Plan: "Jass Online" - From Zero to Hero

**Role:** Code Mentor
**Goal:** Build a multiplayer online Jass game.
**Timeline:** 3 Months (12 Weeks).
**Stack:** Laravel (Backend), Vue.js (Frontend via Inertia.js), Laravel Reverb (Real-time).

---

## 🧠 Mentor's Philosophy
> "The temptation with games is to start with the graphics. **Don't.** A card game is 90% logic and state management. If your backend doesn't know who holds the 'Nell' or if an 'Undertrump' is allowed, the prettiest UI won't save you. We build the brain first, then the body."

---

## 🛠 Recommended Tech Stack
Since you are learning, this stack strikes the perfect balance between "Modern/Interactive" and "Developer Experience".

*   **Backend:** **Laravel 11**. It is the gold standard for PHP development.
*   **Frontend:** **Vue.js 3** using **Inertia.js**.
    *   *Why?* Inertia allows you to build a modern Single Page App (SPA) without building a separate API. You write standard Laravel controllers and return Vue components. It feels like magic.
*   **Real-time:** **Laravel Reverb**.
    *   *Why?* It's the new, native WebSocket server for Laravel (released 2024). It handles the live updates (e.g., "Player A played a card") incredibly well.
*   **Database:** MySQL or PostgreSQL.
*   **Testing:** PHPUnit / Pest (We will use TDD significantly).

---

## 📅 Month 1: The Engine (Backend & Logic)
*Focus: Logic, Unit Testing, Data Structure. No UI yet.*

### Week 1: Setup & Architecture XXXX
1.  **Install Laravel**: Set up a fresh project via `laravel new jassen --jet` (Jetstream gives you Login/Registration out of the box). Choose **Inertia** + **Vue**.
2.  **Database Schema**: Design your tables.
    *   `users`: (Standard auth).
    *   `games`: `id`, `status` (waiting, playing, finished), `trump`, `current_player_id`.
    *   `game_states` or `hands`: How do you store who has what cards? JSON columns are often easier for game state than normalized tables for every single card.
3.  **Card Representation**: Decide how to store cards.
    *   Suggestion: Simple strings (`'h-a'` for Heart Ace, `'s-6'` for Shield 6) or a Value Object Class.

### Week 2: The Deck & Dealing (TDD Introduction)
*Do not create a controller yet. Create a Service class `GameService`.*
1.  **Test First**: Write a test that asserts a deck has 36 cards.
2.  **Shuffling**: Implement shuffling logic.
3.  **Dealing**: Write logic to distribute 9 cards to 4 players.
4.  **Trump Selection**: Logic to store/select the trump color.

### Week 3: Rule Validation (The Hard Part)
*Jass has tricky rules (Zwang, Undertrump).*
1.  **Validation Logic**: Create a `RuleEngine` class.
    *   Input: `CurrentGameState`, `CardToPlay`, `Player`.
    *   Output: `Boolean` (Allowed/Denied).
2.  **Edge Cases**: Write unit tests for:
    *   Player must follow suit.
    *   Player can play Trump if they can't follow suit.
    *   **Undertrump**: Player cannot play a lower trump if another player already played a higher trump (unless they have *only* trumps).
    *   *Mentor Tip:* This is where you will spend the most time. If you get this right, the rest is easy.

### Week 4: Scoring & Round Management
1.  **Stich (Trick) Logic**: Who won the trick?
    *   Compare 4 cards based on the current Trump.
    *   Assign points to the winner.
2.  **Game Flow**:
    *   Update `current_player_id` to the next player.
    *   Detect end of round (9 tricks).
    *   Detect end of game (Target score reached).

---

## ⚡ Month 2: The Interface (Frontend & Real-time)
*Focus: Connecting the logic to the screen.*

### Week 5: Basic UI & State Visualization
1.  **Inertia Setup**: Create a `Game/Show.vue` page.
2.  **Render State**: Pass the Game State from Laravel to Vue.
    *   Display the 9 cards in your hand.
    *   Display the table (cards played so far).
    *   *Design:* Keep it ugly. Use simple `div` boxes with text "Heart Ace". Don't do graphics yet.

### Week 6: Actions & API
1.  **Play Card Route**: Create `POST /game/{id}/play`.
2.  **Controller Logic**:
    *   Receive card selection.
    *   Call your `RuleEngine` to validate.
    *   Update Game State.
    *   Return new state.
3.  **Feedback**: Show error messages if the move is invalid (e.g., "You must follow suit!").

### Week 7: Going Live (WebSockets)
*Currently, you have to refresh the page to see if opponent played. Let's fix that.*
* **Decision Point:**
    *   **Option A (Easy/Shared Hosting):** Use **Pusher.com** (External Service). Easiest to set up, free for small apps. Works on shared hosting (like Cyon).
    *   **Option B (Advanced/VPS):** Use **Laravel Reverb** (Self-Hosted). Free unlimited connections, but requires a VPS (DigitalOcean, Hetzner). **Does not work on standard shared hosting.**
1.  **Configuration**: Set up `BROADCAST_DRIVER=pusher` (or `reverb`).
2.  **Events**: Create `CardPlayed` event.
3.  **Broadcasting**: When a player moves, broadcast the event to the specific Game Channel.
4.  **Frontend Listening**: Vue component listens for the event and updates the local state automatically without refresh.

### Week 8: UI Polish & UX
1.  **Assets**: Find or buy SVG assets for Swiss Jass cards.
2.  **Animations**: Use **Vue UseMotion** or standard CSS transitions.
    *   Animate the card flying from hand to table.
3.  **Responsiveness**: Make sure the cards fit on a mobile screen (Jass is complex on mobile, landscape mode recommended).

---

## 🤝 Month 3: Community & Meta
*Focus: Making it a "Platform".*

### Week 9: The Lobby System
1.  **Game Creation**: "Create Private Game" vs "Public Game".
2.  **Matchmaking**: List of open games.
3.  **Join Codes**: Share a 4-digit code to invite friends.

### Week 10: User Profiles & Stats
1.  **Friends List**: Simple Many-to-Many relationship on Users.
2.  **History**: Save completed games to database.
3.  **Stats**: Calculate "Win Rate", "Total Points", etc.

### Week 11: Refactoring & Performance
1.  **Optimization**: Ensure the database isn't queried 100 times per move.
2.  **Security**: Ensure Player A cannot see Player B's cards by inspecting the network requests (Backend must filter the state sent to frontend!).
3.  **Bug Bash**: Play with friends, find bugs, fix them.

### Week 12: Deployment
1.  **Server**: Set up a DigitalOcean Droplet or AWS EC2.
2.  **Provisioning**: Use **Laravel Forge** (paid but easiest) or learn to set up Nginx/PHP manually.
3.  **SSL**: HTTPS is required for secure WebSockets.

---

## 💡 Mentor's "Gotchas" (Common Pitfalls)

1.  **Trusting the Client**: Never trust the frontend. Even if you hide the "Play" button, a user can send a POST request manually. Always validate moves on the backend `RuleEngine`.
2.  **State Hiding**: When sending the "Game State" to the frontend, make sure you **only** send the logged-in user's cards. If you send everyone's cards and just "hide" them with CSS, tech-savvy users will cheat by looking at the JSON response.
3.  **Race Conditions**: What if two players play at the *exact* same millisecond? Use Database Transactions or Atomic Locks (`Cache::lock()`) when processing a turn.

## 📚 Resources for You
*   **Laracasts**: Look for "Inertia" and "Reverb" courses.
*   **Jass Rules**: Keep the Wikipedia article open.
*   **Vue.js Docs**: specifically the "Reactivity" section.

---

**Ready to start?** Your first task is initializing the Laravel project!


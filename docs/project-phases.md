# NativePHP Quiz App — Project Phases

---

## Phase 1: Project Setup & Dependencies

### Phase 1.1: Install Livewire 4
- Install `livewire/livewire` via Composer
- Publish Livewire config if needed
- **Status:** DONE

**Tests:**
- Feature test: Livewire scripts are present in the base layout

### Phase 1.2: Create Base Layout
- Create `resources/views/layouts/app.blade.php` (Livewire 4 layout namespace)
- Mobile-friendly viewport, Tailwind styling
- HomePage Livewire component with route
- **Status:** DONE

**Tests:**
- Feature test: GET `/` returns 200 and contains the app layout shell

---

## Phase 2: Database Schema & Models

### Phase 2.1: Age Groups Migration & Model
- Create `age_groups` migration (id, code, label, min_age, max_age, sort_order, is_active, timestamps)
- Create `AgeGroup` model with `movies()` hasMany relationship
- Create `AgeGroupFactory`
- **Status:** NOT STARTED

**Tests:**
- Feature test: AgeGroup can be created with factory
- Feature test: AgeGroup has many Movies relationship
- Feature test: Scope `active` returns only `is_active = true` records

### Phase 2.2: Movies Migration & Model
- Create `movies` migration (id, age_group_id FK, title, slug, release_year, poster_path, description, sort_order, is_active, timestamps)
- Create `Movie` model with `ageGroup()` belongsTo and `questions()` hasMany
- Create `MovieFactory`
- **Status:** NOT STARTED

**Tests:**
- Feature test: Movie can be created with factory
- Feature test: Movie belongs to AgeGroup
- Feature test: Movie has many Questions
- Feature test: Scope `active` returns only `is_active = true` records

### Phase 2.3: Questions Migration & Model
- Create `questions` migration (id, movie_id FK, prompt, difficulty, kind, explanation, is_active, timestamps)
- Create `Question` model with `movie()` belongsTo and `choices()` hasMany
- Create `QuestionFactory`
- **Status:** NOT STARTED

**Tests:**
- Feature test: Question can be created with factory
- Feature test: Question belongs to Movie
- Feature test: Question has many QuestionChoices
- Feature test: Scope `active` returns only `is_active = true` records

### Phase 2.4: Question Choices Migration & Model
- Create `question_choices` migration (id, question_id FK, label, text, is_correct, sort_order, timestamps)
- Unique constraint on (question_id, label)
- Create `QuestionChoice` model with `question()` belongsTo
- Create `QuestionChoiceFactory`
- **Status:** NOT STARTED

**Tests:**
- Feature test: QuestionChoice can be created with factory
- Feature test: QuestionChoice belongs to Question
- Feature test: Unique constraint on (question_id, label) is enforced
- Feature test: `correctAnswer` scope returns the correct choice for a question

### Phase 2.5: Quiz Sessions Migration & Model
- Create `quiz_sessions` migration (id UUID, movie_id FK, age_group_id FK, question_count, correct_count, started_at, completed_at, duration_seconds, question_ids JSON, timestamps)
- Create `QuizSession` model with `movie()`, `ageGroup()`, `answers()` relationships
- Create `QuizSessionFactory`
- **Status:** NOT STARTED

**Tests:**
- Feature test: QuizSession can be created with factory
- Feature test: QuizSession belongs to Movie and AgeGroup
- Feature test: QuizSession has many QuizAnswers
- Feature test: `question_ids` is cast to array
- Feature test: `completed_at` and `duration_seconds` are nullable on creation

### Phase 2.6: Quiz Answers Migration & Model
- Create `quiz_answers` migration (id, quiz_session_id FK, question_id FK, selected_choice_id FK, is_correct, answered_at, time_spent_seconds, timestamps)
- Create `QuizAnswer` model with `quizSession()`, `question()`, `selectedChoice()` relationships
- Create `QuizAnswerFactory`
- **Status:** NOT STARTED

**Tests:**
- Feature test: QuizAnswer can be created with factory
- Feature test: QuizAnswer belongs to QuizSession, Question, and QuestionChoice

### Phase 2.7: User Settings Migration & Model
- Create `user_settings` migration (id, key unique, value, timestamps)
- Create `UserSetting` model with static helper methods (`get($key)`, `set($key, $value)`)
- **Status:** NOT STARTED

**Tests:**
- Feature test: UserSetting can store and retrieve a key-value pair
- Feature test: UserSetting `set()` updates existing key instead of duplicating
- Feature test: UserSetting `get()` returns null for non-existent key

---

## Phase 3: Seed Data

### Phase 3.1: Create JSON Seed Data Files
- Create `database/seeders/data/age_groups.json`
- Create `database/seeders/data/movies.json`
- Create `database/seeders/data/questions_{movie_slug}.json` files (at least 2 movies with 10+ questions each)
- **Status:** DONE

**Tests:**
- Unit test: Each JSON seed file is valid JSON
- Unit test: Each question JSON has exactly 4 choices with labels A–D
- Unit test: Each question JSON has exactly one correct answer

### Phase 3.2: Implement Database Seeders
- Create `AgeGroupSeeder` reading from JSON
- Create `MovieSeeder` reading from JSON
- Create `QuestionSeeder` reading from JSON (with choices)
- Wire all seeders into `DatabaseSeeder`
- **Status:** DONE

**Tests:**
- Feature test: Running `AgeGroupSeeder` populates age_groups table
- Feature test: Running `MovieSeeder` populates movies table with correct age_group relationships
- Feature test: Running `QuestionSeeder` populates questions and question_choices tables
- Feature test: Running full `DatabaseSeeder` seeds all tables with correct record counts

---

## Phase 4: Core UI Pages

### Phase 4.1: Home Page — Age Group Selection
- Create `HomePage` Livewire component
- Display age group selector (cards/buttons)
- Store selection in `user_settings`
- Show "quick start" to jump into a random movie quiz
- Route: `GET /`
- **Status:** NOT STARTED

**Tests:**
- Feature test: GET `/` renders HomePage component
- Feature test: Age groups are displayed from database
- Feature test: Selecting an age group stores it in user_settings
- Feature test: If age group already selected, it is pre-selected on load

### Phase 4.2: Movie Index Page
- Create `MovieIndex` Livewire component
- List movies filtered by selected age group
- Show completion indicators (quizzes played, best score)
- Route: `GET /movies`
- **Status:** NOT STARTED

**Tests:**
- Feature test: GET `/movies` renders MovieIndex component
- Feature test: Only movies for the current age group are shown
- Feature test: Movies are ordered by sort_order
- Feature test: Completion indicator shows correct data for played quizzes

### Phase 4.3: Movie Show Page
- Create `MovieShow` Livewire component
- Display movie details (title, year, description, poster)
- "Start Quiz" button with question count selector
- Route: `GET /movies/{slug}`
- **Status:** NOT STARTED

**Tests:**
- Feature test: GET `/movies/{slug}` renders MovieShow component with movie data
- Feature test: 404 returned for non-existent slug
- Feature test: "Start Quiz" button is visible when movie has questions
- Feature test: Question count selector defaults to 10 (or max available)

---

## Phase 5: Quiz Engine

### Phase 5.1: Quiz Session Creation
- Create `QuizRunner` Livewire component
- On mount: create `QuizSession`, randomize questions, store IDs
- Route: `GET /quiz/{session}`
- **Status:** NOT STARTED

**Tests:**
- Feature test: Starting a quiz creates a QuizSession record
- Feature test: QuizSession stores randomized question_ids as JSON
- Feature test: Question count matches requested count
- Feature test: GET `/quiz/{session}` renders QuizRunner with first question

### Phase 5.2: Answering Questions
- Display current question prompt and 4 choices
- On answer: save `QuizAnswer`, show feedback (correct/incorrect + explanation)
- Track `time_spent_seconds` per question
- "Next" button to advance
- **Status:** NOT STARTED

**Tests:**
- Feature test: Selecting a choice creates a QuizAnswer record
- Feature test: `is_correct` is properly set based on the choice
- Feature test: Feedback is shown after answering (correct/incorrect)
- Feature test: Explanation is displayed after answering
- Feature test: Cannot answer the same question twice in a session

### Phase 5.3: Quiz Completion
- After last question, update `QuizSession` (completed_at, correct_count, duration_seconds)
- Redirect to summary page
- **Status:** NOT STARTED

**Tests:**
- Feature test: Completing all questions sets `completed_at` on QuizSession
- Feature test: `correct_count` matches actual correct answers
- Feature test: `duration_seconds` is calculated from started_at to completed_at
- Feature test: User is redirected to summary after last question

### Phase 5.4: Quiz Summary Page
- Create `QuizSummary` Livewire component
- Display score (correct/total), percentage, time taken
- "Play Again" and "Back to Movies" buttons
- Route: `GET /quiz/{session}/summary`
- **Status:** NOT STARTED

**Tests:**
- Feature test: GET `/quiz/{session}/summary` renders QuizSummary with correct score
- Feature test: Summary shows correct count, total count, and percentage
- Feature test: "Play Again" starts a new session for the same movie
- Feature test: Incomplete session redirects back to QuizRunner

---

## Phase 6: Progress & Stats

### Phase 6.1: Progress Dashboard
- Create `ProgressDashboard` Livewire component
- Display: total quizzes played, overall accuracy %, best scores per movie, last played times
- Route: `GET /progress`
- **Status:** DONE

**Tests:**
- Feature test: GET `/progress` renders ProgressDashboard
- Feature test: Total quizzes count is accurate
- Feature test: Accuracy percentage is calculated correctly
- Feature test: Best score per movie is displayed correctly
- Feature test: Dashboard shows "no data" state when no quizzes played

### Phase 6.2: Movie Completion Indicators on MovieIndex
- Enhance `MovieIndex` to show per-movie stats (attempts, best score, last played)
- Visual indicators (stars, checkmarks, progress bars)
- **Status:** DONE

**Tests:**
- Feature test: Movie card shows number of quiz attempts
- Feature test: Movie card shows best score percentage
- Feature test: Movie with no attempts shows "Not played" state

---

## Phase 7: Parent Gate

### Phase 7.1: Parent Gate Modal
- Create `ParentGate` Livewire component (modal)
- Show a simple math question (e.g., "What is 12 + 7?")
- Gate access to: age group change, progress reset, settings
- **Status:** DONE

**Tests:**
- Feature test: Parent gate shows a math question
- Feature test: Correct answer grants access
- Feature test: Incorrect answer denies access
- Feature test: Math question is age-appropriate (addition/multiplication of small numbers)

### Phase 7.2: Settings Page (Behind Parent Gate)
- Allow changing age group
- Reset progress option
- Sound/haptics toggle placeholders
- **Status:** DONE

**Tests:**
- Feature test: Settings page is only accessible after passing parent gate
- Feature test: Changing age group updates user_settings
- Feature test: Reset progress deletes all quiz_sessions and quiz_answers

---

## Phase 8: UI Polish & Mobile Experience

### Phase 8.1: Mobile-Optimized Styling
- Kid-friendly color scheme and typography
- Touch-friendly button sizes (min 44px tap targets)
- Smooth transitions between quiz questions
- Responsive layout optimized for phone screens
- **Status:** DONE

**Tests:**
- Feature test: All pages return 200 status codes
- Feature test: Layout renders without errors on all routes

### Phase 8.2: Alpine.js Micro-Interactions
- Answer selection animation
- Score reveal animation on summary
- Progress bar animations
- Button press feedback
- **Status:** DONE

**Tests:**
- Feature test: Quiz answer buttons have Alpine click handlers
- Feature test: Summary page contains animation markup

---

## Phase 9: Offline & NativePHP Readiness

### Phase 9.1: Offline-First Verification
- Ensure all data is local (no external API calls)
- Verify SQLite is the only data source
- Test full app flow without network
- **Status:** DONE

**Tests:**
- Feature test: Full quiz flow works with seeded SQLite data (no HTTP mocking needed)
- Feature test: All pages render without external resource dependencies

### Phase 9.2: NativePHP Integration Prep
- Create `NativeFeedback` service with no-op fallbacks
- Stub methods: `success()`, `error()`, `toast()`
- Bind in service provider
- **Status:** DONE

**Tests:**
- Unit test: NativeFeedback service methods are callable
- Unit test: NativeFeedback methods return gracefully (no-op) without NativePHP runtime

---

## Phase 10: NativePHP Dialog & Haptics Plugins

### Phase 10.1: Install Dialog & System Plugins
- Install and register `nativephp/mobile-dialog` and `nativephp/mobile-system` plugins
- **Status:** DONE (Dialog and Haptics facades available via nativephp/mobile v3.0)

**Tests:**
- Unit test: Dialog plugin service provider is registered
- Unit test: System plugin service provider is registered

### Phase 10.2: Implement NativeFeedback Service with Real Calls
- Update `NativeFeedback` service to use real `Dialog::toast()` and `Haptics::vibrate()` calls
- Graceful fallbacks when not running in NativePHP runtime (detect via class/facade availability)
- **Status:** DONE

**Tests:**
- Unit test: NativeFeedback `toast()` calls `Dialog::toast()` when available
- Unit test: NativeFeedback `vibrate()` calls `Haptics::vibrate()` when available
- Unit test: NativeFeedback methods return gracefully when NativePHP runtime is absent

### Phase 10.3: Wire Feedback into Quiz and Settings
- Trigger haptic/toast feedback in QuizRunner on correct/wrong answers
- Use `Dialog::alert()` for reset confirmation in Settings page
- **Status:** DONE

**Tests:**
- Feature test: QuizRunner calls NativeFeedback on answer submission
- Feature test: Settings reset triggers confirmation dialog
- Feature test: Feedback calls do not break the app when NativePHP is unavailable

---

## Phase 11: Share Plugin — Native Share Sheet

### Phase 11.1: Install Share Plugin
- Install and register `nativephp/mobile-share` plugin
- **Status:** DONE (Share facade available via nativephp/mobile v3.0)

**Tests:**
- Unit test: Share plugin service provider is registered

### Phase 11.2: Share Results from QuizSummary
- Add "Share Results" button on QuizSummary page
- Trigger native share sheet with score text (e.g., "I scored 8/10 on Frozen! 🎬")
- Graceful fallback when not in NativePHP runtime
- **Status:** DONE

**Tests:**
- Feature test: QuizSummary page displays "Share Results" button
- Feature test: Share action is triggered with correct score text
- Feature test: Share fallback works when NativePHP is unavailable

### Phase 11.3: Share Overall Stats from ProgressDashboard
- Add share action to ProgressDashboard for overall stats summary
- **Status:** DONE

**Tests:**
- Feature test: ProgressDashboard displays share button
- Feature test: Share action includes overall accuracy and total quizzes played

---

## Phase 12: Device Identity & Username

### Phase 12.1: Install Device Plugin
- Install and register `nativephp/mobile-device` plugin
- **Status:** DONE (Device facade available via nativephp/mobile v3.0)

**Tests:**
- Unit test: Device plugin service provider is registered

### Phase 12.2: Device Identity Service
- On first launch, get `Device::getId()` and store in `UserSetting`
- Generate default username: `User` + last 6 characters of device ID (e.g., `User3A8F2D`)
- Store both `device_id` and `username` in `UserSetting`
- Graceful fallback: generate a random device ID when not in NativePHP runtime
- **Status:** DONE

**Tests:**
- Unit test: Device identity service generates a default username from device ID
- Unit test: Device identity is stored in UserSetting on first call
- Unit test: Subsequent calls return the stored device ID (not a new one)
- Unit test: Fallback generates a random ID when Device plugin is unavailable

### Phase 12.3: Username Field in Settings
- Add editable username field to Settings page
- Validate username (alphanumeric, 3–20 characters)
- Store updated username in `UserSetting`
- **Status:** DONE

**Tests:**
- Feature test: Settings page displays current username
- Feature test: Username can be updated and persists
- Feature test: Invalid username (too short, too long, special chars) shows validation error

### Phase 12.4: Device Info on Settings Page
- Display device info (model, OS, platform) on Settings page via `Device::getInfo()`
- Graceful fallback showing "Unknown" when not in NativePHP runtime
- **Status:** DONE

**Tests:**
- Feature test: Settings page displays device info section
- Feature test: Device info shows fallback values when NativePHP is unavailable

---

## Phase 13: Network Plugin — Connectivity Awareness

### Phase 13.1: Install Network Plugin
- Install and register `nativephp/mobile-network` plugin
- **Status:** DONE (Network facade available via nativephp/mobile v3.0)

**Tests:**
- Unit test: Network plugin service provider is registered

### Phase 13.2: Network Status Service
- Create a network status service/helper using `Network::status()`
- Provide `isOnline()` and `getConnectionType()` methods
- Graceful fallback: assume online when not in NativePHP runtime
- **Status:** DONE

**Tests:**
- Unit test: Network status service returns connection status
- Unit test: `isOnline()` returns true as fallback when plugin is unavailable
- Unit test: `getConnectionType()` returns status string

### Phase 13.3: Online/Offline Indicator in Layout
- Add a visual online/offline indicator to the app layout
- Show connection type (Wi-Fi, cellular) when online
- **Status:** DONE

**Tests:**
- Feature test: Layout includes network status indicator
- Feature test: Indicator reflects current connectivity state

### Phase 13.4: Gate Online-Only Features
- Gate online-only features (leaderboard) behind connectivity checks
- Show appropriate messaging when offline and trying to access online features
- **Status:** DONE

**Tests:**
- Feature test: Online-only features show offline message when not connected
- Feature test: Online-only features are accessible when connected

---

## Phase 14: Leaderboard — Anonymous Online Feature

### Phase 14.1: LeaderboardEntry Model & Migration
- Create `leaderboard_entries` migration (id, device_id, username, movie_slug, score, total, played_at, timestamps)
- Create `LeaderboardEntry` model with factory
- Index on `movie_slug` and `device_id`
- **Status:** NOT STARTED

**Tests:**
- Feature test: LeaderboardEntry can be created with factory
- Feature test: LeaderboardEntry is scoped by movie_slug
- Feature test: LeaderboardEntry is scoped by device_id

### Phase 14.2: API Routes with Rate Limiting
- `POST /api/scores` — submit a quiz score
- `GET /api/leaderboard` — overall top scores
- `GET /api/leaderboard/{movie}` — top scores per movie
- `PUT /api/devices/{deviceId}` — update username for a device
- Apply rate limiting to all API routes
- **Status:** NOT STARTED

**Tests:**
- Feature test: POST `/api/scores` creates a LeaderboardEntry
- Feature test: POST `/api/scores` validates required fields
- Feature test: GET `/api/leaderboard` returns top scores ordered by score desc
- Feature test: GET `/api/leaderboard/{movie}` filters by movie_slug
- Feature test: PUT `/api/devices/{deviceId}` updates username on all entries
- Feature test: API routes are rate limited

### Phase 14.3: Leaderboard Livewire Component
- Create `Leaderboard` Livewire component
- Display top scores with movie filter tabs
- Show current user's rank highlighted
- Route: `GET /leaderboard`
- **Status:** NOT STARTED

**Tests:**
- Feature test: GET `/leaderboard` renders Leaderboard component
- Feature test: Leaderboard displays scores from API
- Feature test: Movie filter tabs filter the displayed scores
- Feature test: Current device's entries are highlighted

### Phase 14.4: Auto-Submit Scores After Quiz
- After quiz completion, automatically submit score to `POST /api/scores` when online
- Include device_id, username, movie_slug, score, total, and played_at
- **Status:** NOT STARTED

**Tests:**
- Feature test: Completing a quiz triggers score submission when online
- Feature test: Score submission includes correct data
- Feature test: Score is not submitted when offline

### Phase 14.5: Offline Queue for Score Submissions
- Create `pending_syncs` migration (id, endpoint, payload JSON, created_at)
- Store failed/offline score submissions in `pending_syncs` table
- Sync pending submissions when connectivity resumes
- **Status:** NOT STARTED

**Tests:**
- Feature test: Failed score submission is stored in pending_syncs
- Feature test: Pending syncs are retried when connectivity resumes
- Feature test: Successfully synced entries are removed from pending_syncs
- Feature test: Pending sync payload contains valid score data

### Phase 14.6: Sync Username Changes to API
- When username is changed in Settings, update via `PUT /api/devices/{deviceId}`
- Queue the update if offline
- **Status:** NOT STARTED

**Tests:**
- Feature test: Changing username triggers API update when online
- Feature test: Username change is queued when offline
- Feature test: API update changes username on all leaderboard entries for the device

---

## Phase 15: Content Sync — Adding More Questions

### Phase 15.1: Migration-Based Content Updates
- Create a new migration that seeds additional movie and question data
- Demonstrates how app updates deliver new content on mobile via migrations
- **Status:** NOT STARTED

**Tests:**
- Feature test: Migration inserts new movies and questions without duplicating existing data
- Feature test: New questions have valid structure (4 choices, one correct)
- Feature test: Existing data is not modified by the migration

### Phase 15.2: API-Based Content Endpoint
- Create `GET /api/questions` endpoint that returns questions since a given timestamp
- Accept `since` query parameter (ISO 8601 timestamp)
- Return movies and their questions with choices
- **Status:** NOT STARTED

**Tests:**
- Feature test: GET `/api/questions` returns all questions when no `since` param
- Feature test: GET `/api/questions?since=...` returns only questions created after timestamp
- Feature test: Response includes movie data, questions, and choices

### Phase 15.3: Content Sync Service
- Create a content sync service that checks for new questions when online
- Download new questions from API and insert into local SQLite
- Track last sync timestamp in `UserSetting`
- Avoid duplicating existing questions
- **Status:** NOT STARTED

**Tests:**
- Feature test: Sync service fetches questions from API
- Feature test: New questions are inserted into local database
- Feature test: Duplicate questions are skipped
- Feature test: Last sync timestamp is updated after successful sync

### Phase 15.4: New Content Available Indicator
- Add "New content available" indicator in the UI (e.g., badge on movie index)
- Show indicator when sync service detects new content
- Dismiss indicator after user views new content
- **Status:** NOT STARTED

**Tests:**
- Feature test: Indicator is shown when new content is available
- Feature test: Indicator is hidden when no new content exists
- Feature test: Indicator is dismissed after user views the content

---

## Summary

| Phase | Description                    | Status      |
|-------|--------------------------------|-------------|
| 1     | Project Setup & Dependencies   | DONE        |
| 2     | Database Schema & Models       | NOT STARTED |
| 3     | Seed Data                      | DONE        |
| 4     | Core UI Pages                  | NOT STARTED |
| 5     | Quiz Engine                    | NOT STARTED |
| 6     | Progress & Stats               | DONE        |
| 7     | Parent Gate                    | DONE        |
| 8     | UI Polish & Mobile Experience  | DONE        |
| 9     | Offline & NativePHP Readiness  | DONE        |
| 10    | NativePHP Dialog & Haptics     | DONE        |
| 11    | Share Plugin — Native Share    | DONE        |
| 12    | Device Identity & Username     | DONE        |
| 13    | Network — Connectivity         | DONE        |
| 14    | Leaderboard — Anonymous Online | NOT STARTED |
| 15    | Content Sync                   | NOT STARTED |

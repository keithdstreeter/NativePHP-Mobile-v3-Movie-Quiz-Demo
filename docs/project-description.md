# NativePHP Mobile Quiz App — Full Implementation Plan

**Stack:** Laravel 12 + Livewire 4 + Alpine (minimal) + SQLite
**Target:** NativePHP Mobile v3 (offline-first)

---

# 1. Product Overview

## Concept

A kids quiz mobile app about **animated movies**.

* Each question has **A/B/C/D answers**
* Questions belong to a **movie**
* Movies belong to an **age group**
* App is **offline-first** using SQLite

---

## Core Features

### Main Features

* Choose age group
* Browse movies by age group
* Play quiz per movie
* Track progress & stats
* Local-only data storage

### Optional Enhancements (Future)

* Haptics + native toast feedback
* Push reminders
* Audio questions
* Badges / achievements

---

# 2. Architecture & Design Principles

## Offline-First

* All quiz data seeded locally
* No internet required
* Progress stored in SQLite

## NativePHP Considerations

* Single web view UI
* Event-driven native actions
* Keep flows simple and web-like
* Avoid long blocking PHP logic

---

# 3. Database Schema (SQLite)

---

## age_groups

Stores age categories.

| Column     | Type             |
| ---------- | ---------------- |
| id         | PK               |
| code       | string unique    |
| label      | string           |
| min_age    | tinyint nullable |
| max_age    | tinyint nullable |
| sort_order | smallint         |
| is_active  | boolean          |
| timestamps |                  |

---

## movies

Each movie belongs to an age group.

| Column       | Type            |
| ------------ | --------------- |
| id           | PK              |
| age_group_id | FK              |
| title        | string          |
| slug         | string unique   |
| release_year | smallint        |
| poster_path  | string nullable |
| description  | text nullable   |
| sort_order   | smallint        |
| is_active    | boolean         |
| timestamps   |                 |

---

## questions

Each question belongs to a movie.

| Column      | Type          |
| ----------- | ------------- |
| id          | PK            |
| movie_id    | FK            |
| prompt      | text          |
| difficulty  | tinyint       |
| kind        | string        |
| explanation | text nullable |
| is_active   | boolean       |
| timestamps  |               |

---

## question_choices

Stores A/B/C/D answers.

| Column      | Type    |
| ----------- | ------- |
| id          | PK      |
| question_id | FK      |
| label       | char(1) |
| text        | string  |
| is_correct  | boolean |
| sort_order  | tinyint |
| timestamps  |         |

Constraints:

* Unique(question_id, label)
* Exactly one correct answer per question

---

## quiz_sessions

Represents one quiz attempt.

| Column           | Type                  |
| ---------------- | --------------------- |
| id               | PK (UUID recommended) |
| movie_id         | FK                    |
| age_group_id     | FK                    |
| question_count   | smallint              |
| correct_count    | smallint              |
| started_at       | datetime              |
| completed_at     | datetime nullable     |
| duration_seconds | int nullable          |
| question_ids     | JSON text             |
| timestamps       |                       |

---

## quiz_answers

Stores answers per session.

| Column             | Type     |
| ------------------ | -------- |
| id                 | PK       |
| quiz_session_id    | FK       |
| question_id        | FK       |
| selected_choice_id | FK       |
| is_correct         | boolean  |
| answered_at        | datetime |
| time_spent_seconds | smallint |
| timestamps         |          |

---

## user_settings

Key-value storage.

| Column     | Type          |
| ---------- | ------------- |
| id         | PK            |
| key        | string unique |
| value      | text          |
| timestamps |               |

Example keys:

* current_age_group_id
* sound_enabled
* haptics_enabled

---

# 4. Seed Data Strategy

## JSON Seed Files

Recommended location:

```
database/seeders/data/
```

### Files

* age_groups.json
* movies.json
* questions_{movie_slug}.json

---

## Question JSON Example

```json
{
  "movie_slug": "toy-story",
  "questions": [
    {
      "prompt": "Who is Woody?",
      "difficulty": 1,
      "explanation": "Woody is Andy’s cowboy toy.",
      "choices": [
        {"label":"A","text":"A cowboy toy","correct":true},
        {"label":"B","text":"A space ranger","correct":false},
        {"label":"C","text":"A dinosaur","correct":false},
        {"label":"D","text":"A piggy bank","correct":false}
      ]
    }
  ]
}
```

---

# 5. Application Flow

---

## First Launch

1. Ask user to choose age group
2. Store selection in user_settings

---

## Quiz Flow

### Start Quiz

* Choose movie
* Select question count (default 10)
* Create quiz_session
* Randomize questions

---

### Answer Question

* Show prompt + 4 options
* Save quiz_answer
* Show feedback
* Move to next question

---

### End Quiz

* Show score
* Offer replay

---

## Progress Screen

Displays:

* Total quizzes played
* Accuracy %
* Best scores per movie
* Last played times

---

# 6. Livewire Component Structure

---

## Pages

```
HomePage
MovieIndex
MovieShow
QuizRunner
QuizSummary
ProgressDashboard
ParentGateModal
```

---

## Component Responsibilities

### HomePage

* Select age group
* Quick start

---

### MovieIndex

* List movies by age group
* Show completion indicators

---

### MovieShow

* Movie info
* Start quiz button

---

### QuizRunner

State:

* quizSessionId
* questionIds[]
* currentIndex
* selectedChoiceId
* showFeedback

Methods:

* mount()
* answer()
* next()
* finish()

---

### QuizSummary

* Display final score

---

### ProgressDashboard

* Aggregated statistics

---

# 7. Routes

```
/ → HomePage
/movies → MovieIndex
/movies/{slug} → MovieShow
/quiz/{session} → QuizRunner
/quiz/{session}/summary → QuizSummary
/progress → ProgressDashboard
```

---

# 8. Query & Performance Notes

## Question Loading

At quiz start:

* Randomize questions
* Store IDs as JSON in session

---

## Aggregations

Use SQL for:

* Best scores
* Attempts per movie
* Accuracy percentage

---

# 9. Parent Gate

Simple child-safe lock:

* Show math question modal
* Allow:

  * Age group change
  * Reset progress
  * Settings access

---

# 10. Optional NativePHP Integrations

Future enhancements:

* Haptic feedback
* Native toast messages
* Push notifications
* Share results

Use a wrapper service:

```
NativeFeedback::success()
NativeFeedback::error()
NativeFeedback::toast()
```

Fallback to no-op on web.

---

# 11. Implementation Checklist

For the coding agent:

1. Create Laravel 12 project
2. Install Livewire 4
3. Configure SQLite
4. Create migrations
5. Create models & relations
6. Implement JSON seeders
7. Build Livewire pages
8. Implement quiz logic
9. Add parent gate
10. Add Alpine micro-interactions
11. Test fully offline

---

# 12. Future Extensions

Possible upgrades:

* Leaderboards
* Sync to cloud
* Audio questions
* Animated transitions
* Badge system
* Daily challenges

---

# END OF PLAN


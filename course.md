# From Laravel to Mobile: Building Native Apps with NativePHP

**Audience:** Laravel developers who want to ship mobile apps using their existing skills.
**Premise:** We don't teach Laravel or Livewire — we focus purely on the NativePHP layer.
**Format:** ~8 videos, ~1 hour total. Each video = one focused topic.
**Demo device:** Android (real device via Jump).
**Demo project:** Kids' movie quiz app (Laravel 12, Livewire 4, SQLite, Tailwind v4).

---

## Video 1: Introduction & First Launch (8 min)

What NativePHP is, install it, see it run on a real phone.

- What is NativePHP? PHP runtime bundled into a native mobile shell (https://nativephp.com/docs/mobile/3/getting-started/introduction)
- Architecture in 60 seconds: your Laravel app runs ON the phone, no server needed
- Free core + paid premium plugins model (changed their model to free in v3 - https://nativephp.com/blog/nativephp-for-mobile-is-now-free)
- **Install:** `composer require nativephp/mobile` → `php artisan native:install`
- **Config walkthrough:** `app_id`, `version`, `orientation`, `start_url`
- **Jump:** install Jump app on Android → `php artisan native:jump` → scan QR → app on real device
- Demo the finished quiz app on the phone
- Quick tour of the companion repo (the code is already built — we'll walk through it)

---

## Video 2: Mobile-First Thinking for Laravel (5 min)

The mental shifts when your Laravel app becomes a mobile app.

- Your app IS the server — SQLite is the only database, everything is local
- Offline by default: no network dependency for core features
- Seeding data: JSON files + seeders ship with the app
- Migrations as content delivery: new questions arrive via app updates
- Mobile UI basics: viewport meta, 44px tap targets, no hover states
- You already know how to build this — it's just Laravel with constraints

---

## Video 3: Plugin System & Graceful Fallbacks (5 min)

How NativePHP plugins work and the pattern that makes your app run everywhere.

- Plugins = Composer packages with native Kotlin/Swift bridges behind Laravel facades
- Two-way communication: call native functions via facades, listen for native events
- **The key pattern:** `function_exists('nativephp_call')` to detect NativePHP runtime
- Building service wrappers with no-op fallbacks (app works in browser AND on device)
- Show `NativeFeedback` service as the example: wraps Dialog, Haptics, Share
- Why this matters: you can develop in the browser and test native features on device

---

## Video 4: Native Features — Dialog, Haptics, Share, Device (10 min)

Using the free plugins to make the app feel native.

- **Dialog & Haptics — feedback that feels real**
  - `Dialog::toast()` on correct/wrong quiz answers
  - `Haptics::vibrate()` alongside toasts for tactile feedback
  - `Dialog::alert()` with buttons for reset confirmation
  - Listening for button presses: `#[On('native:' . ButtonPressed::class)]`
  - Demo on device: answer a quiz question, feel the vibration, see the toast
- **Share — native share sheet**
  - `Share::url()` to share quiz results: "I scored 8/10 on Frozen!"
  - Share overall stats from progress dashboard
  - Demo on device: tap Share, see Android share sheet
- **Device — identity & hardware info**
  - `Device::getId()` — unique hardware identifier for anonymous leaderboard
  - `Device::getInfo()` — model, OS, platform displayed in settings
  - Auto-generating usernames from device ID
  - Fallback: `Str::uuid()` when running in browser

---

## Video 5: Network Awareness & Online Features (8 min)

Detecting connectivity and building features that work online and offline.

- **Network plugin**
  - `Network::status()` — connected boolean + type (Wi-Fi/cellular)
  - `NetworkStatus` service wrapper with "assume online" fallback
  - Online/offline indicator in layout with `wire:poll.30s`
- **Gating online features**
  - Leaderboard only accessible when online, "you're offline" message otherwise
- **Self-referential API pattern**
  - Same Laravel app serves the UI AND the API — the app calls itself
  - API routes: submit scores, fetch leaderboard, sync content
- **Offline queue**
  - Complete a quiz offline → score saved to `pending_syncs` table
  - Back online → pending submissions automatically retry
  - Username changes queued the same way

---

## Video 6: Content Sync — Keeping the App Fresh (5 min)

Two strategies for delivering new content to mobile users.

- **Strategy 1: Migrations as content delivery**
  - New questions added via a migration — users get content when the app updates
  - Simple, no server needed, works fully offline
- **Strategy 2: API-based sync**
  - `GET /api/questions?since=...` — delta sync endpoint
  - `ContentSync` service: fetch new movies/questions, upsert locally, track last sync time
  - "New content available" banner on movie index
  - Runs on app open, skipped when offline
- When to use which: migrations for infrequent updates, API sync for living content

---

## Video 7: Testing NativePHP Apps (5 min)

You already know how to test Laravel — here's the NativePHP-specific part.

- It's just Pest tests — nothing special about the test runner
- Mocking NativePHP facades: `Dialog::shouldReceive('toast')`, etc.
- Testing the fallback path: assert methods work when runtime is absent
- Testing offline behavior: mock `NetworkStatus::isOnline()` returning false
- Testing Livewire components that trigger native features
- Show a few real tests from the demo project

---

## Video 8: What's Next — Premium Plugins & Roadmap (5 min)

A quick tour of what else NativePHP can do beyond what we built.

**Paid plugins (shown from docs/marketplace, not implemented):**
- **Biometrics** — Face ID / Fingerprint. Could replace the parent gate math question.
- **Push Notifications** (Firebase) — Notify when new quiz content drops.
- **Secure Storage** — Keychain / EncryptedSharedPreferences for sensitive data.
- **Scanner** — QR/barcode scanning. Scan a code to join a quiz room.
- **Geolocation** — GPS location. Location-based leaderboards.
- **Camera** — Photo/video capture. Custom avatar photos.
- **Microphone** — Audio recording. Voice-based quiz answers.

**Free plugins not covered:**
- **Browser** — In-app browser, OAuth authentication flows.
- **File** — File operations, export quiz history.
- **Community plugins**: example https://x.com/SRWieZ/status/2023714818457563231?s=20

**Also mention:**
- **Jump** limitations: can't test paid plugins, need full build for those
- **EDGE components:** native UI elements (bottom nav, top bar) — available but not demoed here
- **Roadmap:** background tasks, more EDGE components, performance improvements

---

## Summary

| # | Video | Time |
|---|-------|------|
| 1 | Introduction & First Launch | ~8 min |
| 2 | Mobile-First Thinking for Laravel | ~5 min |
| 3 | Plugin System & Graceful Fallbacks | ~5 min |
| 4 | Native Features — Dialog, Haptics, Share, Device | ~10 min |
| 5 | Network Awareness & Online Features | ~8 min |
| 6 | Content Sync — Keeping the App Fresh | ~5 min |
| 7 | Testing NativePHP Apps | ~5 min |
| 8 | What's Next — Premium Plugins & Roadmap | ~5 min |
| | **Total** | **~51 min** |

---

## Notes

- **Companion repo:** all code is already built — viewers clone and follow along.
- **No Xcode/Android Studio shown.** Jump is the dev workflow. Mention that a full build is needed for app store submission and paid plugins, but don't demonstrate it.
- **Android only** for on-device demos. Mention iOS works the same way.
- **Videos 3–6 are the meat** — the NativePHP-specific patterns. Videos 1–2 are setup/context, 7–8 are wrap-up.
- **Each video is self-contained** but they build on each other narratively.

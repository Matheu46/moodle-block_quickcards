# QuickCards (`block_quickcards`)

QuickCards is a comprehensive spaced repetition flashcard plugin for Moodle 4.x and 5.x built directly as a Moodle Block. It combines an interactive Dashboard and course block with dedicated views for deck management, study sessions based on the SuperMemo SM-2 algorithm, and import/export capabilities.

---

## Key Features

### 🧠 Spaced Repetition (SuperMemo SM-2 Engine)
- **Scientific Review Scheduling**: Adapts to individual memory retention, dynamically scheduling cards for review right before they are forgotten.
- **4-Tier Feedback System**:
  - **Again** (Rating 0): Resets repetitions, schedules card for review in the current session.
  - **Hard** (Rating 3): Increases interval conservatively and decreases ease factor.
  - **Good** (Rating 4): Standard interval progression based on current ease factor.
  - **Easy** (Rating 5): Extended interval jump with increased ease factor.
- **Smart Progress Tracking**: Tracks repetitions, ease factor, review intervals, and next review dates per card and user.

### 🎯 Context-Aware Moodle Block
- **Dashboard (`/my`)**: Displays today's due cards count, decks needing immediate review, and global flashcard statistics.
- **Course Pages**: Automatically filters and highlights decks linked to the current enrolled course.
- **Quick Add Flashcard Modal**: Create cards on the fly without leaving the current course or dashboard page via AJAX (`core/ajax` + `block_quickcards_create_card`).

### 📖 Distraction-Free Study Session (`study.php`)
- **Keyboard Shortcuts**:
  - `Space` / `Enter`: Flip card / show answer.
  - `1`, `2`, `3`, `4`: Select rating (*Again*, *Hard*, *Good*, *Easy*).
- **Session Summary**: Real-time progress bar, remaining cards counter, and completion screen.

### 🗂️ Deck & Card Management (`view.php` & `deck.php`)
- **Deck Organizer**: Create, edit, and organize decks linked to specific courses or as general study decks.
- **Card Explorer**: Filter cards by status (*All*, *Due Today*, *New*, *Learned*) and perform instant live search.
- **Deck Actions**: Rename, edit descriptions, reset study progress, or delete decks.

### 🔄 Import & Export (Anki, Quizlet & JSON)
- **JSON Backup & Restore**: Export full decks with or without personal SM-2 study progress history.
- **Anki & Quizlet Import**: Paste or upload TSV, CSV, or TXT exports directly with automatic delimiter detection (Tab, Comma, Semicolon).
- **Import into Existing Decks**:
  - Choose to create a new deck or append cards to an existing deck (from the dashboard or directly from the deck page).
  - **Duplicate Card Resolution**:
    - **Skip** (Default): Keeps existing cards and skips duplicates.
    - **Update**: Updates the answer while keeping the card's existing SM-2 review progress intact.
    - **Allow**: Adds cards even if identical questions already exist.

### 🔒 Privacy & Security (GDPR Compliant)
- Full implementation of Moodle's Privacy API (`core_privacy`).
- Supports user data export (all decks, cards, and review records) and user data deletion.
- Rigorous capability and ownership checks (`dml_missing_record_exception` on unauthorized access).

---

## Requirements

- **Moodle**: 4.4+ (Build 2024042200 or higher) / Moodle 5.x
- **PHP**: 8.1, 8.2, or 8.3
- **Database**: PostgreSQL 13+, MariaDB 10.6+, or MySQL 8.0+

---

## Installation

1. Copy or clone the plugin directory into your Moodle installation:
   ```bash
   git clone <repo-url> blocks/quickcards
   ```
2. Log in to Moodle as an administrator and go to **Site administration > Notifications** to run the database installation.
3. *(Recommended)* Add the **QuickCards** block to the **Default Dashboard** (`Site administration > Appearance > Default Dashboard page`) so students have immediate access to their flashcards.

---

## Technical Architecture

- **Backend**: Strict PSR-4 namespaces (`block_quickcards\*`), Moodle External API (`core_external`), Delegated DB transactions, and parameterized `$DB` queries.
- **Frontend**: Modern ES6 AMD modules (`amd/src/*.js`) compiled with Grunt/Babel (no jQuery dependency), Mustache templates (`templates/*.mustache`), and responsive Bootstrap 5 styling.
- **Automated Testing**: Comprehensive PHPUnit test suite covering SM-2 algorithm calculations, card reviews, export/import roundtrips, duplicate handling, and access control.

---

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

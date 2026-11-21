# QuickCards (`block_quickcards`)

QuickCards is a comprehensive spaced repetition flashcard plugin for Moodle 4.x and 5.x built directly as a Moodle Block. It combines an interactive Dashboard and course block with dedicated views for deck management, study sessions based on the SuperMemo SM-2 algorithm, and import/export capabilities.

---

## Features

- **Review Notifications**: Real-time counter of flashcards due for review today based on the SM-2 algorithm.
- **Context-Aware Block**:
  - **Dashboard (`/my`)**: Surfaces decks requiring immediate review and aggregate study statistics.
  - **Course View**: Focuses on decks associated with the current course.
- **Direct Study CTA**: One-click launch into the interactive study session for any deck with due cards.
- **Quick Add Flashcard Modal**: Conveniently add new flashcards to any owned deck via an accessible Bootstrap modal and AJAX (`core/ajax` + `block_quickcards_create_card`) without page reloads.
- **Full-featured Deck Management**: Dedicated dashboard at `/blocks/quickcards/view.php` to create, edit, organize, and study decks.
- **Import & Export**: Backup and restore decks via QuickCards JSON or import from Anki / Quizlet CSV.

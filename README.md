# QuickCards Block (`block_quickcards`)

QuickCards Block is a companion block for the `local_quickcards` spaced repetition plugin. It provides learners with immediate visibility into their pending flashcard reviews and quick access to study sessions directly from the Moodle Dashboard or course pages.

---

## Features

- **Review Notifications**: Real-time counter of flashcards due for review today based on the SM-2 algorithm.
- **Context-Aware**:
  - **Dashboard (`/my`)**: Surfaces decks requiring immediate review and aggregate study statistics.
  - **Course View**: Focuses on decks associated with the current course.
- **Direct Study CTA**: One-click launch into the interactive study session for any deck with due cards.
- **Quick Add Flashcard Modal**: Conveniently add new flashcards to any owned deck via an accessible Bootstrap modal and AJAX (`core/ajax` + `local_quickcards_create_card`) without page reloads.
- **Seamless Integration**: Consumes the backend API of `local_quickcards` without duplicating business logic.

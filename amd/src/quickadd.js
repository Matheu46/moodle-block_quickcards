// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Quick add flashcard and quick create deck module for block_quickcards.
 *
 * @module     block_quickcards/quickadd
 * @copyright  2026 Matheus Mathias
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call as fetchMany} from 'core/ajax';
import Notification from 'core/notification';
import {add as addToast} from 'core/toast';
import {getString} from 'core/str';
import {Modal} from 'bootstrap';

/**
 * Setup Quick Add Card modal handling.
 *
 * @param {string} uniqid Unique block instance ID.
 */
const setupQuickAddCard = (uniqid) => {
    const modalEl = document.getElementById(`quickadd-modal-${uniqid}`);
    const form = document.getElementById(`quickadd-form-${uniqid}`);

    if (!modalEl || !form) {
        return;
    }

    if (modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
    }

    const questionInput = document.getElementById(`quickadd-question-${uniqid}`);
    const answerInput = document.getElementById(`quickadd-answer-${uniqid}`);
    const deckSelect = document.getElementById(`quickadd-deck-${uniqid}`);
    const errorBox = form.querySelector('.quickadd-error-box');
    const submitBtn = form.querySelector('.quickadd-submit-btn');
    const spinner = form.querySelector('.quickadd-spinner');
    const btnIcon = form.querySelector('.quickadd-btn-icon');
    const triggerBtn = document.querySelector(
        `#block-quickcards-${uniqid} [data-action="quickadd-card"]`
    );

    if (triggerBtn) {
        triggerBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const modal = Modal.getOrCreateInstance(modalEl);
            if (modal) {
                modal.show();
            }
        });
    }

    modalEl.addEventListener('shown.bs.modal', () => {
        if (questionInput) {
            questionInput.focus();
        }
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        if (errorBox) {
            errorBox.classList.add('d-none');
            errorBox.textContent = '';
        }
    });

    const resetLoadingState = () => {
        if (submitBtn) {
            submitBtn.disabled = false;
        }
        if (spinner) {
            spinner.classList.add('d-none');
        }
        if (btnIcon) {
            btnIcon.classList.remove('d-none');
        }
    };

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const deckId = deckSelect ? parseInt(deckSelect.value, 10) : 0;
        const question = questionInput ? questionInput.value.trim() : '';
        const answer = answerInput ? answerInput.value.trim() : '';

        if (!deckId) {
            if (errorBox) {
                getString('nodeckselected', 'block_quickcards').then((msg) => {
                    errorBox.textContent = msg;
                    errorBox.classList.remove('d-none');
                    return msg;
                }).catch(Notification.exception);
            }
            return;
        }

        if (!question || !answer) {
            if (errorBox) {
                getString('cardemptyerror', 'block_quickcards').then((msg) => {
                    errorBox.textContent = msg;
                    errorBox.classList.remove('d-none');
                    return msg;
                }).catch(Notification.exception);
            }
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
        }
        if (spinner) {
            spinner.classList.remove('d-none');
        }
        if (btnIcon) {
            btnIcon.classList.add('d-none');
        }
        if (errorBox) {
            errorBox.classList.add('d-none');
        }

        const request = {
            methodname: 'block_quickcards_create_card',
            args: {
                deckid: deckId,
                question: question,
                answer: answer,
            },
        };

        fetchMany([request])[0].then((response) => {
            resetLoadingState();

            if (response && response.success) {
                const modal = Modal.getOrCreateInstance(modalEl);
                if (modal) {
                    modal.hide();
                }

                form.reset();

                const deckItem = document.querySelector(
                    `#block-quickcards-${uniqid} [data-deck-id="${deckId}"]`
                );
                if (deckItem) {
                    const countEl = deckItem.querySelector('.quickcards-deck-card-count');
                    if (countEl) {
                        const currentCount = parseInt(countEl.textContent, 10) || 0;
                        countEl.textContent = (currentCount + 1).toString();
                    }
                }

                return getString('cardsaved', 'block_quickcards');
            }

            if (errorBox) {
                errorBox.textContent = (response && response.message) || 'Error creating card';
                errorBox.classList.remove('d-none');
            }
            return null;
        }).then((savedMsg) => {
            if (savedMsg) {
                addToast(savedMsg);
            }
            return true;
        }).catch((err) => {
            resetLoadingState();

            if (errorBox) {
                errorBox.textContent = err.message || err.error || 'Server error';
                errorBox.classList.remove('d-none');
            } else {
                Notification.exception(err);
            }
        });
    });
};

/**
 * Setup Quick Create Deck modal handling.
 *
 * @param {string} uniqid Unique block instance ID.
 */
const setupQuickCreateDeck = (uniqid) => {
    const modalEl = document.getElementById(`quickcreate-deck-modal-${uniqid}`);
    const form = document.getElementById(`quickcreate-deck-form-${uniqid}`);

    if (!modalEl || !form) {
        return;
    }

    if (modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
    }

    const nameInput = document.getElementById(`quickcreate-deck-name-${uniqid}`);
    const descInput = document.getElementById(`quickcreate-deck-desc-${uniqid}`);
    const courseInput = document.getElementById(`quickcreate-deck-course-${uniqid}`);
    const errorBox = form.querySelector('.quickcreate-deck-error-box');
    const submitBtn = form.querySelector('.quickcreate-deck-submit-btn');
    const spinner = form.querySelector('.quickcreate-deck-spinner');
    const btnIcon = form.querySelector('.quickcreate-deck-btn-icon');

    const triggerBtns = document.querySelectorAll(
        `#block-quickcards-${uniqid} [data-action="quickcreate-deck"]`
    );
    triggerBtns.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const modal = Modal.getOrCreateInstance(modalEl);
            if (modal) {
                modal.show();
            }
        });
    });

    modalEl.addEventListener('shown.bs.modal', () => {
        if (nameInput) {
            nameInput.focus();
        }
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        if (errorBox) {
            errorBox.classList.add('d-none');
            errorBox.textContent = '';
        }
        resetLoadingState();
    });

    const resetLoadingState = () => {
        if (submitBtn) {
            submitBtn.disabled = false;
        }
        if (spinner) {
            spinner.classList.add('d-none');
        }
        if (btnIcon) {
            btnIcon.classList.remove('d-none');
        }
    };

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const name = nameInput ? nameInput.value.trim() : '';
        const description = descInput ? descInput.value.trim() : '';
        const rawCourse = courseInput ? parseInt(courseInput.value, 10) : 0;
        const courseid = isNaN(rawCourse) ? 0 : rawCourse;

        if (!name) {
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
        }
        if (spinner) {
            spinner.classList.remove('d-none');
        }
        if (btnIcon) {
            btnIcon.classList.add('d-none');
        }
        if (errorBox) {
            errorBox.classList.add('d-none');
        }

        const request = {
            methodname: 'block_quickcards_create_deck',
            args: {
                name: name,
                description: description,
                courseid: courseid,
            },
        };

        fetchMany([request])[0].then((response) => {
            const deckId = response ? (response.id || response.deckid) : null;

            if (response && response.success && deckId) {
                const modal = Modal.getOrCreateInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
                form.reset();
                window.location.href = M.cfg.wwwroot + '/blocks/quickcards/deck.php?id=' + deckId;
            } else {
                resetLoadingState();
                if (errorBox) {
                    errorBox.textContent = (response && response.message) || 'Error creating deck';
                    errorBox.classList.remove('d-none');
                }
            }
            return true;
        }).catch((err) => {
            resetLoadingState();

            if (errorBox) {
                errorBox.textContent = err.message || err.error || 'Server error';
                errorBox.classList.remove('d-none');
            } else {
                Notification.exception(err);
            }
        });
    });
};

/**
 * Initialize Quick Add modal and Quick Create Deck modal for a block instance.
 *
 * @param {string} uniqid Unique block instance ID.
 */
export const init = (uniqid) => {
    setupQuickAddCard(uniqid);
    setupQuickCreateDeck(uniqid);
};

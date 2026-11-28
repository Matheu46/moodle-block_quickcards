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
 * Deck and Card management module for QuickCards.
 *
 * @module      block_quickcards/deck_manager
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call as fetchMany} from 'core/ajax';
import Notification from 'core/notification';
import {getString, getStrings} from 'core/str';
import {Modal} from 'bootstrap';

/**
 * Get or create a Bootstrap modal instance.
 *
 * @param {HTMLElement} element Modal DOM element.
 * @return {Modal|null} Bootstrap Modal instance.
 */
const getModalInstance = (element) => {
    if (!element) {
        return null;
    }
    return Modal.getOrCreateInstance(element);
};

/**
 * Setup search and filter functionality for cards in a deck.
 *
 * @return {object} Controller with refresh function.
 */
const setupCardFilters = () => {
    const searchInput = document.getElementById('quickcards-search-cards');
    const clearBtn = document.getElementById('quickcards-clear-card-search');
    const filterBtns = document.querySelectorAll('[data-filter]');
    const countInfo = document.getElementById('quickcards-cards-count-info');
    const resetFiltersBtn = document.getElementById('quickcards-reset-filters-btn');
    const noResultsEl = document.getElementById('quickcards-no-search-results');
    const emptyClearBtn = document.getElementById('quickcards-empty-clear-btn');
    const cardsContainer = document.getElementById('quickcards-cards-container');

    if (!cardsContainer) {
        return {
            refresh: () => false,
        };
    }

    const isServerPaginated = (cardsContainer.dataset.serverPaginated === '1');
    if (isServerPaginated) {
        const baseUrl = cardsContainer.dataset.baseUrl || window.location.pathname;
        let currentQuery = searchInput ? searchInput.value.trim() : '';
        let currentStatus = cardsContainer.dataset.status || 'all';
        let debounceTimer = null;
        let activeController = null;

        const buildFilterUrl = (query, status, page = 0) => {
            const url = new URL(baseUrl, window.location.origin);
            if (query) {
                url.searchParams.set('search', query);
            }
            if (status && status !== 'all') {
                url.searchParams.set('status', status);
            }
            if (page > 0) {
                url.searchParams.set('page', page.toString());
            }
            return url;
        };

        const loadFilteredView = (url, pushHistory = false) => {
            if (activeController) {
                activeController.abort();
            }
            activeController = new AbortController();
            cardsContainer.style.opacity = '0.5';

            window.fetch(url.toString(), {
                credentials: 'same-origin',
                signal: activeController.signal,
            }).then((response) => {
                if (!response.ok) {
                    throw new Error('Failed to load cards');
                }
                return response.text();
            }).then((html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newContainer = doc.getElementById('quickcards-cards-container');
                if (!newContainer) {
                    window.location.href = url.toString();
                    return true;
                }

                cardsContainer.innerHTML = newContainer.innerHTML;
                cardsContainer.dataset.status = newContainer.dataset.status || 'all';
                cardsContainer.style.opacity = '';
                currentStatus = cardsContainer.dataset.status;

                const newCountInfo = doc.getElementById('quickcards-cards-count-info');
                if (countInfo && newCountInfo) {
                    countInfo.textContent = newCountInfo.textContent;
                }

                const paginationEl = document.getElementById('quickcards-cards-pagination');
                const newPaginationEl = doc.getElementById('quickcards-cards-pagination');
                if (paginationEl && newPaginationEl) {
                    paginationEl.innerHTML = newPaginationEl.innerHTML;
                    paginationEl.classList.toggle('d-none', newPaginationEl.classList.contains('d-none'));
                }

                const newNoResults = doc.getElementById('quickcards-no-search-results');
                if (noResultsEl && newNoResults) {
                    noResultsEl.classList.toggle('d-none', newNoResults.classList.contains('d-none'));
                }

                const isFiltered = (currentQuery !== '' || currentStatus !== 'all');
                if (clearBtn) {
                    clearBtn.classList.toggle('d-none', currentQuery === '');
                }
                if (resetFiltersBtn) {
                    resetFiltersBtn.classList.toggle('d-none', !isFiltered);
                }
                filterBtns.forEach((btn) => {
                    btn.classList.toggle('active', (btn.dataset.filter || 'all') === currentStatus);
                });

                if (pushHistory) {
                    window.history.pushState({}, '', url.toString());
                } else {
                    window.history.replaceState({}, '', url.toString());
                }
                return true;
            }).catch((err) => {
                cardsContainer.style.opacity = '';
                if (err && err.name === 'AbortError') {
                    return;
                }
                window.location.href = url.toString();
            });
        };

        const applyServerFilters = (query, status, pushHistory = false) => {
            currentQuery = query;
            currentStatus = status || 'all';
            loadFilteredView(buildFilterUrl(currentQuery, currentStatus, 0), pushHistory);
        };

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const val = e.target.value.trim();
                if (clearBtn) {
                    clearBtn.classList.toggle('d-none', val === '');
                }
                if (debounceTimer) {
                    window.clearTimeout(debounceTimer);
                }
                debounceTimer = window.setTimeout(() => {
                    if (val !== currentQuery) {
                        applyServerFilters(val, currentStatus, false);
                    }
                }, 350);
            });

            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (debounceTimer) {
                        window.clearTimeout(debounceTimer);
                    }
                    applyServerFilters(searchInput.value.trim(), currentStatus, false);
                } else if (e.key === 'Escape') {
                    if (debounceTimer) {
                        window.clearTimeout(debounceTimer);
                    }
                    searchInput.value = '';
                    applyServerFilters('', 'all', false);
                }
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.focus();
                }
                applyServerFilters('', currentStatus, false);
            });
        }

        filterBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const q = searchInput ? searchInput.value.trim() : '';
                applyServerFilters(q, btn.dataset.filter || 'all', true);
            });
        });

        const resetAll = () => {
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
            }
            applyServerFilters('', 'all', true);
        };

        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', resetAll);
        }
        if (emptyClearBtn) {
            emptyClearBtn.addEventListener('click', resetAll);
        }

        const paginationEl = document.getElementById('quickcards-cards-pagination');
        if (paginationEl) {
            paginationEl.addEventListener('click', (e) => {
                const link = e.target.closest('a[href]');
                if (!link) {
                    return;
                }
                e.preventDefault();
                loadFilteredView(new URL(link.href, window.location.origin), true);
            });
        }

        return {
            refresh: () => {
                loadFilteredView(new URL(window.location.href), false);
                return true;
            },
        };
    }

    let currentQuery = '';
    let currentStatus = 'all';

    const getCardItems = () => cardsContainer.querySelectorAll('.quickcards-card-item');

    const applyCardFilters = () => {
        const cards = getCardItems();
        const total = cards.length;
        let shown = 0;
        let totalDue = 0;
        let totalNew = 0;
        let totalLearned = 0;

        cards.forEach((card) => {
            const isDue = (card.dataset.isDue === '1');
            const isNew = (card.dataset.isNew === '1');
            const isLearned = (card.dataset.isLearned === '1');

            if (isDue) {
                totalDue++;
            }
            if (isNew) {
                totalNew++;
            }
            if (isLearned) {
                totalLearned++;
            }

            const matchesText = !currentQuery || card.textContent.toLowerCase().includes(currentQuery);

            let matchesStatus = true;
            if (currentStatus === 'due') {
                matchesStatus = isDue;
            } else if (currentStatus === 'new') {
                matchesStatus = isNew;
            } else if (currentStatus === 'learned') {
                matchesStatus = isLearned;
            }

            const visible = (matchesText && matchesStatus);
            if (visible) {
                card.classList.remove('d-none');
                shown++;
            } else {
                card.classList.add('d-none');
            }
        });

        // Update badge counts.
        const countAllEl = document.getElementById('filter-count-all');
        const countDueEl = document.getElementById('filter-count-due');
        const countNewEl = document.getElementById('filter-count-new');
        const countLearnedEl = document.getElementById('filter-count-learned');
        if (countAllEl) {
            countAllEl.textContent = total.toString();
        }
        if (countDueEl) {
            countDueEl.textContent = totalDue.toString();
        }
        if (countNewEl) {
            countNewEl.textContent = totalNew.toString();
        }
        if (countLearnedEl) {
            countLearnedEl.textContent = totalLearned.toString();
        }

        // Update count label.
        if (countInfo) {
            getString('showingcount', 'block_quickcards', {shown: shown, total: total}).then((text) => {
                countInfo.textContent = text;
                return true;
            }).catch(() => {
                countInfo.textContent = `${shown} / ${total}`;
            });
        }

        // Toggle clear buttons and empty state.
        const isFiltered = (currentQuery !== '' || currentStatus !== 'all');
        if (clearBtn) {
            clearBtn.classList.toggle('d-none', currentQuery === '');
        }
        if (resetFiltersBtn) {
            resetFiltersBtn.classList.toggle('d-none', !isFiltered);
        }
        if (noResultsEl) {
            noResultsEl.classList.toggle('d-none', shown > 0 || total === 0);
        }
    };

    const resetFilters = () => {
        currentQuery = '';
        currentStatus = 'all';
        if (searchInput) {
            searchInput.value = '';
        }
        filterBtns.forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.filter === 'all');
        });
        applyCardFilters();
    };

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            currentQuery = e.target.value.trim().toLowerCase();
            applyCardFilters();
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                resetFilters();
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            currentQuery = '';
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
            }
            applyCardFilters();
        });
    }

    filterBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            filterBtns.forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            currentStatus = btn.dataset.filter;
            applyCardFilters();
        });
    });

    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', resetFilters);
    }
    if (emptyClearBtn) {
        emptyClearBtn.addEventListener('click', () => {
            resetFilters();
            if (searchInput) {
                searchInput.focus();
            }
        });
    }

    return {
        refresh: applyCardFilters,
    };
};

/**
 * Setup search and filter functionality for decks on dashboard.
 */
const setupDeckFilters = () => {
    const searchInput = document.getElementById('quickcards-search-decks');
    const clearBtn = document.getElementById('quickcards-clear-deck-search');
    const courseSelect = document.getElementById('quickcards-filter-deck-course');
    const filterBtns = document.querySelectorAll('[data-deck-filter]');
    const countInfo = document.getElementById('quickcards-decks-count-info');
    const resetFiltersBtn = document.getElementById('quickcards-reset-deck-filters-btn');
    const noResultsEl = document.getElementById('quickcards-no-deck-results');
    const emptyClearBtn = document.getElementById('quickcards-empty-deck-clear-btn');
    const decksContainer = document.getElementById('quickcards-decks-container');

    if (!decksContainer) {
        return;
    }

    let currentQuery = '';
    let currentCourse = 'all';
    let currentStatus = 'all';

    const getDeckItems = () => decksContainer.querySelectorAll('.quickcards-deck-item');

    const applyDeckFilters = () => {
        const decks = getDeckItems();
        const total = decks.length;
        let shown = 0;

        decks.forEach((deck) => {
            const matchesText = !currentQuery || deck.textContent.toLowerCase().includes(currentQuery);

            let matchesCourse = true;
            if (currentCourse === 'general') {
                matchesCourse = (deck.dataset.courseId === '0');
            } else if (currentCourse !== 'all') {
                matchesCourse = (deck.dataset.courseId === currentCourse);
            }

            let matchesStatus = true;
            if (currentStatus === 'due') {
                matchesStatus = (deck.dataset.hasDue === '1');
            }

            const visible = (matchesText && matchesCourse && matchesStatus);
            if (visible) {
                deck.classList.remove('d-none');
                shown++;
            } else {
                deck.classList.add('d-none');
            }
        });

        // Update count label.
        if (countInfo) {
            getString('showingdeckscount', 'block_quickcards', {shown: shown, total: total}).then((text) => {
                countInfo.textContent = text;
                return true;
            }).catch(() => {
                countInfo.textContent = `${shown} / ${total}`;
            });
        }

        // Toggle clear buttons and empty state.
        const isFiltered = (currentQuery !== '' || currentCourse !== 'all' || currentStatus !== 'all');
        if (clearBtn) {
            clearBtn.classList.toggle('d-none', currentQuery === '');
        }
        if (resetFiltersBtn) {
            resetFiltersBtn.classList.toggle('d-none', !isFiltered);
        }
        if (noResultsEl) {
            noResultsEl.classList.toggle('d-none', shown > 0 || total === 0);
        }
    };

    const resetFilters = () => {
        currentQuery = '';
        currentCourse = 'all';
        currentStatus = 'all';
        if (searchInput) {
            searchInput.value = '';
        }
        if (courseSelect) {
            courseSelect.value = 'all';
        }
        filterBtns.forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.deckFilter === 'all');
        });
        applyDeckFilters();
    };

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            currentQuery = e.target.value.trim().toLowerCase();
            applyDeckFilters();
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                resetFilters();
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            currentQuery = '';
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
            }
            applyDeckFilters();
        });
    }

    if (courseSelect) {
        courseSelect.addEventListener('change', (e) => {
            currentCourse = e.target.value;
            applyDeckFilters();
        });
    }

    filterBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            filterBtns.forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            currentStatus = btn.dataset.deckFilter;
            applyDeckFilters();
        });
    });

    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', resetFilters);
    }
    if (emptyClearBtn) {
        emptyClearBtn.addEventListener('click', () => {
            resetFilters();
            if (searchInput) {
                searchInput.focus();
            }
        });
    }
};

/**
 * Initialize Deck Manager event listeners.
 */
export const init = () => {
    const cardFilters = setupCardFilters();
    setupDeckFilters();

    const deckModalEl = document.getElementById('quickcards-deck-modal');
    const cardModalEl = document.getElementById('quickcards-card-modal');
    const moveCardModalEl = document.getElementById('quickcards-move-card-modal');
    const exportModalEl = document.getElementById('quickcards-export-modal');
    const importModalEl = document.getElementById('quickcards-import-modal');

    // Import Deck form fields.
    const importForm = document.getElementById('quickcards-import-form');
    const importFileInput = document.getElementById('quickcards-import-file-input');
    const importErrorEl = document.getElementById('quickcards-import-error');
    const importErrorMsg = document.getElementById('quickcards-import-error-msg');
    const importPreviewEl = document.getElementById('quickcards-import-preview');
    const importPreviewName = document.getElementById('quickcards-import-preview-name');
    const importPreviewCount = document.getElementById('quickcards-import-preview-count');
    const importCourseSelect = document.getElementById('quickcards-import-course-select');
    const importProgressWrapper = document.getElementById('quickcards-import-progress-wrapper');
    const importRestoreProgressCheck = document.getElementById('quickcards-import-restore-progress');
    const importSubmitBtn = document.getElementById('quickcards-import-submit-btn');

    let loadedJsonContent = '';
    let currentImportTargetDeckId = 0;
    let currentImportTargetDeckName = '';

    // Export Deck form fields.
    const exportForm = document.getElementById('quickcards-export-form');
    const exportDeckIdInput = document.getElementById('quickcards-export-deck-id');
    const exportDeckNameEl = document.getElementById('quickcards-export-deck-name');
    const exportSesskeyInput = document.getElementById('quickcards-export-sesskey');
    const exportFormatInput = document.getElementById('quickcards-export-format');
    const exportIncludeProgressInput = document.getElementById('quickcards-export-includeprogress');
    const exportContentOnlyRadio = document.getElementById('quickcards-export-content-only');
    const exportModeRadios = document.querySelectorAll('input[name="exportmode"]');

    // Import CSV form fields.
    const importCsvForm = document.getElementById('quickcards-import-csv-form');
    const importCsvNameInput = document.getElementById('quickcards-import-csv-name');
    const importCsvDelimiterSelect = document.getElementById('quickcards-import-csv-delimiter');
    const importCsvCourseSelect = document.getElementById('quickcards-import-csv-course');
    const importCsvFileInput = document.getElementById('quickcards-import-csv-file');
    const importCsvTextArea = document.getElementById('quickcards-import-csv-text');
    const importCsvCountBadge = document.getElementById('quickcards-import-csv-count');
    const importCsvSubmitBtn = document.getElementById('quickcards-import-csv-submit-btn');

    // Deck form fields.
    const deckForm = document.getElementById('quickcards-deck-form');
    const deckIdInput = document.getElementById('quickcards-deck-id');
    const deckNameInput = document.getElementById('quickcards-deck-name');
    const deckDescInput = document.getElementById('quickcards-deck-description');
    const deckCourseSelect = document.getElementById('quickcards-deck-course');
    const deckModalTitle = document.getElementById('quickcards-deck-modal-title');

    // Card form fields.
    const cardForm = document.getElementById('quickcards-card-form');
    const cardIdInput = document.getElementById('quickcards-card-id');
    const cardDeckIdInput = document.getElementById('quickcards-card-deckid');
    const cardQuestionInput = document.getElementById('quickcards-card-question');
    const cardAnswerInput = document.getElementById('quickcards-card-answer');
    const cardModalTitle = document.getElementById('quickcards-card-modal-title');

    // Move Card form fields.
    const moveCardForm = document.getElementById('quickcards-move-card-form');
    const moveCardIdInput = document.getElementById('quickcards-move-card-id');
    const moveTargetDeckSelect = document.getElementById('quickcards-move-target-deck');

    // Action executor helpers for confirmation dialogs.
    const executeResetDeck = (id) => {
        fetchMany([{
            methodname: 'block_quickcards_reset_deck',
            args: {id: id},
        }])[0].then((res) => {
            if (res.success) {
                window.location.reload();
            }
            return true;
        }).catch(Notification.exception);
    };

    const executeDeleteDeck = (id) => {
        fetchMany([{
            methodname: 'block_quickcards_delete_deck',
            args: {id: id},
        }])[0].then((res) => {
            if (res.success) {
                window.location.href = M.cfg.wwwroot + '/blocks/quickcards/view.php';
            }
            return true;
        }).catch(Notification.exception);
    };

    const executeDeleteCard = (id) => {
        fetchMany([{
            methodname: 'block_quickcards_delete_card',
            args: {id: id},
        }])[0].then((res) => {
            if (res.success) {
                const cardEl = document.getElementById('card-item-' + id);
                if (cardEl) {
                    cardEl.remove();
                    cardFilters.refresh();
                } else {
                    window.location.reload();
                }
            }
            return true;
        }).catch(Notification.exception);
    };

    // Modal action openers.
    const openCreateDeckModal = () => {
        if (!deckForm) {
            return;
        }
        deckForm.reset();
        deckIdInput.value = '0';
        getString('createdeck', 'block_quickcards').then((title) => {
            if (deckModalTitle) {
                deckModalTitle.textContent = title;
            }
            const modal = getModalInstance(deckModalEl);
            if (modal) {
                modal.show();
            }
            return true;
        }).catch(Notification.exception);
    };

    /**
     * Get target deck ID for import based on current modal mode or selected radio.
     *
     * @param {string} mode Format mode: 'json' or 'csv'.
     * @return {number} Target deck ID (0 for new deck).
     */
    const getImportTargetDeckId = (mode) => {
        if (currentImportTargetDeckId > 0) {
            return currentImportTargetDeckId;
        }
        const destExisting = document.getElementById(`quickcards-import-${mode}-dest-existing`);
        if (destExisting && destExisting.checked) {
            const sel = document.getElementById(`quickcards-import-${mode}-deck-select`);
            return sel ? parseInt(sel.value, 10) : 0;
        }
        return 0;
    };

    /**
     * Get duplicate action selected in the import modal.
     *
     * @param {string} mode Format mode: 'json' or 'csv'.
     * @return {string} 'skip', 'update', or 'allow'.
     */
    const getDuplicateAction = (mode) => {
        const dupRadio = document.querySelector(`input[name="quickcards-import-${mode}-dup"]:checked`);
        return dupRadio ? dupRadio.value : 'skip';
    };

    /**
     * Extract leading Anki 2.1.54+ file header directives (#separator, #html, #deck, #tags column, etc.).
     *
     * @param {string[]} lines Non-empty trimmed lines.
     * @return {{separator: ?string, deck: ?string, dataLines: string[]}}
     */
    const extractAnkiHeaders = (lines) => {
        const sepMap = {
            tab: '\t',
            comma: ',',
            semicolon: ';',
            pipe: '|',
            colon: ':',
            space: ' ',
        };
        const directiveRegex = new RegExp(
            '^#(separator|html|deck|notetype|tags|tags column|columns|' +
            'guid column|deck column|notetype column)\\s*:(.*)$',
            'i'
        );
        let separator = null;
        let deck = null;
        let idx = 0;

        while (idx < lines.length) {
            const match = lines[idx].match(directiveRegex);
            if (!match) {
                break;
            }
            const key = match[1].toLowerCase();
            const val = match[2].trim();
            if (key === 'separator' && val !== '') {
                const valLower = val.toLowerCase();
                if (Object.prototype.hasOwnProperty.call(sepMap, valLower)) {
                    separator = sepMap[valLower];
                } else if (val.length === 1) {
                    separator = val;
                }
            } else if (key === 'deck' && val !== '') {
                deck = val;
            }
            idx++;
        }

        return {
            separator: separator,
            deck: deck,
            dataLines: lines.slice(idx),
        };
    };

    /**
     * Live preview and validation for CSV import.
     */
    const updateCsvPreview = () => {
        if (!importCsvTextArea || !importCsvSubmitBtn) {
            return;
        }
        const raw = importCsvTextArea.value.trim();

        if (!raw) {
            if (importCsvCountBadge) {
                importCsvCountBadge.classList.add('d-none');
            }
            importCsvSubmitBtn.disabled = true;
            return;
        }

        const allLines = raw.split(/\r?\n/).map((l) => l.trim()).filter((l) => l !== '');
        const anki = extractAnkiHeaders(allLines);
        const lines = anki.dataLines;

        const isExisting = (currentImportTargetDeckId > 0) || (
            document.getElementById('quickcards-import-csv-dest-existing') &&
            document.getElementById('quickcards-import-csv-dest-existing').checked
        );

        if (!isExisting && anki.deck && importCsvNameInput && importCsvNameInput.value.trim() === '') {
            importCsvNameInput.value = anki.deck;
        }
        const deckName = importCsvNameInput ? importCsvNameInput.value.trim() : '';

        const mode = importCsvDelimiterSelect ? importCsvDelimiterSelect.value : 'auto';
        let sep = ',';
        if (mode === 'tab') {
            sep = '\t';
        } else if (mode === 'semicolon') {
            sep = ';';
        } else if (mode === 'comma') {
            sep = ',';
        } else if (anki.separator) {
            sep = anki.separator;
        } else if (lines.length > 0) {
            if (lines[0].includes('\t')) {
                sep = '\t';
            } else if (lines[0].includes(';')) {
                sep = ';';
            }
        }

        const headerQuestions = ['question', 'front', 'term', 'pergunta', 'frente', 'termo'];
        const headerAnswers = ['answer', 'back', 'definition', 'resposta', 'verso', 'definicao', 'definição'];
        let count = 0;

        lines.forEach((line, idx) => {
            const parts = line.split(sep);
            if (parts.length < 2) {
                return;
            }
            const q = parts[0].replace(/^"|"$/g, '').trim();
            const a = parts[1].replace(/^"|"$/g, '').trim();
            if (!q || !a) {
                return;
            }
            if (idx === 0 && headerQuestions.includes(q.toLowerCase()) && headerAnswers.includes(a.toLowerCase())) {
                return;
            }
            count++;
        });

        if (importCsvCountBadge) {
            if (count > 0) {
                getString('csvcardsdetected', 'block_quickcards', count).then((label) => {
                    importCsvCountBadge.textContent = label;
                    importCsvCountBadge.classList.remove('d-none');
                    return true;
                }).catch(() => {
                    importCsvCountBadge.textContent = `${count} cards`;
                    importCsvCountBadge.classList.remove('d-none');
                });
            } else {
                importCsvCountBadge.classList.add('d-none');
            }
        }

        if (isExisting) {
            const destDeckId = getImportTargetDeckId('csv');
            importCsvSubmitBtn.disabled = (count === 0 || !destDeckId);
        } else {
            importCsvSubmitBtn.disabled = (count === 0 || deckName === '');
        }
    };

    /**
     * Update import modal UI based on whether destination is new or existing deck.
     */
    const updateImportDestinationUI = () => {
        const titleEl = document.getElementById('quickcards-import-modal-title-text');
        const targetBanners = document.querySelectorAll('.quickcards-import-target-banner');
        const targetNames = document.querySelectorAll('.quickcards-import-target-name');
        const destChoices = document.querySelectorAll('.quickcards-import-dest-choice');

        const jsonExistingWrap = document.getElementById('quickcards-import-json-existing-select-wrap');
        const jsonDupWrap = document.getElementById('quickcards-import-json-duplicate-wrap');
        const jsonCourseWrap = document.getElementById('quickcards-import-json-course-wrap');

        const csvNameWrap = document.getElementById('quickcards-import-csv-name-wrap');
        const csvExistingWrap = document.getElementById('quickcards-import-csv-existing-select-wrap');
        const csvDupWrap = document.getElementById('quickcards-import-csv-duplicate-wrap');
        const csvCourseWrap = document.getElementById('quickcards-import-csv-course-wrap');

        const isDirectDeck = (currentImportTargetDeckId > 0);

        if (isDirectDeck) {
            getString('importcards', 'block_quickcards').then((title) => {
                if (titleEl) {
                    titleEl.textContent = title;
                }
                return true;
            }).catch(Notification.exception);

            getString('importcardsfor', 'block_quickcards', currentImportTargetDeckName).then((label) => {
                targetNames.forEach((el) => {
                    el.textContent = label;
                });
                return true;
            }).catch(() => {
                targetNames.forEach((el) => {
                    el.textContent = currentImportTargetDeckName;
                });
            });

            targetBanners.forEach((el) => el.classList.remove('d-none'));
            destChoices.forEach((el) => el.classList.add('d-none'));

            if (jsonDupWrap) {
                jsonDupWrap.classList.remove('d-none');
            }
            if (jsonCourseWrap) {
                jsonCourseWrap.classList.add('d-none');
            }

            if (csvDupWrap) {
                csvDupWrap.classList.remove('d-none');
            }
            if (csvNameWrap) {
                csvNameWrap.classList.add('d-none');
            }
            if (csvCourseWrap) {
                csvCourseWrap.classList.add('d-none');
            }
        } else {
            getString('importdeck', 'block_quickcards').then((title) => {
                if (titleEl) {
                    titleEl.textContent = title;
                }
                return true;
            }).catch(Notification.exception);

            targetBanners.forEach((el) => el.classList.add('d-none'));
            destChoices.forEach((el) => el.classList.remove('d-none'));

            const jsonDestExisting = document.getElementById('quickcards-import-json-dest-existing');
            const isJsonExisting = jsonDestExisting && jsonDestExisting.checked;
            if (jsonExistingWrap) {
                jsonExistingWrap.classList.toggle('d-none', !isJsonExisting);
            }
            if (jsonDupWrap) {
                jsonDupWrap.classList.toggle('d-none', !isJsonExisting);
            }
            if (jsonCourseWrap) {
                jsonCourseWrap.classList.toggle('d-none', isJsonExisting);
            }

            const csvDestExisting = document.getElementById('quickcards-import-csv-dest-existing');
            const isCsvExisting = csvDestExisting && csvDestExisting.checked;
            if (csvExistingWrap) {
                csvExistingWrap.classList.toggle('d-none', !isCsvExisting);
            }
            if (csvDupWrap) {
                csvDupWrap.classList.toggle('d-none', !isCsvExisting);
            }
            if (csvNameWrap) {
                csvNameWrap.classList.toggle('d-none', isCsvExisting);
            }
            if (csvCourseWrap) {
                csvCourseWrap.classList.toggle('d-none', isCsvExisting);
            }
        }

        updateCsvPreview();
    };

    /**
     * Open the import modal, optionally pre-configured for a specific target deck.
     *
     * @param {number} targetDeckId Destination deck ID (0 for general import).
     * @param {string} targetDeckName Destination deck name.
     */
    const openImportDeckModal = (targetDeckId = 0, targetDeckName = '') => {
        currentImportTargetDeckId = targetDeckId ? parseInt(targetDeckId, 10) : 0;
        currentImportTargetDeckName = targetDeckName || '';

        if (importForm) {
            importForm.reset();
            loadedJsonContent = '';
            if (importPreviewEl) {
                importPreviewEl.classList.add('d-none');
            }
            if (importErrorEl) {
                importErrorEl.classList.add('d-none');
            }
            if (importSubmitBtn) {
                importSubmitBtn.disabled = true;
            }
        }
        if (importCsvForm) {
            importCsvForm.reset();
            if (importCsvCountBadge) {
                importCsvCountBadge.classList.add('d-none');
            }
            if (importCsvSubmitBtn) {
                importCsvSubmitBtn.disabled = true;
            }
        }

        updateImportDestinationUI();

        const modal = getModalInstance(importModalEl);
        if (modal) {
            modal.show();
        }
    };

    const openEditDeckModal = (btn) => {
        if (!deckForm) {
            return;
        }
        deckIdInput.value = btn.dataset.id;
        deckNameInput.value = btn.dataset.name || '';
        deckDescInput.value = btn.dataset.description || '';
        if (deckCourseSelect) {
            deckCourseSelect.value = btn.dataset.courseid || '0';
        }

        getString('editdeck', 'block_quickcards').then((title) => {
            if (deckModalTitle) {
                deckModalTitle.textContent = title;
            }
            const modal = getModalInstance(deckModalEl);
            if (modal) {
                modal.show();
            }
            return true;
        }).catch(Notification.exception);
    };

    const openExportDeckModal = (btn) => {
        if (exportDeckIdInput) {
            exportDeckIdInput.value = btn.dataset.id || '0';
        }
        if (exportDeckNameEl) {
            exportDeckNameEl.textContent = btn.dataset.name || '';
        }
        if (exportSesskeyInput && M.cfg && M.cfg.sesskey) {
            exportSesskeyInput.value = M.cfg.sesskey;
        }
        if (exportContentOnlyRadio) {
            exportContentOnlyRadio.checked = true;
        }
        if (exportFormatInput) {
            exportFormatInput.value = 'json';
        }
        if (exportIncludeProgressInput) {
            exportIncludeProgressInput.value = '0';
        }

        const modal = getModalInstance(exportModalEl);
        if (modal) {
            modal.show();
        }
    };

    const promptResetDeck = (btn) => {
        const id = parseInt(btn.dataset.id, 10);
        const name = btn.dataset.name || '';

        getStrings([
            {key: 'resetdeck', component: 'block_quickcards'},
            {key: 'confirmresetdeck', component: 'block_quickcards', param: name},
            {key: 'resetprogress', component: 'block_quickcards'},
            {key: 'cancel', component: 'block_quickcards'},
        ]).then(([title, question, confirmLabel, cancelLabel]) => {
            Notification.confirm(
                title,
                question,
                confirmLabel,
                cancelLabel,
                () => executeResetDeck(id)
            );
            return true;
        }).catch(Notification.exception);
    };

    const promptDeleteDeck = (btn) => {
        const id = parseInt(btn.dataset.id, 10);

        getStrings([
            {key: 'deletedeck', component: 'block_quickcards'},
            {key: 'confirmdeletedeck', component: 'block_quickcards'},
            {key: 'delete', component: 'block_quickcards'},
            {key: 'cancel', component: 'block_quickcards'},
        ]).then(([title, question, deleteLabel, cancelLabel]) => {
            Notification.confirm(
                title,
                question,
                deleteLabel,
                cancelLabel,
                () => executeDeleteDeck(id)
            );
            return true;
        }).catch(Notification.exception);
    };

    const openCreateCardModal = (btn) => {
        if (!cardForm) {
            return;
        }
        cardForm.reset();
        cardIdInput.value = '0';
        cardDeckIdInput.value = btn.dataset.deckid;

        getString('addcard', 'block_quickcards').then((title) => {
            if (cardModalTitle) {
                cardModalTitle.textContent = title;
            }
            const modal = getModalInstance(cardModalEl);
            if (modal) {
                modal.show();
            }
            return true;
        }).catch(Notification.exception);
    };

    const openEditCardModal = (btn) => {
        if (!cardForm) {
            return;
        }
        cardIdInput.value = btn.dataset.id;
        cardQuestionInput.value = btn.dataset.question || '';
        cardAnswerInput.value = btn.dataset.answer || '';

        getString('editcard', 'block_quickcards').then((title) => {
            if (cardModalTitle) {
                cardModalTitle.textContent = title;
            }
            const modal = getModalInstance(cardModalEl);
            if (modal) {
                modal.show();
            }
            return true;
        }).catch(Notification.exception);
    };

    const promptDeleteCard = (btn) => {
        const id = parseInt(btn.dataset.id, 10);

        getStrings([
            {key: 'deletecard', component: 'block_quickcards'},
            {key: 'confirmdeletecard', component: 'block_quickcards'},
            {key: 'delete', component: 'block_quickcards'},
            {key: 'cancel', component: 'block_quickcards'},
        ]).then(([title, question, deleteLabel, cancelLabel]) => {
            Notification.confirm(
                title,
                question,
                deleteLabel,
                cancelLabel,
                () => executeDeleteCard(id)
            );
            return true;
        }).catch(Notification.exception);
    };

    const openMoveCardModal = (btn) => {
        if (moveCardIdInput) {
            moveCardIdInput.value = btn.dataset.id;
        }
        const modal = getModalInstance(moveCardModalEl);
        if (modal) {
            modal.show();
        }
    };

    const handleDeckClick = (e) => {
        const createDeckBtn = e.target.closest('[data-action="quickcards-create-deck"]');
        if (createDeckBtn) {
            openCreateDeckModal();
            return true;
        }

        const importDeckBtn = e.target.closest('[data-action="quickcards-import-deck"]');
        if (importDeckBtn) {
            openImportDeckModal(0, '');
            return true;
        }

        const importCardsBtn = e.target.closest('[data-action="quickcards-import-cards"]');
        if (importCardsBtn) {
            e.preventDefault();
            const deckId = importCardsBtn.dataset.deckid || 0;
            const deckName = importCardsBtn.dataset.deckname || '';
            openImportDeckModal(deckId, deckName);
            return true;
        }

        const editDeckBtn = e.target.closest('[data-action="quickcards-edit-deck"]');
        if (editDeckBtn) {
            e.preventDefault();
            openEditDeckModal(editDeckBtn);
            return true;
        }

        const exportDeckBtn = e.target.closest('[data-action="quickcards-export-deck"]');
        if (exportDeckBtn) {
            e.preventDefault();
            openExportDeckModal(exportDeckBtn);
            return true;
        }

        const resetDeckBtn = e.target.closest('[data-action="quickcards-reset-deck"]');
        if (resetDeckBtn) {
            e.preventDefault();
            promptResetDeck(resetDeckBtn);
            return true;
        }

        const deleteDeckBtn = e.target.closest('[data-action="quickcards-delete-deck"]');
        if (deleteDeckBtn) {
            e.preventDefault();
            promptDeleteDeck(deleteDeckBtn);
            return true;
        }

        return false;
    };

    const handleCardClick = (e) => {
        const createCardBtn = e.target.closest('[data-action="quickcards-create-card"]');
        if (createCardBtn) {
            openCreateCardModal(createCardBtn);
            return true;
        }

        const editCardBtn = e.target.closest('[data-action="quickcards-edit-card"]');
        if (editCardBtn) {
            e.preventDefault();
            openEditCardModal(editCardBtn);
            return true;
        }

        const deleteCardBtn = e.target.closest('[data-action="quickcards-delete-card"]');
        if (deleteCardBtn) {
            e.preventDefault();
            promptDeleteCard(deleteCardBtn);
            return true;
        }

        const moveCardBtn = e.target.closest('[data-action="quickcards-move-card"]');
        if (moveCardBtn) {
            openMoveCardModal(moveCardBtn);
            return true;
        }

        return false;
    };

    // Document-level click handler for actions.
    document.addEventListener('click', (e) => {
        if (!handleDeckClick(e)) {
            handleCardClick(e);
        }
    });

    // Submit Deck Form.
    if (deckForm) {
        deckForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const id = parseInt(deckIdInput.value, 10);
            const name = deckNameInput.value.trim();
            const description = deckDescInput ? deckDescInput.value.trim() : '';
            const courseid = deckCourseSelect ? parseInt(deckCourseSelect.value, 10) : 0;

            if (!name) {
                return;
            }

            const isNew = (id === 0);
            const method = isNew ? 'block_quickcards_create_deck' : 'block_quickcards_update_deck';
            const args = isNew ? {name, description, courseid} : {id, name, description, courseid};

            fetchMany([{
                methodname: method,
                args: args,
            }])[0].then((res) => {
                if (res.success) {
                    const modal = getModalInstance(deckModalEl);
                    if (modal) {
                        modal.hide();
                    }
                    if (isNew) {
                        window.location.href = M.cfg.wwwroot + '/blocks/quickcards/deck.php?id=' + res.id;
                    } else {
                        window.location.reload();
                    }
                }
                return true;
            }).catch(Notification.exception);
        });
    }

    // Submit Card Form.
    if (cardForm) {
        cardForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const id = parseInt(cardIdInput.value, 10);
            const deckid = parseInt(cardDeckIdInput.value, 10);
            const question = cardQuestionInput.value.trim();
            const answer = cardAnswerInput.value.trim();

            if (!question || !answer) {
                return;
            }

            const isNew = (id === 0);
            const method = isNew ? 'block_quickcards_create_card' : 'block_quickcards_update_card';
            const args = isNew ? {deckid, question, answer} : {id, question, answer};

            fetchMany([{
                methodname: method,
                args: args,
            }])[0].then((res) => {
                if (res.success) {
                    const modal = getModalInstance(cardModalEl);
                    if (modal) {
                        modal.hide();
                    }
                    window.location.reload();
                }
                return true;
            }).catch(Notification.exception);
        });
    }

    // Submit Move Card Form.
    if (moveCardForm) {
        moveCardForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const cardid = parseInt(moveCardIdInput.value, 10);
            const targetdeckid = moveTargetDeckSelect ? parseInt(moveTargetDeckSelect.value, 10) : 0;

            if (!cardid || !targetdeckid) {
                return;
            }

            fetchMany([{
                methodname: 'block_quickcards_move_card',
                args: {cardid, targetdeckid},
            }])[0].then((res) => {
                if (res.success) {
                    const modal = getModalInstance(moveCardModalEl);
                    if (modal) {
                        modal.hide();
                    }
                    const cardEl = document.getElementById('card-item-' + cardid);
                    if (cardEl) {
                        cardEl.remove();
                        cardFilters.refresh();
                    } else {
                        window.location.reload();
                    }
                }
                return true;
            }).catch(Notification.exception);
        });
    }

    // Sync Export mode radio selection with hidden format & includeprogress inputs.
    exportModeRadios.forEach((radio) => {
        radio.addEventListener('change', () => {
            if (radio.checked) {
                if (exportFormatInput) {
                    exportFormatInput.value = radio.dataset.format || 'json';
                }
                if (exportIncludeProgressInput) {
                    exportIncludeProgressInput.value = radio.dataset.includeprogress || '0';
                }
            }
        });
    });

    // Submit Export Deck Form (native GET download, just close the modal).
    if (exportForm) {
        exportForm.addEventListener('submit', () => {
            const modal = getModalInstance(exportModalEl);
            if (modal) {
                modal.hide();
            }
        });
    }

    // Handle Import Deck File Selection and Preview.
    if (importFileInput) {
        importFileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            loadedJsonContent = '';
            importPreviewEl.classList.add('d-none');
            importErrorEl.classList.add('d-none');
            importSubmitBtn.disabled = true;

            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                try {
                    const text = event.target.result;
                    const data = JSON.parse(text);

                    if (!data || typeof data !== 'object' || data.format !== 'block_quickcards') {
                        getString('invalidfileformat', 'block_quickcards').then((msg) => {
                            importErrorMsg.textContent = msg;
                            importErrorEl.classList.remove('d-none');
                            return true;
                        }).catch(Notification.exception);
                        return;
                    }

                    if (!data.deck || !data.deck.name || !Array.isArray(data.cards)) {
                        getString('nodeckname', 'block_quickcards').then((msg) => {
                            importErrorMsg.textContent = msg;
                            importErrorEl.classList.remove('d-none');
                            return true;
                        }).catch(Notification.exception);
                        return;
                    }

                    loadedJsonContent = text;
                    importPreviewName.textContent = data.deck.name;
                    getString('cardsinfile', 'block_quickcards', data.cards.length).then((label) => {
                        importPreviewCount.textContent = label;
                        return true;
                    }).catch(() => {
                        importPreviewCount.textContent = data.cards.length + ' cards';
                    });

                    if (data.includesprogress) {
                        importProgressWrapper.classList.remove('d-none');
                        importRestoreProgressCheck.checked = false;
                    } else {
                        importProgressWrapper.classList.add('d-none');
                        importRestoreProgressCheck.checked = false;
                    }

                    importPreviewEl.classList.remove('d-none');
                    importSubmitBtn.disabled = false;
                } catch (err) {
                    getString('invalidjson', 'block_quickcards').then((msg) => {
                        importErrorMsg.textContent = msg;
                        importErrorEl.classList.remove('d-none');
                        return true;
                    }).catch(Notification.exception);
                }
            };
            reader.readAsText(file);
        });
    }

    // Submit Import Deck Form via AJAX WebService.
    if (importForm) {
        importForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (!loadedJsonContent) {
                return;
            }

            const targetDeckId = getImportTargetDeckId('json');
            const isExisting = (currentImportTargetDeckId > 0) || (
                document.getElementById('quickcards-import-json-dest-existing') &&
                document.getElementById('quickcards-import-json-dest-existing').checked
            );

            if (isExisting && !targetDeckId) {
                return;
            }

            importSubmitBtn.disabled = true;
            const courseid = importCourseSelect ? parseInt(importCourseSelect.value, 10) : 0;
            const restoreprogress = importRestoreProgressCheck ? importRestoreProgressCheck.checked : false;
            const duplicateaction = getDuplicateAction('json');

            fetchMany([{
                methodname: 'block_quickcards_import_deck',
                args: {
                    jsoncontent: loadedJsonContent,
                    courseid: courseid,
                    restoreprogress: restoreprogress,
                    deckid: targetDeckId,
                    duplicateaction: duplicateaction,
                },
            }])[0].then((res) => {
                if (res.success && res.deckid) {
                    const modal = getModalInstance(importModalEl);
                    if (modal) {
                        modal.hide();
                    }
                    if (window.location.href.includes('deck.php?id=' + res.deckid)) {
                        window.location.reload();
                    } else {
                        window.location.href = M.cfg.wwwroot + '/blocks/quickcards/deck.php?id=' + res.deckid;
                    }
                }
                return true;
            }).catch((err) => {
                importSubmitBtn.disabled = false;
                Notification.exception(err);
            });
        });
    }

    if (importCsvFileInput) {
        importCsvFileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) {
                return;
            }
            const reader = new FileReader();
            reader.onload = (event) => {
                const fileText = event.target.result || '';
                if (importCsvTextArea) {
                    importCsvTextArea.value = fileText;
                }
                const isExisting = (currentImportTargetDeckId > 0) || (
                    document.getElementById('quickcards-import-csv-dest-existing') &&
                    document.getElementById('quickcards-import-csv-dest-existing').checked
                );
                if (!isExisting && importCsvNameInput && importCsvNameInput.value.trim() === '') {
                    const allLines = fileText.split(/\r?\n/).map((l) => l.trim()).filter((l) => l !== '');
                    const anki = extractAnkiHeaders(allLines);
                    importCsvNameInput.value = anki.deck || file.name.replace(/\.[^.]+$/, '');
                }
                updateCsvPreview();
            };
            reader.readAsText(file);
        });
    }

    if (importCsvTextArea) {
        importCsvTextArea.addEventListener('input', updateCsvPreview);
    }
    if (importCsvNameInput) {
        importCsvNameInput.addEventListener('input', updateCsvPreview);
    }
    if (importCsvDelimiterSelect) {
        importCsvDelimiterSelect.addEventListener('change', updateCsvPreview);
    }

    if (importCsvForm) {
        importCsvForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const targetDeckId = getImportTargetDeckId('csv');
            const isExisting = (currentImportTargetDeckId > 0) || (
                document.getElementById('quickcards-import-csv-dest-existing') &&
                document.getElementById('quickcards-import-csv-dest-existing').checked
            );

            if (isExisting && !targetDeckId) {
                return;
            }

            const deckname = importCsvNameInput ? importCsvNameInput.value.trim() : '';
            const csvcontent = importCsvTextArea ? importCsvTextArea.value.trim() : '';
            const courseid = importCsvCourseSelect ? parseInt(importCsvCourseSelect.value, 10) : 0;
            const delimiter = importCsvDelimiterSelect ? importCsvDelimiterSelect.value : 'auto';
            const duplicateaction = getDuplicateAction('csv');

            if (!csvcontent || (!isExisting && !deckname)) {
                return;
            }

            importCsvSubmitBtn.disabled = true;

            fetchMany([{
                methodname: 'block_quickcards_import_csv',
                args: {
                    deckname: deckname,
                    csvcontent: csvcontent,
                    courseid: courseid,
                    delimiter: delimiter,
                    deckid: targetDeckId,
                    duplicateaction: duplicateaction,
                },
            }])[0].then((res) => {
                if (res.success && res.deckid) {
                    const modal = getModalInstance(importModalEl);
                    if (modal) {
                        modal.hide();
                    }
                    if (window.location.href.includes('deck.php?id=' + res.deckid)) {
                        window.location.reload();
                    } else {
                        window.location.href = M.cfg.wwwroot + '/blocks/quickcards/deck.php?id=' + res.deckid;
                    }
                }
                return true;
            }).catch((err) => {
                importCsvSubmitBtn.disabled = false;
                Notification.exception(err);
            });
        });
    }

    ['json', 'csv'].forEach((mode) => {
        const destRadios = document.querySelectorAll(`input[name="quickcards-import-${mode}-dest"]`);
        destRadios.forEach((r) => {
            r.addEventListener('change', updateImportDestinationUI);
        });
        const deckSel = document.getElementById(`quickcards-import-${mode}-deck-select`);
        if (deckSel) {
            deckSel.addEventListener('change', updateImportDestinationUI);
        }
    });
};

export default {
    init: init,
};

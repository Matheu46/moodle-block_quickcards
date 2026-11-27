<?php
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

namespace block_quickcards;

use moodle_exception;

/**
 * Deck importer class for QuickCards.
 *
 * Imports flashcard decks from JSON payloads into the database,
 * optionally restoring SM-2 spaced repetition progress.
 *
 * @package     block_quickcards
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class importer {
    /** @var int Maximum allowed import payload size in bytes (5 MB). */
    public const MAX_PAYLOAD_BYTES = 5242880;

    /** @var int Maximum allowed number of cards per single import. */
    public const MAX_IMPORT_CARDS = 2000;

    /** @var int Maximum deck name length matching DB schema CHAR(255). */
    public const MAX_DECK_NAME_LENGTH = 255;

    /** @var float Maximum allowed SM-2 ease factor on import. */
    public const MAX_EASE_FACTOR = 5.0;

    /** @var int Maximum allowed SM-2 interval in days (~100 years). */
    public const MAX_INTERVAL_DAYS = 36500;

    /** @var int Maximum allowed SM-2 repetition count on import. */
    public const MAX_REPETITIONS = 10000;

    /** @var int Maximum JSON nesting depth allowed during decoding. */
    private const MAX_JSON_DEPTH = 16;

    /**
     * Whitelist a Moodle text format constant.
     *
     * @param int $format Raw format integer from import payload.
     * @return int Valid Moodle FORMAT_* constant.
     */
    private static function sanitize_format(int $format): int {
        $allowed = [FORMAT_MOODLE, FORMAT_HTML, FORMAT_PLAIN, FORMAT_MARKDOWN];
        return in_array($format, $allowed, true) ? $format : FORMAT_HTML;
    }

    /**
     * Sanitize imported card or deck text content to prevent stored XSS.
     *
     * @param string $content Raw text content.
     * @param int $format Validated Moodle FORMAT_* constant.
     * @return string Cleaned content.
     */
    private static function sanitize_content(string $content, int $format): string {
        $raw = clean_param(trim($content), PARAM_RAW);
        if ($raw === '') {
            return '';
        }
        return trim(clean_text($raw, $format));
    }

    /**
     * Sanitize and clamp imported SM-2 progress fields to safe bounds.
     *
     * @param array $prog Raw progress array from JSON.
     * @param int $now Current Unix timestamp.
     * @return array Clamped easefactor, intervaldays, repetitions, nextreview, lastreview.
     */
    private static function sanitize_progress(array $prog, int $now): array {
        $rawease = $prog['easefactor'] ?? sm2::DEFAULT_EASE_FACTOR;
        $ease = is_numeric($rawease) ? (float) $rawease : sm2::DEFAULT_EASE_FACTOR;
        if (!is_finite($ease)) {
            $ease = sm2::DEFAULT_EASE_FACTOR;
        }
        $ease = round(max(sm2::MINIMUM_EASE_FACTOR, min(self::MAX_EASE_FACTOR, $ease)), 2);

        $rawinterval = $prog['intervaldays'] ?? 0;
        $intervaldays = is_numeric($rawinterval) ? (int) $rawinterval : 0;
        $intervaldays = max(0, min(self::MAX_INTERVAL_DAYS, $intervaldays));

        $rawreps = $prog['repetitions'] ?? 0;
        $repetitions = is_numeric($rawreps) ? (int) $rawreps : 0;
        $repetitions = max(0, min(self::MAX_REPETITIONS, $repetitions));

        $maxnext = $now + (self::MAX_INTERVAL_DAYS * DAYSECS);
        if (!empty($prog['nextreview']) && is_numeric($prog['nextreview'])) {
            $nextreview = (int) $prog['nextreview'];
            if ($nextreview < 0) {
                $nextreview = $now;
            } else if ($nextreview > $maxnext) {
                $nextreview = $maxnext;
            }
        } else {
            $nextreview = $now;
        }

        $lastreview = null;
        if (!empty($prog['lastreview']) && is_numeric($prog['lastreview'])) {
            $lr = (int) $prog['lastreview'];
            if ($lr > 0) {
                $lastreview = min($now, $lr);
            }
        }

        return [
            'easefactor' => $ease,
            'intervaldays' => $intervaldays,
            'repetitions' => $repetitions,
            'nextreview' => $nextreview,
            'lastreview' => $lastreview,
        ];
    }

    /**
     * Parse and validate a QuickCards JSON payload without saving to the database.
     *
     * @param string $jsoncontent Raw JSON string.
     * @return array Validated structure with deck info and cards count.
     * @throws moodle_exception If JSON or schema is invalid.
     */
    public static function validate_payload(string $jsoncontent): array {
        if (strlen($jsoncontent) > self::MAX_PAYLOAD_BYTES) {
            throw new moodle_exception('importfiletoolarge', 'block_quickcards');
        }

        $data = json_decode($jsoncontent, true, self::MAX_JSON_DEPTH);
        if ($data === null || !is_array($data)) {
            throw new moodle_exception('invalidjson', 'block_quickcards');
        }

        if (empty($data['format']) || $data['format'] !== exporter::FORMAT_IDENTIFIER) {
            throw new moodle_exception('invalidfileformat', 'block_quickcards');
        }

        if (
            empty($data['version']) ||
            !is_string($data['version']) ||
            version_compare($data['version'], exporter::FORMAT_VERSION, '>')
        ) {
            $versionstr = !empty($data['version']) && is_scalar($data['version']) ? (string) $data['version'] : 'unknown';
            throw new moodle_exception('unsupportedversion', 'block_quickcards', '', $versionstr);
        }

        if (
            empty($data['deck']) ||
            !is_array($data['deck']) ||
            !is_string($data['deck']['name'] ?? null) ||
            empty(trim(clean_param($data['deck']['name'], PARAM_TEXT)))
        ) {
            throw new moodle_exception('nodeckname', 'block_quickcards');
        }

        if (!isset($data['cards']) || !is_array($data['cards'])) {
            throw new moodle_exception('nofileorcard', 'block_quickcards');
        }

        if (count($data['cards']) > self::MAX_IMPORT_CARDS) {
            throw new moodle_exception('importtoomanycards', 'block_quickcards', '', self::MAX_IMPORT_CARDS);
        }

        return $data;
    }

    /**
     * Import a deck and its cards from a JSON payload.
     *
     * @param string $jsoncontent Raw JSON string.
     * @param int $userid Target user ID.
     * @param int $courseid Optional associated course ID.
     * @param bool $restoreprogress Whether to restore SM-2 progress if present in the file.
     * @param int $deckid Optional destination deck ID (0 to create a new deck).
     * @param string $duplicateaction Action for duplicates: 'skip', 'update', or 'allow'.
     * @return array Import summary containing deckid, deckname, cardscount, updatedcount, skippedcount, and progressrestored.
     * @throws moodle_exception If validation or insertion fails.
     */
    public static function import_deck_from_json(
        string $jsoncontent,
        int $userid,
        int $courseid = 0,
        bool $restoreprogress = false,
        int $deckid = 0,
        string $duplicateaction = 'skip'
    ): array {
        global $DB;

        $data = self::validate_payload($jsoncontent);

        if (!in_array($duplicateaction, ['skip', 'update', 'allow'], true)) {
            $duplicateaction = 'skip';
        }

        $hasprogress = !empty($data['includesprogress']) && $restoreprogress;
        $now = time();

        $transaction = $DB->start_delegated_transaction();

        if ($deckid > 0) {
            $existingdeck = $DB->get_record(
                'block_quickcards_decks',
                ['id' => $deckid, 'userid' => $userid],
                '*',
                MUST_EXIST
            );
            $targetdeckid = (int) $existingdeck->id;
            $targetdeckname = $existingdeck->name;

            // Fetch existing cards to index for duplicate checking.
            $existingcards = $DB->get_records('block_quickcards_cards', ['deckid' => $targetdeckid]);
            $cardsbyquestion = [];
            foreach ($existingcards as $ec) {
                $qnorm = \core_text::strtolower(trim(strip_tags($ec->question)));
                if ($qnorm === '') {
                    $qnorm = \core_text::strtolower(trim($ec->question));
                }
                $cardsbyquestion[$qnorm] = $ec;
            }
        } else {
            $deckname = \core_text::substr(
                clean_param(trim($data['deck']['name']), PARAM_TEXT),
                0,
                self::MAX_DECK_NAME_LENGTH
            );
            if ($deckname === '') {
                throw new moodle_exception('nodeckname', 'block_quickcards');
            }
            $rawdesc = is_string($data['deck']['description'] ?? null) ? $data['deck']['description'] : '';
            $deckdescription = self::sanitize_content($rawdesc, FORMAT_HTML);
            $validatedcourseid = manager::validate_user_courseid($courseid, $userid);

            $newdeck = (object) [
                'userid' => $userid,
                'courseid' => $validatedcourseid,
                'name' => $deckname,
                'description' => $deckdescription,
                'timecreated' => $now,
                'timemodified' => $now,
            ];
            $targetdeckid = (int) $DB->insert_record('block_quickcards_decks', $newdeck);
            $targetdeckname = $deckname;
            $cardsbyquestion = [];
        }

        $cardscount = 0;
        $updatedcount = 0;
        $skippedcount = 0;

        // 2. Insert or update cards and review schedules.
        foreach ($data['cards'] as $carditem) {
            if (!is_array($carditem)) {
                continue;
            }

            if (!is_string($carditem['question'] ?? null) || !is_string($carditem['answer'] ?? null)) {
                continue;
            }

            $qformat = self::sanitize_format((int) ($carditem['questionformat'] ?? FORMAT_HTML));
            $aformat = self::sanitize_format((int) ($carditem['answerformat'] ?? FORMAT_HTML));

            $question = self::sanitize_content($carditem['question'], $qformat);
            $answer = self::sanitize_content($carditem['answer'], $aformat);

            if ($question === '' || $answer === '') {
                continue;
            }

            $qnorm = \core_text::strtolower(trim(strip_tags($question)));
            if ($qnorm === '') {
                $qnorm = \core_text::strtolower(trim($question));
            }

            $isduplicate = ($deckid > 0 && isset($cardsbyquestion[$qnorm]));

            if ($isduplicate) {
                if ($duplicateaction === 'skip') {
                    $skippedcount++;
                    continue;
                } else if ($duplicateaction === 'update') {
                    $existingcard = $cardsbyquestion[$qnorm];
                    $existingcard->answer = $answer;
                    $existingcard->answerformat = $aformat;
                    $existingcard->timemodified = $now;
                    $DB->update_record('block_quickcards_cards', $existingcard);

                    if ($hasprogress && !empty($carditem['progress']) && is_array($carditem['progress'])) {
                        $prog = self::sanitize_progress($carditem['progress'], $now);
                        $existingreview = $DB->get_record('block_quickcards_reviews', [
                            'cardid' => $existingcard->id,
                            'userid' => $userid,
                        ]);
                        if ($existingreview) {
                            $existingreview->easefactor = $prog['easefactor'];
                            $existingreview->intervaldays = $prog['intervaldays'];
                            $existingreview->repetitions = $prog['repetitions'];
                            $existingreview->nextreview = $prog['nextreview'];
                            $existingreview->lastreview = $prog['lastreview'];
                            $DB->update_record('block_quickcards_reviews', $existingreview);
                        } else {
                            $reviewrecord = (object) [
                                'cardid' => $existingcard->id,
                                'userid' => $userid,
                                'easefactor' => $prog['easefactor'],
                                'intervaldays' => $prog['intervaldays'],
                                'repetitions' => $prog['repetitions'],
                                'nextreview' => $prog['nextreview'],
                                'lastreview' => $prog['lastreview'],
                            ];
                            $DB->insert_record('block_quickcards_reviews', $reviewrecord);
                        }
                    }
                    $updatedcount++;
                    continue;
                }
            }

            $cardrecord = (object) [
                'deckid' => $targetdeckid,
                'userid' => $userid,
                'question' => $question,
                'questionformat' => $qformat,
                'answer' => $answer,
                'answerformat' => $aformat,
                'timecreated' => $now,
                'timemodified' => $now,
            ];
            $cardid = $DB->insert_record('block_quickcards_cards', $cardrecord);

            // Review schedule.
            if ($hasprogress && !empty($carditem['progress']) && is_array($carditem['progress'])) {
                $prog = self::sanitize_progress($carditem['progress'], $now);
                $reviewrecord = (object) [
                    'cardid' => $cardid,
                    'userid' => $userid,
                    'easefactor' => $prog['easefactor'],
                    'intervaldays' => $prog['intervaldays'],
                    'repetitions' => $prog['repetitions'],
                    'nextreview' => $prog['nextreview'],
                    'lastreview' => $prog['lastreview'],
                ];
            } else {
                $reviewrecord = (object) [
                    'cardid' => $cardid,
                    'userid' => $userid,
                    'easefactor' => sm2::DEFAULT_EASE_FACTOR,
                    'intervaldays' => 0,
                    'repetitions' => 0,
                    'nextreview' => $now,
                    'lastreview' => null,
                ];
            }
            $DB->insert_record('block_quickcards_reviews', $reviewrecord);
            $cardscount++;

            if ($deckid > 0) {
                $cardrecord->id = $cardid;
                $cardsbyquestion[$qnorm] = $cardrecord;
            }
        }

        if ($deckid > 0) {
            $existingdeck->timemodified = $now;
            $DB->update_record('block_quickcards_decks', $existingdeck);
        }

        $transaction->allow_commit();

        return [
            'deckid' => (int) $targetdeckid,
            'deckname' => $targetdeckname,
            'cardscount' => $cardscount,
            'updatedcount' => $updatedcount,
            'skippedcount' => $skippedcount,
            'progressrestored' => $hasprogress,
        ];
    }

    /**
     * Detect the best delimiter from a CSV/TSV text snippet.
     *
     * @param string $content Normalized CSV/TSV text.
     * @return string Single-character delimiter (\t, ;, or ,).
     */
    public static function detect_delimiter(string $content): string {
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }
            if (strpos($trimmed, "\t") !== false) {
                return "\t";
            }
            if (strpos($trimmed, ';') !== false) {
                return ';';
            }
            return ',';
        }
        return ',';
    }

    /**
     * Parse cards from CSV or tab-separated text (Anki / Quizlet / Excel).
     *
     * @param string $csvcontent Raw CSV/TSV text content.
     * @param string $delimiter Delimiter character or 'auto' for auto-detection.
     * @return array List of ['question' => string, 'answer' => string].
     */
    public static function parse_csv_cards(string $csvcontent, string $delimiter = 'auto'): array {
        if (strlen($csvcontent) > self::MAX_PAYLOAD_BYTES) {
            throw new moodle_exception('importfiletoolarge', 'block_quickcards');
        }

        // Strip UTF-8 BOM if present and normalize line endings.
        $normalized = preg_replace('/^\xEF\xBB\xBF/', '', $csvcontent);
        $normalized = str_replace(["\r\n", "\r"], "\n", (string) $normalized);

        if ($delimiter === 'auto' || $delimiter === '') {
            $delimiter = self::detect_delimiter($normalized);
        }

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $normalized);
        rewind($handle);

        $cards = [];
        $isfirstrow = true;
        $headerquestions = ['question', 'front', 'term', 'pergunta', 'frente', 'termo'];
        $headeranswers = ['answer', 'back', 'definition', 'resposta', 'verso', 'definicao', 'definição'];

        while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            if (count($row) < 2) {
                continue;
            }

            $question = self::sanitize_content((string) $row[0], FORMAT_HTML);
            $answer = self::sanitize_content((string) $row[1], FORMAT_HTML);

            if ($question === '' || $answer === '') {
                continue;
            }

            // Skip standard header row if detected on the first non-empty row.
            if ($isfirstrow) {
                $isfirstrow = false;
                $qlower = \core_text::strtolower($question);
                $alower = \core_text::strtolower($answer);
                if (in_array($qlower, $headerquestions, true) && in_array($alower, $headeranswers, true)) {
                    continue;
                }
            }

            if (count($cards) >= self::MAX_IMPORT_CARDS) {
                fclose($handle);
                throw new moodle_exception('importtoomanycards', 'block_quickcards', '', self::MAX_IMPORT_CARDS);
            }

            $cards[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        fclose($handle);
        return $cards;
    }

    /**
     * Import a new deck or append cards to an existing deck from CSV or tab-separated text (Anki / Quizlet / Excel).
     *
     * @param string $deckname Name for the new deck (optional if deckid > 0).
     * @param string $csvcontent Raw CSV/TSV text content.
     * @param int $userid Target user ID.
     * @param int $courseid Optional associated course ID.
     * @param string $delimiter Delimiter character or 'auto'.
     * @param string $description Optional deck description.
     * @param int $deckid Optional destination deck ID (0 to create a new deck).
     * @param string $duplicateaction Action for duplicates: 'skip', 'update', or 'allow'.
     * @return array Import summary containing deckid, deckname, cardscount, updatedcount, and skippedcount.
     * @throws moodle_exception If deck name is empty or no valid cards are found.
     */
    public static function import_deck_from_csv(
        string $deckname,
        string $csvcontent,
        int $userid,
        int $courseid = 0,
        string $delimiter = 'auto',
        string $description = '',
        int $deckid = 0,
        string $duplicateaction = 'skip'
    ): array {
        global $DB;

        $cleandeckname = \core_text::substr(
            clean_param(trim($deckname), PARAM_TEXT),
            0,
            self::MAX_DECK_NAME_LENGTH
        );

        if (!in_array($duplicateaction, ['skip', 'update', 'allow'], true)) {
            $duplicateaction = 'skip';
        }

        $cards = self::parse_csv_cards($csvcontent, $delimiter);
        if (empty($cards)) {
            throw new moodle_exception('nofileorcard', 'block_quickcards');
        }

        $now = time();
        $transaction = $DB->start_delegated_transaction();

        if ($deckid > 0) {
            $existingdeck = $DB->get_record(
                'block_quickcards_decks',
                ['id' => $deckid, 'userid' => $userid],
                '*',
                MUST_EXIST
            );
            $targetdeckid = (int) $existingdeck->id;
            $targetdeckname = $existingdeck->name;

            // Fetch existing cards to index for duplicate checking.
            $existingcards = $DB->get_records('block_quickcards_cards', ['deckid' => $targetdeckid]);
            $cardsbyquestion = [];
            foreach ($existingcards as $ec) {
                $qnorm = \core_text::strtolower(trim(strip_tags($ec->question)));
                if ($qnorm === '') {
                    $qnorm = \core_text::strtolower(trim($ec->question));
                }
                $cardsbyquestion[$qnorm] = $ec;
            }
        } else {
            if ($cleandeckname === '') {
                throw new moodle_exception('nodeckname', 'block_quickcards');
            }

            $validatedcourseid = manager::validate_user_courseid($courseid, $userid);

            $newdeck = (object) [
                'userid' => $userid,
                'courseid' => $validatedcourseid,
                'name' => $cleandeckname,
                'description' => self::sanitize_content($description, FORMAT_HTML),
                'timecreated' => $now,
                'timemodified' => $now,
            ];
            $targetdeckid = (int) $DB->insert_record('block_quickcards_decks', $newdeck);
            $targetdeckname = $cleandeckname;
            $cardsbyquestion = [];
        }

        $cardscount = 0;
        $updatedcount = 0;
        $skippedcount = 0;

        foreach ($cards as $carditem) {
            $question = $carditem['question'];
            $answer = $carditem['answer'];

            $qnorm = \core_text::strtolower(trim(strip_tags($question)));
            if ($qnorm === '') {
                $qnorm = \core_text::strtolower(trim($question));
            }

            $isduplicate = ($deckid > 0 && isset($cardsbyquestion[$qnorm]));

            if ($isduplicate) {
                if ($duplicateaction === 'skip') {
                    $skippedcount++;
                    continue;
                } else if ($duplicateaction === 'update') {
                    $existingcard = $cardsbyquestion[$qnorm];
                    $existingcard->answer = $answer;
                    $existingcard->timemodified = $now;
                    $DB->update_record('block_quickcards_cards', $existingcard);
                    $updatedcount++;
                    continue;
                }
            }

            $cardrecord = (object) [
                'deckid' => $targetdeckid,
                'userid' => $userid,
                'question' => $question,
                'questionformat' => FORMAT_HTML,
                'answer' => $answer,
                'answerformat' => FORMAT_HTML,
                'timecreated' => $now,
                'timemodified' => $now,
            ];
            $cardid = $DB->insert_record('block_quickcards_cards', $cardrecord);

            $reviewrecord = (object) [
                'cardid' => $cardid,
                'userid' => $userid,
                'easefactor' => sm2::DEFAULT_EASE_FACTOR,
                'intervaldays' => 0,
                'repetitions' => 0,
                'nextreview' => $now,
                'lastreview' => null,
            ];
            $DB->insert_record('block_quickcards_reviews', $reviewrecord);
            $cardscount++;

            if ($deckid > 0) {
                $cardrecord->id = $cardid;
                $cardsbyquestion[$qnorm] = $cardrecord;
            }
        }

        if ($deckid > 0) {
            $existingdeck->timemodified = $now;
            $DB->update_record('block_quickcards_decks', $existingdeck);
        }

        $transaction->allow_commit();

        return [
            'deckid' => (int) $targetdeckid,
            'deckname' => $targetdeckname,
            'cardscount' => $cardscount,
            'updatedcount' => $updatedcount,
            'skippedcount' => $skippedcount,
        ];
    }
}

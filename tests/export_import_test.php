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

use advanced_testcase;
use core_external\external_api;
use dml_missing_record_exception;
use block_quickcards\external\create_card;
use block_quickcards\external\create_deck;
use block_quickcards\external\import_csv;
use block_quickcards\external\import_deck;
use block_quickcards\external\submit_review;
use moodle_exception;

/**
 * Unit tests for QuickCards deck export and import (JSON and CSV/Anki/Quizlet).
 *
 * @package     block_quickcards
 * @category    test
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \block_quickcards\exporter
 * @covers      \block_quickcards\importer
 * @covers      \block_quickcards\external\import_deck
 * @covers      \block_quickcards\external\import_csv
 */
final class export_import_test extends advanced_testcase {
    /**
     * Test exporting a deck as JSON without study progress.
     */
    public function test_export_deck_json_without_progress(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $deck = create_deck::execute('Biology', 'Cell structures', 0);
        create_card::execute($deck['id'], 'What is ATP?', 'Energy currency of the cell');
        create_card::execute($deck['id'], 'What is DNA?', 'Genetic material');

        $payload = exporter::export_deck($deck['id'], (int) $user->id, false);

        $this->assertEquals(exporter::FORMAT_IDENTIFIER, $payload['format']);
        $this->assertEquals(exporter::FORMAT_VERSION, $payload['version']);
        $this->assertFalse($payload['includesprogress']);
        $this->assertEquals('Biology', $payload['deck']['name']);
        $this->assertEquals('Cell structures', $payload['deck']['description']);
        $this->assertCount(2, $payload['cards']);
        $this->assertEquals('What is ATP?', $payload['cards'][0]['question']);
        $this->assertEquals('Energy currency of the cell', $payload['cards'][0]['answer']);
        $this->assertArrayNotHasKey('progress', $payload['cards'][0]);
    }

    /**
     * Test exporting a deck as JSON including SM-2 study progress.
     */
    public function test_export_deck_json_with_progress(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $deck = create_deck::execute('Physics', '', 0);
        $card = create_card::execute($deck['id'], 'F = m * a', 'Second law of Newton');

        // Review the card once with rating Easy.
        submit_review::execute($card['id'], sm2::RATING_EASY);

        $json = exporter::export_deck_json($deck['id'], (int) $user->id, true);
        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['includesprogress']);
        $this->assertCount(1, $decoded['cards']);
        $this->assertArrayHasKey('progress', $decoded['cards'][0]);
        $this->assertEquals(1, $decoded['cards'][0]['progress']['repetitions']);
        $this->assertGreaterThan(0, $decoded['cards'][0]['progress']['intervaldays']);
        $this->assertGreaterThan(sm2::DEFAULT_EASE_FACTOR, $decoded['cards'][0]['progress']['easefactor']);
        $this->assertNotNull($decoded['cards'][0]['progress']['lastreview']);
    }

    /**
     * Test that a user cannot export another user's deck.
     */
    public function test_export_deck_user_isolation(): void {
        $this->resetAfterTest();

        $usera = $this->getDataGenerator()->create_user();
        $userb = $this->getDataGenerator()->create_user();

        $this->setUser($usera);
        $deck = create_deck::execute('Private Deck', '', 0);

        $this->expectException(dml_missing_record_exception::class);
        exporter::export_deck($deck['id'], (int) $userb->id, false);
    }

    /**
     * Test exporting a deck to CSV format and verifying UTF-8 BOM and escaping.
     */
    public function test_export_deck_csv(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $deck = create_deck::execute('Português', '', 0);
        create_card::execute($deck['id'], 'O que é "crase"?', 'Fusão de preposição + artigo, ex: à');

        $csv = exporter::export_deck_csv($deck['id'], (int) $user->id);

        // Starts with UTF-8 BOM.
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Question,Answer', $csv);
        $this->assertStringContainsString('Fusão de preposição + artigo, ex: à', $csv);

        // Verify parsed rows match original content.
        $parsed = importer::parse_csv_cards($csv, ',');
        $this->assertCount(1, $parsed);
        $this->assertEquals('O que é "crase"?', $parsed[0]['question']);
        $this->assertEquals('Fusão de preposição + artigo, ex: à', $parsed[0]['answer']);
    }

    /**
     * Test filename sanitization for JSON, JSON backup, and CSV exports.
     */
    public function test_generate_filename(): void {
        $today = date('Ymd');

        $this->assertEquals(
            "quickcards-biology-101-{$today}.json",
            exporter::generate_filename('Biology 101!', false, 'json')
        );
        $this->assertEquals(
            "quickcards-biology-101-backup-{$today}.json",
            exporter::generate_filename('Biology 101!', true, 'json')
        );
        $this->assertEquals(
            "quickcards-biology-101-{$today}.csv",
            exporter::generate_filename('Biology 101!', true, 'csv')
        );
        $this->assertEquals(
            "quickcards-deck-{$today}.json",
            exporter::generate_filename('!!!', false, 'json')
        );
    }

    /**
     * Test importing a JSON deck as fresh cards (ignoring progress in file).
     */
    public function test_import_json_fresh_cards(): void {
        global $DB;
        $this->resetAfterTest();

        $usera = $this->getDataGenerator()->create_user();
        $userb = $this->getDataGenerator()->create_user();

        // User A creates, studies, and exports a deck with progress.
        $this->setUser($usera);
        $decka = create_deck::execute('Shared Deck', 'From classmate', 0);
        $carda = create_card::execute($decka['id'], 'Capital of Japan', 'Tokyo');
        submit_review::execute($carda['id'], sm2::RATING_GOOD);
        $json = exporter::export_deck_json($decka['id'], (int) $usera->id, true);

        // User B imports it without restoring progress.
        $this->setUser($userb);
        $res = import_deck::execute($json, 0, false);
        $res = external_api::clean_returnvalue(import_deck::execute_returns(), $res);

        $this->assertTrue($res['success']);
        $this->assertEquals('Shared Deck', $res['deckname']);
        $this->assertEquals(1, $res['cardscount']);
        $this->assertFalse($res['progressrestored']);

        $importedcards = $DB->get_records('block_quickcards_cards', ['deckid' => $res['deckid']]);
        $this->assertCount(1, $importedcards);
        $importedcard = reset($importedcards);

        $review = $DB->get_record('block_quickcards_reviews', ['cardid' => $importedcard->id], '*', MUST_EXIST);
        $this->assertEquals(0, (int) $review->repetitions);
        $this->assertEquals(0, (int) $review->intervaldays);
        $this->assertEquals(sm2::DEFAULT_EASE_FACTOR, (float) $review->easefactor);
        $this->assertNull($review->lastreview);
    }

    /**
     * Test importing a JSON deck while restoring personal SM-2 progress.
     */
    public function test_import_json_restoring_progress(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $deck = create_deck::execute('My Backup Deck', 'Personal notes', 0);
        $card = create_card::execute($deck['id'], '2 + 2', '4');
        $rev1 = submit_review::execute($card['id'], sm2::RATING_EASY);

        $json = exporter::export_deck_json($deck['id'], (int) $user->id, true);

        // Import restoring progress.
        $res = import_deck::execute($json, 0, true);
        $res = external_api::clean_returnvalue(import_deck::execute_returns(), $res);

        $this->assertTrue($res['success']);
        $this->assertTrue($res['progressrestored']);
        $this->assertEquals(1, $res['cardscount']);

        $importedcards = $DB->get_records('block_quickcards_cards', ['deckid' => $res['deckid']]);
        $importedcard = reset($importedcards);

        $review = $DB->get_record('block_quickcards_reviews', ['cardid' => $importedcard->id], '*', MUST_EXIST);
        $this->assertEquals($rev1['repetitions'], (int) $review->repetitions);
        $this->assertEquals($rev1['intervaldays'], (int) $review->intervaldays);
        $this->assertEqualsWithDelta($rev1['easefactor'], (float) $review->easefactor, 0.01);
        $this->assertNotNull($review->lastreview);
    }

    /**
     * Test JSON import rejects malformed JSON.
     */
    public function test_import_json_invalid_syntax(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(moodle_exception::class);
        import_deck::execute('{not valid json', 0, false);
    }

    /**
     * Test JSON import rejects unrecognized format identifier.
     */
    public function test_import_json_invalid_format(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $payload = json_encode(['format' => 'other_app', 'version' => '1.0']);
        $this->expectException(moodle_exception::class);
        import_deck::execute($payload, 0, false);
    }

    /**
     * Test JSON import rejects unsupported future schema version.
     */
    public function test_import_json_unsupported_version(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $payload = json_encode([
            'format' => exporter::FORMAT_IDENTIFIER,
            'version' => '99.0',
            'deck' => ['name' => 'Future Deck'],
            'cards' => [],
        ]);

        $this->expectException(moodle_exception::class);
        import_deck::execute($payload, 0, false);
    }

    /**
     * Test importing tab-separated cards (Anki / Quizlet format) with auto-detection.
     */
    public function test_import_csv_tab_separated(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $tsv = "Front\tBack\n" .
            "Mitocôndria\tProdução de ATP\n" .
            "Ribossomo\tSíntese de proteínas\n\n";

        $res = import_csv::execute('Biologia Quizlet', $tsv, 0, 'auto');
        $res = external_api::clean_returnvalue(import_csv::execute_returns(), $res);

        $this->assertTrue($res['success']);
        $this->assertEquals('Biologia Quizlet', $res['deckname']);
        $this->assertEquals(2, $res['cardscount']);

        $cards = array_values($DB->get_records('block_quickcards_cards', ['deckid' => $res['deckid']], 'id ASC'));
        $this->assertCount(2, $cards);
        $this->assertEquals('Mitocôndria', $cards[0]->question);
        $this->assertEquals('Produção de ATP', $cards[0]->answer);
        $this->assertEquals('Ribossomo', $cards[1]->question);
        $this->assertEquals('Síntese de proteínas', $cards[1]->answer);

        // Verify SM-2 default reviews were initialized for each card.
        $this->assertEquals(2, $DB->count_records('block_quickcards_reviews', ['userid' => $user->id]));
    }

    /**
     * Test importing semicolon-separated CSV (Excel PT-BR) with Portuguese header and BOM.
     */
    public function test_import_csv_semicolon_excel(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $csv = "\xEF\xBB\xBFPergunta;Resposta\r\n" .
            "Capital do Brasil;Brasília\r\n" .
            "Maior rio;Amazonas\r\n";

        $res = import_csv::execute('Geografia', $csv, 0, 'auto');
        $res = external_api::clean_returnvalue(import_csv::execute_returns(), $res);

        $this->assertTrue($res['success']);
        $this->assertEquals(2, $res['cardscount']);
    }

    /**
     * Test full CSV export and re-import roundtrip.
     */
    public function test_csv_export_import_roundtrip(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $deck = create_deck::execute('Original CSV Deck', '', 0);
        create_card::execute($deck['id'], 'Termo 1, com vírgula', 'Definição 1');
        create_card::execute($deck['id'], 'Termo 2', 'Definição "com aspas"');

        $exportedcsv = exporter::export_deck_csv($deck['id'], (int) $user->id);

        $imported = import_csv::execute('Reimported CSV Deck', $exportedcsv, 0, 'comma');
        $imported = external_api::clean_returnvalue(import_csv::execute_returns(), $imported);

        $this->assertEquals(2, $imported['cardscount']);

        $cards = array_values($DB->get_records('block_quickcards_cards', ['deckid' => $imported['deckid']], 'id ASC'));
        $this->assertEquals('Termo 1, com vírgula', $cards[0]->question);
        $this->assertEquals('Definição 1', $cards[0]->answer);
        $this->assertEquals('Termo 2', $cards[1]->question);
        $this->assertEquals('Definição "com aspas"', $cards[1]->answer);
    }

    /**
     * Test CSV import rejects content with no valid cards.
     */
    public function test_import_csv_no_valid_cards(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(moodle_exception::class);
        import_csv::execute('Empty Deck', "Only one column\nAnother line without delimiter", 0, 'auto');
    }

    /**
     * Test guest users cannot call import web services.
     */
    public function test_import_guest_forbidden(): void {
        $this->resetAfterTest();
        $this->setGuestUser();

        $this->expectException(moodle_exception::class);
        import_csv::execute('Guest Deck', "Q1\tA1", 0, 'tab');
    }

    /**
     * Test importing JSON cards into an existing deck skipping duplicates.
     */
    public function test_import_json_into_existing_deck_skip_duplicate(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $deck = create_deck::execute('My Existing Deck', 'Initial description', 0);
        $card1 = create_card::execute($deck['id'], 'What is mitochondria?', 'Powerhouse of the cell');
        submit_review::execute($card1['id'], sm2::RATING_EASY);

        $payload = json_encode([
            'format' => exporter::FORMAT_IDENTIFIER,
            'version' => exporter::FORMAT_VERSION,
            'includesprogress' => false,
            'deck' => ['name' => 'Ignored Name'],
            'cards' => [
                ['question' => 'What is mitochondria?', 'answer' => 'New powerhouse definition'],
                ['question' => 'What is ribosome?', 'answer' => 'Protein factory'],
            ],
        ]);

        $res = import_deck::execute($payload, 0, false, $deck['id'], 'skip');
        $res = external_api::clean_returnvalue(import_deck::execute_returns(), $res);

        $this->assertTrue($res['success']);
        $this->assertEquals($deck['id'], $res['deckid']);
        $this->assertEquals('My Existing Deck', $res['deckname']);
        $this->assertEquals(1, $res['cardscount']);
        $this->assertEquals(1, $res['skippedcount']);
        $this->assertEquals(0, $res['updatedcount']);

        // Check cards in deck.
        $cards = array_values($DB->get_records('block_quickcards_cards', ['deckid' => $deck['id']], 'id ASC'));
        $this->assertCount(2, $cards);

        // First card answer was NOT modified because skip was used.
        $this->assertEquals('Powerhouse of the cell', $cards[0]->answer);
        $this->assertEquals('What is ribosome?', $cards[1]->question);
        $this->assertEquals('Protein factory', $cards[1]->answer);
    }

    /**
     * Test importing JSON cards into an existing deck updating duplicate card answers and keeping review progress.
     */
    public function test_import_json_into_existing_deck_update_duplicate(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $deck = create_deck::execute('Science Deck', '', 0);
        $card1 = create_card::execute($deck['id'], 'Light speed', '300,000 km/s');
        $reviewbefore = submit_review::execute($card1['id'], sm2::RATING_GOOD);

        $payload = json_encode([
            'format' => exporter::FORMAT_IDENTIFIER,
            'version' => exporter::FORMAT_VERSION,
            'includesprogress' => false,
            'deck' => ['name' => 'Science Deck'],
            'cards' => [
                ['question' => 'Light speed', 'answer' => 'Approx. 299,792,458 m/s'],
            ],
        ]);

        $res = import_deck::execute($payload, 0, false, $deck['id'], 'update');
        $res = external_api::clean_returnvalue(import_deck::execute_returns(), $res);

        $this->assertTrue($res['success']);
        $this->assertEquals(0, $res['cardscount']);
        $this->assertEquals(1, $res['updatedcount']);
        $this->assertEquals(0, $res['skippedcount']);

        // Card answer updated.
        $updatedcard = $DB->get_record('block_quickcards_cards', ['id' => $card1['id']], '*', MUST_EXIST);
        $this->assertEquals('Approx. 299,792,458 m/s', $updatedcard->answer);

        // Review state was preserved.
        $reviewafter = $DB->get_record('block_quickcards_reviews', ['cardid' => $card1['id']], '*', MUST_EXIST);
        $this->assertEquals($reviewbefore['repetitions'], (int) $reviewafter->repetitions);
        $this->assertEquals($reviewbefore['intervaldays'], (int) $reviewafter->intervaldays);
    }

    /**
     * Test importing JSON cards into an existing deck allowing duplicates.
     */
    public function test_import_json_into_existing_deck_allow_duplicate(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $deck = create_deck::execute('Duplicates Deck', '', 0);
        create_card::execute($deck['id'], 'Term', 'Definition A');

        $payload = json_encode([
            'format' => exporter::FORMAT_IDENTIFIER,
            'version' => exporter::FORMAT_VERSION,
            'includesprogress' => false,
            'deck' => ['name' => 'Duplicates Deck'],
            'cards' => [
                ['question' => 'Term', 'answer' => 'Definition B'],
            ],
        ]);

        $res = import_deck::execute($payload, 0, false, $deck['id'], 'allow');
        $res = external_api::clean_returnvalue(import_deck::execute_returns(), $res);

        $this->assertTrue($res['success']);
        $this->assertEquals(1, $res['cardscount']);
        $this->assertEquals(0, $res['skippedcount']);
        $this->assertEquals(0, $res['updatedcount']);

        $cards = $DB->get_records('block_quickcards_cards', ['deckid' => $deck['id']]);
        $this->assertCount(2, $cards);
    }

    /**
     * Test importing CSV cards into an existing deck.
     */
    public function test_import_csv_into_existing_deck(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $deck = create_deck::execute('Language Deck', '', 0);
        create_card::execute($deck['id'], 'Hello', 'Olá');

        $csv = "Hello\tOi\nGoodbye\tAdeus\nThank you\tObrigado";

        $res = import_csv::execute('', $csv, 0, 'tab', $deck['id'], 'skip');
        $res = external_api::clean_returnvalue(import_csv::execute_returns(), $res);

        $this->assertTrue($res['success']);
        $this->assertEquals($deck['id'], $res['deckid']);
        $this->assertEquals('Language Deck', $res['deckname']);
        $this->assertEquals(2, $res['cardscount']);
        $this->assertEquals(1, $res['skippedcount']);

        $cards = $DB->get_records('block_quickcards_cards', ['deckid' => $deck['id']]);
        $this->assertCount(3, $cards);
    }

    /**
     * Test importing cards into another user's deck is forbidden.
     */
    public function test_import_existing_deck_user_isolation(): void {
        $this->resetAfterTest();

        $usera = $this->getDataGenerator()->create_user();
        $userb = $this->getDataGenerator()->create_user();

        $this->setUser($usera);
        $deck = create_deck::execute('User A Deck', '', 0);

        // Switch to User B and try to import into User A's deck.
        $this->setUser($userb);

        $this->expectException(dml_missing_record_exception::class);
        import_csv::execute('', "Foreign Q\tForeign A", 0, 'tab', $deck['id'], 'skip');
    }

    /**
     * Test JSON import strips XSS payloads, normalizes invalid formats, and truncates long deck names.
     */
    public function test_import_json_sanitizes_xss_and_formats(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $longname = str_repeat('A', 300);
        $payload = json_encode([
            'format' => exporter::FORMAT_IDENTIFIER,
            'version' => exporter::FORMAT_VERSION,
            'includesprogress' => false,
            'deck' => [
                'name' => $longname,
                'description' => '<p>Notes</p><script>alert("xss")</script>',
            ],
            'cards' => [
                [
                    'question' => 'Safe Q <script>alert(1)</script><img src="x" onerror="alert(2)">',
                    'questionformat' => 999,
                    'answer' => 'Safe A <a href="javascript:alert(3)">click</a>',
                    'answerformat' => -5,
                ],
                [
                    // Card containing only a script tag must become empty after sanitization and be skipped.
                    'question' => '<script>evil()</script>',
                    'answer' => 'Valid answer',
                ],
            ],
        ]);

        $res = import_deck::execute($payload, 0, false);
        $res = external_api::clean_returnvalue(import_deck::execute_returns(), $res);

        $this->assertTrue($res['success']);
        $this->assertEquals(1, $res['cardscount']);

        $deck = $DB->get_record('block_quickcards_decks', ['id' => $res['deckid']], '*', MUST_EXIST);
        $this->assertEquals(importer::MAX_DECK_NAME_LENGTH, \core_text::strlen($deck->name));
        $this->assertStringNotContainsString('<script', $deck->description);
        $this->assertStringContainsString('Notes', $deck->description);

        $cards = array_values($DB->get_records('block_quickcards_cards', ['deckid' => $res['deckid']]));
        $this->assertCount(1, $cards);
        $this->assertEquals(FORMAT_HTML, (int) $cards[0]->questionformat);
        $this->assertEquals(FORMAT_HTML, (int) $cards[0]->answerformat);
        $this->assertStringNotContainsString('<script', $cards[0]->question);
        $this->assertStringNotContainsString('onerror', $cards[0]->question);
        $this->assertStringContainsString('Safe Q', $cards[0]->question);
        $this->assertStringNotContainsString('javascript:', $cards[0]->answer);
        $this->assertStringContainsString('Safe A', $cards[0]->answer);
    }

    /**
     * Test CSV import strips XSS payloads before saving to the database.
     */
    public function test_import_csv_sanitizes_xss(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $csv = "<script>alert(1)</script>Mitochondria\t<img src=x onerror=alert(1)>ATP\n";
        $res = import_csv::execute('Bio Safe', $csv, 0, 'tab');
        $res = external_api::clean_returnvalue(import_csv::execute_returns(), $res);

        $this->assertTrue($res['success']);
        $this->assertEquals(1, $res['cardscount']);

        $cards = array_values($DB->get_records('block_quickcards_cards', ['deckid' => $res['deckid']]));
        $this->assertCount(1, $cards);
        $this->assertStringNotContainsString('<script', $cards[0]->question);
        $this->assertStringContainsString('Mitochondria', $cards[0]->question);
        $this->assertStringNotContainsString('onerror', $cards[0]->answer);
        $this->assertStringContainsString('ATP', $cards[0]->answer);
    }

    /**
     * Test JSON import clamps extreme or malicious SM-2 progress values to safe bounds.
     */
    public function test_import_json_clamps_malicious_sm2_progress(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $now = time();
        $payload = json_encode([
            'format' => exporter::FORMAT_IDENTIFIER,
            'version' => exporter::FORMAT_VERSION,
            'includesprogress' => true,
            'deck' => ['name' => 'Clamped SM2'],
            'cards' => [
                [
                    'question' => 'Q1',
                    'answer' => 'A1',
                    'progress' => [
                        'easefactor' => 99999.99,
                        'intervaldays' => 999999999,
                        'repetitions' => 999999999,
                        'nextreview' => -500,
                        'lastreview' => $now + 9999999,
                    ],
                ],
            ],
        ]);

        $res = import_deck::execute($payload, 0, true);
        $res = external_api::clean_returnvalue(import_deck::execute_returns(), $res);

        $this->assertTrue($res['success']);
        $cards = array_values($DB->get_records('block_quickcards_cards', ['deckid' => $res['deckid']]));
        $review = $DB->get_record('block_quickcards_reviews', ['cardid' => $cards[0]->id], '*', MUST_EXIST);

        $this->assertEquals(importer::MAX_EASE_FACTOR, (float) $review->easefactor);
        $this->assertEquals(importer::MAX_INTERVAL_DAYS, (int) $review->intervaldays);
        $this->assertEquals(importer::MAX_REPETITIONS, (int) $review->repetitions);
        $this->assertGreaterThanOrEqual($now, (int) $review->nextreview);
        $this->assertLessThanOrEqual(time(), (int) $review->lastreview);
    }

    /**
     * Test JSON import rejects payloads exceeding MAX_PAYLOAD_BYTES.
     */
    public function test_import_json_rejects_oversized_payload(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $oversized = str_repeat('x', importer::MAX_PAYLOAD_BYTES + 1);
        $this->expectException(moodle_exception::class);
        importer::validate_payload($oversized);
    }

    /**
     * Test JSON import rejects decks exceeding MAX_IMPORT_CARDS.
     */
    public function test_import_json_rejects_too_many_cards(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $cards = array_fill(0, importer::MAX_IMPORT_CARDS + 1, ['question' => 'Q', 'answer' => 'A']);
        $payload = json_encode([
            'format' => exporter::FORMAT_IDENTIFIER,
            'version' => exporter::FORMAT_VERSION,
            'deck' => ['name' => 'Huge Deck'],
            'cards' => $cards,
        ]);

        $this->expectException(moodle_exception::class);
        importer::validate_payload($payload);
    }

    /**
     * Test CSV import rejects content exceeding MAX_IMPORT_CARDS.
     */
    public function test_import_csv_rejects_too_many_cards(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $csv = str_repeat("Q\tA\n", importer::MAX_IMPORT_CARDS + 1);
        $this->expectException(moodle_exception::class);
        importer::parse_csv_cards($csv, "\t");
    }

    /**
     * Test CSV export neutralizes potential spreadsheet formula injection payloads.
     */
    public function test_export_deck_csv_neutralizes_formula_injection(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $deck = create_deck::execute('Formula Safety Deck', '', 0);
        create_card::execute($deck['id'], '=HYPERLINK("https://evil.example","Click")', '+1+1');
        create_card::execute($deck['id'], '-2+3', '@SUM(A1:A2)');
        create_card::execute($deck['id'], 'Normal Question', 'Normal Answer');

        $csv = exporter::export_deck_csv($deck['id'], (int) $user->id);
        $parsed = importer::parse_csv_cards($csv, ',');

        $this->assertCount(3, $parsed);
        $this->assertStringStartsWith("'", $parsed[0]['question']);
        $this->assertStringStartsWith("'", $parsed[0]['answer']);
        $this->assertStringStartsWith("'", $parsed[1]['question']);
        $this->assertStringStartsWith("'", $parsed[1]['answer']);
        $this->assertEquals('Normal Question', $parsed[2]['question']);
        $this->assertEquals('Normal Answer', $parsed[2]['answer']);

        // Direct helper edge cases: leading tab, carriage return, and whitespace before '='.
        $this->assertEquals("'\t=cmd|' /C calc'!A0", exporter::sanitize_csv_cell("\t=cmd|' /C calc'!A0"));
        $this->assertEquals("' =1+1", exporter::sanitize_csv_cell(' =1+1'));
        $this->assertEquals('F = m * a', exporter::sanitize_csv_cell('F = m * a'));
        $this->assertEquals('', exporter::sanitize_csv_cell(''));
    }
}

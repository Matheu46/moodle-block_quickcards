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
use moodle_exception;

/**
 * Unit tests for QuickCards manager class.
 *
 * @package     block_quickcards
 * @category    test
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \block_quickcards\manager
 */
final class manager_test extends advanced_testcase {
    /**
     * Test is_enabled and require_enabled settings control.
     */
    public function test_plugin_enabled_setting(): void {
        $this->resetAfterTest();

        // Default when config is unset is true.
        unset_config('enabled', 'block_quickcards');
        $this->assertTrue(manager::is_enabled());

        // Explicitly enabled.
        set_config('enabled', 1, 'block_quickcards');
        $this->assertTrue(manager::is_enabled());

        // The require_enabled method does not throw when enabled.
        manager::require_enabled();

        // Explicitly disabled.
        set_config('enabled', 0, 'block_quickcards');
        $this->assertFalse(manager::is_enabled());

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage(get_string('plugindisabled', 'block_quickcards'));
        manager::require_enabled();
    }

    /**
     * Test cohort-based access restrictions.
     */
    public function test_cohort_access_restrictions(): void {
        global $CFG;
        require_once($CFG->dirroot . '/cohort/lib.php');

        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $cohort1 = $this->getDataGenerator()->create_cohort(['name' => 'Cohort Alpha']);
        $cohort2 = $this->getDataGenerator()->create_cohort(['name' => 'Cohort Beta']);

        // Add user1 to cohort1; user2 belongs to no cohort.
        cohort_add_member($cohort1->id, $user1->id);

        // No cohorts configured: everyone with capability has access.
        unset_config('allowedcohorts', 'block_quickcards');
        $this->assertEquals([], manager::get_allowed_cohort_ids());
        $this->assertTrue(manager::can_access((int) $user1->id));
        $this->assertTrue(manager::can_access((int) $user2->id));

        // Restrict to cohort1 and cohort2.
        set_config('allowedcohorts', $cohort1->id . ',' . $cohort2->id, 'block_quickcards');
        $this->assertEquals([(int) $cohort1->id, (int) $cohort2->id], manager::get_allowed_cohort_ids());

        // Member of cohort1 has access; non-member does not.
        $this->assertTrue(manager::can_access((int) $user1->id));
        $this->assertFalse(manager::can_access((int) $user2->id));

        // Site admin always bypasses cohort restrictions when plugin is enabled.
        $admin = get_admin();
        $this->assertTrue(manager::can_access((int) $admin->id));

        // When plugin is globally disabled, even cohort members and admins cannot access.
        set_config('enabled', 0, 'block_quickcards');
        $this->assertFalse(manager::can_access((int) $user1->id));
        $this->assertFalse(manager::can_access((int) $admin->id));

        // Re-enable plugin and verify require_access throws accessrestricted for non-member.
        set_config('enabled', 1, 'block_quickcards');
        manager::require_access((int) $user1->id);

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage(get_string('accessrestricted', 'block_quickcards'));
        manager::require_access((int) $user2->id);
    }

    /**
     * Test guest users are strictly prevented from accessing QuickCards.
     */
    public function test_guest_user_cannot_access(): void {
        $this->resetAfterTest();
        $this->setGuestUser();

        // The can_access method must return false for guest users.
        $this->assertFalse(manager::can_access());

        // The user_in_allowed_cohorts method must return false for guest users even with no cohort restriction.
        unset_config('allowedcohorts', 'block_quickcards');
        $this->assertFalse(manager::user_in_allowed_cohorts());

        // The require_access method must throw noguest exception for guest users.
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage(get_string('noguest', 'error'));
        manager::require_access();
    }

    /**
     * Test get_due_cards_for_study retrieves cards and supports shuffling.
     */
    public function test_get_due_cards_for_study(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $deckid = $DB->insert_record('block_quickcards_decks', (object) [
            'userid' => $user->id,
            'name' => 'Chinese Numbers',
            'courseid' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        for ($i = 1; $i <= 5; $i++) {
            $DB->insert_record('block_quickcards_cards', (object) [
                'deckid' => $deckid,
                'userid' => $user->id,
                'question' => "Number $i",
                'answer' => "Answer $i",
                'timecreated' => time(),
                'timemodified' => time(),
            ]);
        }

        // Ordered retrieval without shuffle.
        $ordered = manager::get_due_cards_for_study((int) $user->id, (int) $deckid, false);
        $this->assertCount(5, $ordered);
        $this->assertSame('Number 1', $ordered[0]['question']);
        $this->assertSame('Number 5', $ordered[4]['question']);

        // Retrieval with shuffle.
        $shuffled = manager::get_due_cards_for_study((int) $user->id, (int) $deckid, true);
        $this->assertCount(5, $shuffled);

        // Ensure all card questions are present regardless of order.
        $questions = array_column($shuffled, 'question');
        sort($questions);
        $this->assertEquals(['Number 1', 'Number 2', 'Number 3', 'Number 4', 'Number 5'], $questions);
    }

    /**
     * Test hascards flag in get_user_decks and get_deck_with_cards.
     */
    public function test_deck_hascards_flag(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $now = time();
        $deckid = $DB->insert_record('block_quickcards_decks', (object) [
            'userid' => $user->id,
            'name' => 'Empty Deck',
            'description' => '',
            'courseid' => 0,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);

        // Empty deck should have hascards = false.
        $decks = manager::get_user_decks((int) $user->id);
        $this->assertCount(1, $decks);
        $this->assertFalse($decks[0]['hascards']);
        $this->assertEquals(0, $decks[0]['totalcards']);

        $deckwithcards = manager::get_deck_with_cards((int) $deckid, (int) $user->id);
        $this->assertNotNull($deckwithcards);
        $this->assertFalse($deckwithcards['hascards']);
        $this->assertEquals(0, $deckwithcards['totalcards']);

        // Add a card to the deck.
        $DB->insert_record('block_quickcards_cards', (object) [
            'deckid' => $deckid,
            'userid' => $user->id,
            'question' => 'Sample Question',
            'answer' => 'Sample Answer',
            'timecreated' => $now,
            'timemodified' => $now,
        ]);

        // Deck with cards should have hascards = true.
        $decks = manager::get_user_decks((int) $user->id);
        $this->assertCount(1, $decks);
        $this->assertTrue($decks[0]['hascards']);
        $this->assertEquals(1, $decks[0]['totalcards']);

        $deckwithcards = manager::get_deck_with_cards((int) $deckid, (int) $user->id);
        $this->assertNotNull($deckwithcards);
        $this->assertTrue($deckwithcards['hascards']);
        $this->assertEquals(1, $deckwithcards['totalcards']);
    }
}

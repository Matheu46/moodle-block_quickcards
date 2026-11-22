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

/**
 * Unit tests for block_quickcards.
 *
 * @package     block_quickcards
 * @category    test
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickcards;

use advanced_testcase;
use block_quickcards;

/**
 * Unit tests for block_quickcards block instance.
 *
 * @package     block_quickcards
 * @category    test
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \block_quickcards
 */
final class block_test extends advanced_testcase {
    /**
     * Set up tests.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test that guest users receive empty block content.
     */
    public function test_guest_user_content_is_empty(): void {
        $this->setGuestUser();

        $block = \block_instance('quickcards');
        $this->assertInstanceOf(block_quickcards::class, $block);

        $content = $block->get_content();
        $this->assertNotEmpty($content);
        $this->assertSame('', $content->text);
    }

    /**
     * Test that user without decks sees empty notice and no quickadd button.
     */
    public function test_logged_user_without_decks(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $block = \block_instance('quickcards');
        $content = $block->get_content();

        $this->assertNotEmpty($content);
        $this->assertStringContainsString(get_string('nodecks', 'block_quickcards'), $content->text);
        $this->assertStringNotContainsString('data-action="quickadd-card"', $content->text);
    }

    /**
     * Test that user with decks sees quickadd button, modal, and deck dropdown.
     */
    public function test_logged_user_with_decks_sees_quickadd(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // Create a deck for this user.
        $now = time();
        $deckid = $DB->insert_record('block_quickcards_decks', (object) [
            'userid' => $user->id,
            'name' => 'Biologia Celular',
            'description' => 'Conceitos de Citologia',
            'courseid' => 0,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);

        $block = \block_instance('quickcards');
        $content = $block->get_content();

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('data-action="quickadd-card"', $content->text);
        $this->assertStringContainsString('quickadd-modal-', $content->text);
        $this->assertStringContainsString('Biologia Celular', $content->text);
        $this->assertStringContainsString('value="' . $deckid . '"', $content->text);
    }

    /**
     * Test that in course context, the course deck is preselected and displayed.
     */
    public function test_course_context_filtering(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $this->setUser($user);

        $now = time();
        $coursedeckid = $DB->insert_record('block_quickcards_decks', (object) [
            'userid' => $user->id,
            'name' => 'Baralho do Curso',
            'description' => 'Flashcards deste curso',
            'courseid' => $course->id,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);

        $otherdeckid = $DB->insert_record('block_quickcards_decks', (object) [
            'userid' => $user->id,
            'name' => 'Baralho Geral',
            'description' => 'Outros assuntos',
            'courseid' => 0,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);

        $page = new \moodle_page();
        $page->set_course($course);

        $block = new \block_quickcards();
        $block->page = $page;
        $block->init();

        $content = $block->get_content();

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('Baralho do Curso', $content->text);
        $this->assertStringNotContainsString('data-deck-id="' . $otherdeckid . '"', $content->text);
        $this->assertStringContainsString('value="' . $coursedeckid . '" selected', $content->text);
    }
}

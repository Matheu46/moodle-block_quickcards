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
 * Main block class file for block_quickcards.
 *
 * @package     block_quickcards
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * QuickCards block main class.
 *
 * @package    block_quickcards
 * @copyright  2026 Matheus Mathias
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_quickcards extends block_base {
    /**
     * Initializes the block title.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_quickcards');
    }

    /**
     * Defines page types where the block can be added.
     *
     * @return array
     */
    public function applicable_formats() {
        return [
            'my' => true,
            'course-view' => true,
        ];
    }

    /**
     * Allow only one instance of this block per page.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Generates and returns the block contents.
     *
     * @return stdClass|null
     */
    public function get_content() {
        global $USER, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            return $this->content;
        }

        // Ensure the local_quickcards plugin is present and the user has access.
        if (!class_exists('\local_quickcards\manager')) {
            return $this->content;
        }

        if (!\local_quickcards\manager::can_access((int) $USER->id)) {
            return $this->content;
        }

        $userid = (int) $USER->id;
        $courseid = (int) ($this->page->course->id ?? SITEID);
        $iscourse = ($courseid > 0 && $courseid !== (int) SITEID);

        $stats = \local_quickcards\manager::get_user_stats($userid);
        $alluserdecks = \local_quickcards\manager::get_user_decks($userid);

        $displaydecks = [];
        $deckoptions = [];
        foreach ($alluserdecks as $deck) {
            $iscurrent = ($iscourse && (int) $deck['courseid'] === $courseid);
            $deckoptions[] = [
                'id' => (int) $deck['id'],
                'name' => $deck['name'],
                'coursename' => $deck['coursename'] ?? '',
                'hascourse' => !empty($deck['hascourse']),
                'iscurrentcourse' => $iscurrent,
            ];

            if ($iscourse) {
                if ((int) $deck['courseid'] === $courseid) {
                    $displaydecks[] = $deck;
                }
            } else if (!empty($deck['hasdue'])) {
                $displaydecks[] = $deck;
            }
        }

        if (!$iscourse && empty($displaydecks) && !empty($alluserdecks)) {
            $displaydecks = array_slice($alluserdecks, 0, 3);
        }

        $mainurl = new moodle_url('/local/quickcards/view.php');
        if ($iscourse) {
            $mainurl->param('courseid', $courseid);
        }

        $uniqid = \html_writer::random_id('quickcards_');

        $renderdata = [
            'uniqid' => $uniqid,
            'iscourse' => $iscourse,
            'coursename' => $iscourse ? format_string($this->page->course->fullname) : '',
            'hasdue' => !empty($stats['hasdue']),
            'totaldue' => (int) $stats['totaldue'],
            'totalcards' => (int) $stats['totalcards'],
            'hasdecks' => !empty($displaydecks),
            'decks' => $displaydecks,
            'deckoptions' => $deckoptions,
            'mainurl' => $mainurl->out(false),
            'hasanydecks' => !empty($alluserdecks),
        ];

        $this->content->text = $OUTPUT->render_from_template('block_quickcards/block_content', $renderdata);

        $footerlink = html_writer::link(
            $mainurl,
            get_string('viewall', 'block_quickcards'),
            ['class' => 'btn btn-link btn-sm p-0']
        );
        $this->content->footer = $footerlink;

        return $this->content;
    }
}

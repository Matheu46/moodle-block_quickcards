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
 * Deck management page: list, add, edit, and delete cards within a deck.
 *
 * @package     block_quickcards
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

if (isguestuser()) {
    throw new \moodle_exception('noguest');
}

\block_quickcards\manager::require_access();

$deckid = required_param('id', PARAM_INT);
$page = max(0, optional_param('page', 0, PARAM_INT));
$search = trim(optional_param('search', '', PARAM_TEXT));
$status = optional_param('status', 'all', PARAM_ALPHA);
if (!in_array($status, ['all', 'due', 'new', 'learned'], true)) {
    $status = 'all';
}
$perpage = \block_quickcards\manager::CARDS_PER_PAGE;

$context = \context_system::instance();
require_capability('block/quickcards:use', $context);

$urlparams = ['id' => $deckid];
if ($search !== '') {
    $urlparams['search'] = $search;
}
if ($status !== 'all') {
    $urlparams['status'] = $status;
}
$baseurl = new \moodle_url('/blocks/quickcards/deck.php', $urlparams);
$pageurl = new \moodle_url($baseurl);
if ($page > 0) {
    $pageurl->param('page', $page);
}

$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$deck = \block_quickcards\manager::get_deck_with_cards($deckid, $USER->id, $page, $perpage, $search, $status);
if (!$deck) {
    throw new \moodle_exception('invalidrecord', 'error');
}
$page = $deck['page'];

$PAGE->set_title($deck['name'] . ' - ' . get_string('pluginname', 'block_quickcards'));
$PAGE->set_heading($deck['name']);

// Set up navbar.
$PAGE->navbar->add(
    get_string('mycards', 'block_quickcards'),
    new \moodle_url('/blocks/quickcards/view.php')
);
$PAGE->navbar->add($deck['name']);

$courses = \block_quickcards\manager::get_user_courses_options($USER->id);
$otherdecks = \block_quickcards\manager::get_user_other_decks($USER->id, $deckid);
$pagingbar = ($deck['filteredcount'] > $perpage)
    ? $OUTPUT->paging_bar($deck['filteredcount'], $page, $perpage, $baseurl)
    : '';

$templatecontext = [
    'deck' => $deck,
    'courses' => $courses,
    'hascourses' => !empty($courses),
    'otherdecks' => $otherdecks,
    'hasotherdecks' => !empty($otherdecks),
    'userdecks' => [
        [
            'id' => (int) $deck['id'],
            'name' => $deck['name'],
        ],
    ],
    'hasuserdecks' => true,
    'pagingbar' => $pagingbar,
    'haspagingbar' => !empty($pagingbar),
    'search' => $search,
    'hassearch' => ($search !== ''),
    'status' => $status,
    'isfiltered' => ($search !== '' || $status !== 'all'),
    'statusall' => ($status === 'all'),
    'statusdue' => ($status === 'due'),
    'statusnew' => ($status === 'new'),
    'statuslearned' => ($status === 'learned'),
    'deckbaseurl' => (new \moodle_url('/blocks/quickcards/deck.php', ['id' => $deckid]))->out(false),
    'showingcounttext' => get_string('showingcount', 'block_quickcards', [
        'shown' => $deck['showncount'],
        'total' => $deck['filteredcount'],
    ]),
    'sesskey' => sesskey(),
];

// Initialize frontend AMD module.
$PAGE->requires->js_call_amd('block_quickcards/deck_manager', 'init', []);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_quickcards/deck', $templatecontext);
echo $OUTPUT->footer();

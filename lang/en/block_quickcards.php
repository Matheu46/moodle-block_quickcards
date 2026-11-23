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
 * Language strings for block_quickcards.
 *
 * @package     block_quickcards
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accessrestricted'] = 'Access to QuickCards is currently restricted to specific cohorts.';
$string['actions'] = 'Actions';
$string['addcard'] = 'Add Card';
$string['allcourses'] = 'All courses';
$string['alldone'] = 'All caught up! No reviews due today.';
$string['answer'] = 'Back (Answer / Explanation)';
$string['answer_help'] = 'The concise explanation or solution that you want to remember.';
$string['backtodeck'] = 'Back to Deck';
$string['backtodecks'] = 'Back to Decks';
$string['cancel'] = 'Cancel';
$string['card'] = 'Card';
$string['cardback'] = 'Back (Answer)';
$string['cardback_placeholder'] = 'What is the answer or definition?';
$string['cardcount'] = '{$a} cards';
$string['cardcreated'] = 'Card created successfully.';
$string['carddeleted'] = 'Card deleted successfully.';
$string['cardemptyerror'] = 'Both front and back content are required.';
$string['cardfront'] = 'Front (Question)';
$string['cardfront_placeholder'] = 'What is the question or key concept?';
$string['cardmoved'] = 'Card moved successfully.';
$string['cardof'] = 'Card {$a->current} of {$a->total}';
$string['cards'] = 'Cards';
$string['cardsaddedtodeck'] = 'Added {$a->count} card(s) to "{$a->name}".';
$string['cardsaved'] = 'Card created successfully!';
$string['cardsinfile'] = '{$a} cards found';
$string['cardupdated'] = 'Card updated successfully.';
$string['clearfilter'] = 'Clear filters';
$string['close'] = 'Close';
$string['confirmdeletecard'] = 'Are you sure you want to delete this card?';
$string['confirmdeletedeck'] = 'Are you sure you want to delete this deck? All its cards and review history will be permanently deleted.';
$string['confirmresetdeck'] = 'Are you sure you want to reset the study progress for "{$a}"? All cards in this deck will be reset to 0 repetitions and scheduled for review today.';
$string['congratulations'] = 'Congratulations!';
$string['containsstudyprogress'] = 'This file includes personal study progress.';
$string['course'] = 'Course';
$string['coursedeck'] = 'Linked Course';
$string['coursedecks'] = 'Decks in this course';
$string['createcard'] = 'Add Card';
$string['createdeck'] = 'Create Deck';
$string['csvcardsdetected'] = '{$a} valid cards detected';
$string['deck'] = 'Deck';
$string['deckcreated'] = 'Deck created successfully.';
$string['deckdeleted'] = 'Deck deleted successfully.';
$string['deckdescription'] = 'Description';
$string['deckdescription_placeholder'] = 'Optional notes about what this deck covers...';
$string['deckimported'] = 'Deck "{$a->name}" with {$a->count} cards imported successfully.';
$string['deckname'] = 'Deck Name';
$string['deckname_help'] = 'Give your deck a clear name, such as "Anatomy - Muscles" or "JavaScript Basics".';
$string['deckpreview'] = 'Deck Preview';
$string['deckreset'] = 'Deck study progress has been reset successfully.';
$string['decks'] = 'Decks';
$string['deckupdated'] = 'Deck updated successfully.';
$string['delete'] = 'Delete';
$string['deletecard'] = 'Delete Card';
$string['deletedeck'] = 'Delete Deck';
$string['delimiter'] = 'Separator';
$string['delimiter_auto'] = 'Auto-detect (Recommended)';
$string['delimiter_comma'] = 'Comma (,)';
$string['delimiter_semicolon'] = 'Semicolon (;)';
$string['delimiter_tab'] = 'Tab (Anki / Quizlet default)';
$string['duplicateaction'] = 'Duplicate cards';
$string['duplicateaction_help'] = 'How to handle cards with questions that already exist in the target deck.';
$string['duplicateallow'] = 'Allow duplicates (add anyway)';
$string['duplicateskip'] = 'Ignore duplicates (keep existing card)';
$string['duplicateupdate'] = 'Update existing card (replace answer)';
$string['duecardsstat'] = 'Due today';
$string['duecountbadge'] = '{$a} due';
$string['dueforreview'] = 'Due Reviews';
$string['duereviews'] = 'Cards due for review';
$string['edit'] = 'Edit';
$string['editcard'] = 'Edit Card';
$string['editdeck'] = 'Edit Deck';
$string['errorcreatingcard'] = 'Error creating card.';
$string['errorcreatingdeck'] = 'Error creating deck.';
$string['exportcontentonly'] = 'Cards only (Recommended for sharing)';
$string['exportcontentonlydesc'] = 'Exports deck details and all flashcards without your personal review history. Ideal for sharing with classmates.';
$string['exportcsv'] = 'CSV — Spreadsheet / Anki / Quizlet';
$string['exportcsvdesc'] = 'Exports questions and answers as a standard CSV table. Compatible with Excel, Google Sheets, Anki, and Quizlet.';
$string['exportdeck'] = 'Export Deck';
$string['exportdeckfor'] = 'Exporting deck:';
$string['exportjson'] = 'Download File';
$string['exportwithprogress'] = 'Cards + Study Progress (Personal backup)';
$string['exportwithprogressdesc'] = 'Includes your SM-2 intervals, ease factors, and repetition counts. Ideal for backing up or migrating your personal study progress.';
$string['filterall'] = 'All';
$string['filtercourse'] = 'Filter by course';
$string['filterdue'] = 'Due';
$string['filterlearned'] = 'Learned';
$string['filternew'] = 'New';
$string['filterstatus'] = 'Filter by status';
$string['flipcard'] = 'Flip card';
$string['generaldeck'] = 'General (Not tied to a course)';
$string['importcards'] = 'Import Cards';
$string['importcardsfor'] = 'Importing cards into: {$a}';
$string['importdeck'] = 'Import Deck';
$string['importdestination'] = 'Import destination';
$string['importexistingdeck'] = 'Add to an existing deck';
$string['importjson'] = 'Import JSON';
$string['importnewdeck'] = 'Create a new deck';
$string['importtabcsv'] = 'CSV / Anki / Quizlet';
$string['importtabjson'] = 'QuickCards (JSON)';
$string['invalidfileformat'] = 'The selected file is not a valid QuickCards export.';
$string['invalidjson'] = 'Could not parse JSON from the selected file.';
$string['inxdays'] = 'In {$a} days';
$string['keyboard_shortcuts'] = 'Shortcuts: [Space] Flip card &bull; [1] Again &bull; [2] Hard &bull; [3] Good &bull; [4] Easy';
$string['managecards'] = 'Manage Cards';
$string['movecard'] = 'Move Card';
$string['moveto'] = 'Move to Deck';
$string['mycards'] = 'My QuickCards';
$string['myreviews'] = 'My Reviews';
$string['never'] = 'Never reviewed';
$string['nextreview'] = 'Next Review';
$string['nocards'] = 'There are no cards in this deck yet.';
$string['nocardsdesc'] = 'Add cards with key concepts, definitions, or questions to start studying!';
$string['nocardsfound'] = 'No cards match your search or filter.';
$string['nocoursedecks'] = 'No flashcard decks linked to this course yet.';
$string['nodeckname'] = 'The export file does not contain a valid deck name.';
$string['nodecks'] = 'You have not created any decks yet.';
$string['nodecksdesc'] = 'Flashcard decks help you organize concepts and study with active recall. Create your first deck to get started!';
$string['nodecksfound'] = 'No decks match your search or filter.';
$string['noduecards'] = 'Great job! You have no cards due for review right now.';
$string['nofileorcard'] = 'The export file does not contain any cards.';
$string['notargetdecks'] = 'You have no other decks to move this card to.';
$string['pastecsvdata'] = 'Paste cards (Question & Answer per line)';
$string['pastecsvdata_placeholder'] = "What is mitochondria?\tPowerhouse of the cell\nCapital of France\tParis";
$string['plugindisabled'] = 'QuickCards is currently disabled on this site.';
$string['pluginname'] = 'QuickCards';
$string['privacy:metadata'] = 'QuickCards stores user flashcards, decks, and spaced repetition review intervals.';
$string['privacy:metadata:block_quickcards_cards'] = 'Stores individual flashcards within decks.';
$string['privacy:metadata:block_quickcards_cards:answer'] = 'The answer or explanation on the back of the flashcard.';
$string['privacy:metadata:block_quickcards_cards:deckid'] = 'The ID of the deck to which the flashcard belongs.';
$string['privacy:metadata:block_quickcards_cards:question'] = 'The question or prompt on the front of the flashcard.';
$string['privacy:metadata:block_quickcards_cards:timecreated'] = 'The timestamp when the card was created.';
$string['privacy:metadata:block_quickcards_cards:timemodified'] = 'The timestamp when the card was last modified.';
$string['privacy:metadata:block_quickcards_cards:userid'] = 'The ID of the user who created the flashcard.';
$string['privacy:metadata:block_quickcards_decks'] = 'Stores flashcard decks created by users.';
$string['privacy:metadata:block_quickcards_decks:courseid'] = 'The ID of the course linked to the deck, or 0 for general decks.';
$string['privacy:metadata:block_quickcards_decks:description'] = 'The description or topic summary of the flashcard deck.';
$string['privacy:metadata:block_quickcards_decks:name'] = 'The name of the flashcard deck.';
$string['privacy:metadata:block_quickcards_decks:timecreated'] = 'The timestamp when the deck was created.';
$string['privacy:metadata:block_quickcards_decks:timemodified'] = 'The timestamp when the deck was last modified.';
$string['privacy:metadata:block_quickcards_decks:userid'] = 'The ID of the user who owns the deck.';
$string['privacy:metadata:block_quickcards_reviews'] = 'Stores spaced repetition study progress and review history per card and user.';
$string['privacy:metadata:block_quickcards_reviews:cardid'] = 'The ID of the card reviewed.';
$string['privacy:metadata:block_quickcards_reviews:easefactor'] = 'The SuperMemo SM-2 ease factor determining review interval growth.';
$string['privacy:metadata:block_quickcards_reviews:intervaldays'] = 'The number of days until the next scheduled review.';
$string['privacy:metadata:block_quickcards_reviews:lastreview'] = 'The timestamp of the most recent review session.';
$string['privacy:metadata:block_quickcards_reviews:nextreview'] = 'The timestamp when the card is next due for review.';
$string['privacy:metadata:block_quickcards_reviews:repetitions'] = 'The count of consecutive successful reviews.';
$string['privacy:metadata:block_quickcards_reviews:userid'] = 'The ID of the user who performed the study session.';
$string['question'] = 'Front (Prompt / Question)';
$string['question_help'] = 'The question, term, or prompt that triggers active recall.';
$string['quickaddcard'] = 'New card';
$string['quickcards:addinstance'] = 'Add a new QuickCards block';
$string['quickcards:myaddinstance'] = 'Add a new QuickCards block to Dashboard';
$string['quickcards:use'] = 'Use QuickCards spaced repetition';
$string['rating_again'] = 'Again (1)';
$string['rating_easy'] = 'Easy (4)';
$string['rating_good'] = 'Good (3)';
$string['rating_hard'] = 'Hard (2)';
$string['repetitions'] = 'Repetitions';
$string['resetdeck'] = 'Reset Deck Progress';
$string['resetprogress'] = 'Reset Progress';
$string['restoreprogress'] = 'Restore study progress';
$string['restoreprogress_help'] = 'If enabled, previous review intervals and ease factors will be preserved. Leave unchecked to start studying these cards as new.';
$string['reviews'] = 'Reviews';
$string['save'] = 'Save';
$string['savecard'] = 'Save card';
$string['searchcards'] = 'Search cards...';
$string['searchdecks'] = 'Search decks...';
$string['selectcsvfile'] = 'Upload CSV / TXT file (optional)';
$string['selectcsvfile_help'] = 'Upload a .csv or .txt file, or paste your list directly below.';
$string['selectjsonfile'] = 'Select QuickCards JSON file';
$string['selectjsonfile_help'] = 'Choose a .json file exported from QuickCards.';
$string['selecttargetdeck'] = 'Select destination deck';
$string['sessioncomplete'] = 'Session completed!';
$string['sessioncompletedesc'] = 'You have reviewed all due cards for this session.';
$string['servererror'] = 'A server error occurred. Please try again.';
$string['setting_allowedcohorts'] = 'Allowed cohorts';
$string['setting_allowedcohorts_desc'] = 'Restrict QuickCards access to members of the selected cohorts. Leave empty to allow all authenticated users with permission. Site administrators always retain access.';
$string['setting_enabled'] = 'Enable QuickCards';
$string['setting_enabled_desc'] = 'If enabled, users with permission can access their flashcards and spaced repetition study sessions. If disabled, the entire plugin is deactivated.';
$string['setting_nocohorts'] = 'No cohorts exist on this site yet. Create cohorts in Site administration > Users > Accounts > Cohorts to restrict access by cohort. Currently, all users with permission can access QuickCards.';
$string['showanswer'] = 'Show Answer';
$string['showanswerspace'] = 'Show Answer (Space)';
$string['showingcount'] = 'Showing {$a->shown} of {$a->total} cards';
$string['showingdeckscount'] = 'Showing {$a->shown} of {$a->total} decks';
$string['study'] = 'Study';
$string['studyagain'] = 'Study Again';
$string['studyall'] = 'Study All Due';
$string['studydeck'] = 'Study {$a}';
$string['targetdeck'] = 'Deck';
$string['today'] = 'Due today';
$string['totalcards'] = 'Total Cards';
$string['totalcardsstat'] = 'Total cards';
$string['totaldecks'] = 'Total Decks';
$string['trydifferentsearch'] = 'Try searching with different keywords or reset your filters.';
$string['unsupportedversion'] = 'Unsupported export file version: {$a}.';
$string['viewall'] = 'Open QuickCards';
$string['withduecards'] = 'With due reviews';

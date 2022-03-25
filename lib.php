<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lib fiel for local_quiz_disablecorrectanswers.
 *
 * @package     local_quiz_disablecorrectanswers
 * @author      Donald Barrett <donaldb@skills.org.nz>
 * @copyright   2022 onwards, Skills Consulting Group Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// No direct access.
defined('MOODLE_INTERNAL') || die();

/**
 * An example function following the frankenstyle naming.
 */
function local_quiz_disablecorrectanswers_coursemodule_standard_elements(\moodleform $formwrapper, \MoodleQuickForm $mform): void {
    if (!$formwrapper instanceof mod_quiz_mod_form) {
        // Not a quiz.
        return;
    }
    $quizconfig = get_config('quiz');

    $visiblename = get_string('additionalquestionbehaviour', 'local_quiz_disablecorrectanswers');
    $mform->addElement('header', 'additionalquestionbehaviour', $visiblename);

    $visiblename = get_string('disablealreadycorrectquestions', 'local_quiz_disablecorrectanswers');
    $mform->addElement('selectyesno', 'disablecorrect', $visiblename);
    $mform->addHelpButton('disablecorrect', 'disablealreadycorrectquestions', 'local_quiz_disablecorrectanswers');
    $mform->setAdvanced('disablecorrect', $quizconfig->disablecorrect_adv);
    $mform->setDefault('disablecorrect', $quizconfig->disablecorrect);

    $visiblename = get_string('disablealreadycorrectquestions_showcorrect', 'local_quiz_disablecorrectanswers');
    $mform->addElement('selectyesno', 'disablecorrect_showcorrect', $visiblename);
    $mform->addHelpButton('disablecorrect_showcorrect', 'disablealreadycorrectquestions_showcorrect', 'local_quiz_disablecorrectanswers');
    $mform->setAdvanced('disablecorrect_showcorrect', $quizconfig->disablecorrect_showcorrect_adv);
    $mform->setDefault('disablecorrect_showcorrect', $quizconfig->disablecorrect_showcorrect);

    $visiblename = get_string('disableshowcorrectforstudent', 'local_quiz_disablecorrectanswers');
    $mform->addElement('selectyesno', 'disableshowcorrectforstudent', $visiblename);
    $mform->addHelpButton('disableshowcorrectforstudent', 'disableshowcorrectforstudent', 'quiz');
    $mform->setAdvanced('disableshowcorrectforstudent', $quizconfig->disableshowcorrectforstudent_adv);
    $mform->setDefault('disableshowcorrectforstudent', $quizconfig->disableshowcorrectforstudent);
}
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
 * Quiz attempt things for local_quiz_disablecorrectanswers.
 *
 * @package     local_quiz_disablecorrectanswers
 * @author      Donald Barrett <donaldb@skills.org.nz>
 * @copyright   2022 onwards, Skills Consulting Group Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_quiz_disablecorrectanswers;

// No direct access.

use mod_quiz_renderer;

defined('MOODLE_INTERNAL') || die();

class quiz_attempt extends \quiz_attempt {
    public function __construct($attempt, $quiz, $cm, $course, $loadquestions = true) {
        parent::__construct($attempt, $quiz, $cm, $course, $loadquestions);
    }

    public function get_display_options($reviewing) {
        print_object("overridden get_display_options :D");
        return parent::get_display_options($reviewing);
    }

    public function get_question_status($slot, $showcorrectness) {
        print_object('overridden get_question_state');
        return parent::get_question_status($slot, $showcorrectness);
    }

    protected function render_question_helper($slot, $reviewing, $thispageurl, mod_quiz_renderer $renderer, $seq) {
        print_object('overridden render_question_helper');
        return parent::render_question_helper($slot, $reviewing, $thispageurl, $renderer, $seq);
    }

    public function process_finish($timestamp, $processsubmitted, $timefinish = null) {
        parent::process_finish($timestamp, $processsubmitted, $timefinish);
    }

    public static function create($attemptid) {
        return self::create_helper(['id' => $attemptid]);
    }

    protected static function create_helper($conditions) {
        global $DB;

        $attempt = $DB->get_record('quiz_attempts', $conditions, '*', MUST_EXIST);
        $quiz = \quiz_access_manager::load_quiz_and_settings($attempt->quiz);
        $course = $DB->get_record('course', array('id' => $quiz->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $course->id, false, MUST_EXIST);

        // Update quiz with override information.
        $quiz = quiz_update_effective_access($quiz, $attempt->userid);

        return new quiz_attempt($attempt, $quiz, $cm, $course);
    }

    public function manual_grade_question($slot, $comment, $mark, $commentformat = null) {
        // Todo.
    }

    public function customgrading() {
        // Todo.
    }

    public function disablecorrect() {
        // Todo.
    }

    public function disableshowcorrectforstudent() {
        // Todo.
    }

    public function get_last_complete_attempt() {
        // Todo.
    }
}
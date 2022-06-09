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
 * Quiz attempt things for local_quizadditionalbehaviour.
 *
 * @package     local_quizadditionalbehaviour
 * @author      Donald Barrett <donaldb@skills.org.nz>
 * @copyright   2022 onwards, Skills Consulting Group Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_quizadditionalbehaviour;

use mod_quiz_renderer;
use quiz_attempt as core_quiz_attempt;
use quiz_access_manager;
use question_state;
use question_engine as core_question_engine;
use local_quizadditionalbehaviour\question\question_engine as local_question_engine;
use context_module;
use context_course;
use stdClass;
use mod_quiz\event\question_manually_graded;
use coding_exception;
use html_writer;
use dml_exception;
use mod_quiz_display_options;
use question_display_options;
use lang_string;
use dml_transaction_exception;

/**
 * Overridden quiz_attempt class to add additional functionality for this additional quiz behaviour.
 */
class quiz_attempt extends core_quiz_attempt {
    /**
     * Quiz attempt constructor that does the core things and then non-core things.
     *
     * @param $attempt
     * @param $quiz
     * @param $cm
     * @param $course
     * @param $loadquestions
     * @throws coding_exception
     * @throws dml_exception
     */
    public function __construct($attempt, $quiz, $cm, $course, $loadquestions = true) {
        // Need to do self quba.
        $loadquestions = false;
        parent::__construct($attempt, $quiz, $cm, $course, $loadquestions);
        $this->load_questions();
    }

    /**
     * Override display options if doing non-core things.
     *
     * @param $reviewing
     * @return mod_quiz_display_options|question_display_options|null
     * @throws dml_exception
     */
    public function get_display_options($reviewing) {
        global $USER, $DB;
        if (!$reviewing) {
            // Do the core things.
            return parent::get_display_options($reviewing);
        }
        // Do the non-core things.
        if (is_null($this->reviewoptions)) {
            $this->reviewoptions = quiz_get_review_options($this->get_quiz(),
                $this->attempt, $this->quizobj->get_context());
            if ($this->is_own_preview()) {
                // It should always be possible for a teacher to review their own preview.
                $this->reviewoptions->attempt = true;
            }

            if ($this->disableshowcorrectforstudent()) {
                $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student']);
                $coursecontext = context_course::instance($this->get_courseid());
                $hasstudentrole = user_has_role_assignment($USER->id, $studentroleid, $coursecontext->id);
                $this->reviewoptions->truecorrectness = $this->reviewoptions->correctness;
                $this->reviewoptions->correctness = $hasstudentrole;
            }
        }
        return $this->reviewoptions;
    }

    /**
     * Get question status if using additional behaviour otherwise return the core things.
     *
     * @param $slot
     * @param $showcorrectness
     * @return lang_string|string
     * @throws coding_exception
     */
    public function get_question_status($slot, $showcorrectness) {
        $lastcompleteattempt = $this->get_last_complete_attempt();
        if ($this->disablecorrect() && !empty($lastcompleteattempt) && $lastcompleteattempt[$slot]->correct) {
            return get_string('previouslycompleted', 'local_quizadditionalbehaviour');
        } else {
            return parent::get_question_status($slot, $showcorrectness);
        }
    }

    /**
     * Overridden render_question_helper. The customisations are only called if the quiz is
     * configured to do so otherwise the core things are done.
     *
     * @param $slot
     * @param $reviewing
     * @param $thispageurl
     * @param mod_quiz_renderer $renderer
     * @param $seq
     * @return string
     * @throws coding_exception
     */
    protected function render_question_helper($slot, $reviewing, $thispageurl, mod_quiz_renderer $renderer, $seq) {
        $useparent = true;
        if (!empty($this->quizobj->get_quiz()->disablecorrect) && $this->get_attempt_number() > 1) {
            $useparent = false;
        }
        if ($this->customgrading() && !isset($displayoptions->passed)) {
            $useparent = false;
        }
        if ($useparent) {
            return parent::render_question_helper($slot, $reviewing, $thispageurl, $renderer, $seq);
        }
        // Use overridden code with some of the core code.
        $originalslot = $this->get_original_slot($slot);
        $number = $this->get_question_number($originalslot);
        $displayoptions = $this->get_display_options_with_edit_link($reviewing, $slot, $thispageurl);

        if ($slot != $originalslot) {
            $originalmaxmark = $this->get_question_attempt($slot)->get_max_mark();
            $this->get_question_attempt($slot)->set_max_mark($this->get_question_attempt($originalslot)->get_max_mark());
        }

        if ($this->can_question_be_redone_now($slot)) {
            $displayoptions->extrainfocontent = $renderer->redo_question_button(
                $slot, $displayoptions->readonly);
        }

        if ($displayoptions->history && $displayoptions->questionreviewlink) {
            $links = $this->links_to_other_redos($slot, $displayoptions->questionreviewlink);
            if ($links) {
                $displayoptions->extrahistorycontent = html_writer::tag('p',
                    get_string('redoesofthisquestion', 'quiz', $renderer->render($links)));
            }
        }

        // Additions.
        $userattempts = quiz_get_user_attempts($this->get_quiz()->id, $this->attempt->userid, 'finished');
        $quiz = $this->quizobj->get_quiz();
        $coursemodule = $this->get_cm();
        $course = $this->get_course();

        if (!empty($quiz->disablecorrect) && $this->get_attempt_number() > 1) {
            $qattempt = $this->get_last_complete_attempt();
            $outoforder = $qattempt[$slot]->timecreated > $this->get_submitted_date() && $reviewing;
            if ($qattempt[$slot]->correct && !$outoforder) {
                $displayoptions->passed_question = false;
                $displayoptions->passed = $qattempt[$slot]->correct;

                // The settings have asked us to deploy the prev completed attempt question.
                if (!empty($quiz->disablecorrectshowcorrect)) {
                    foreach ($userattempts as $key => $userattempt) {
                        $oldqattempt = new quiz_attempt($userattempt, $quiz, $coursemodule, $course);
                        $gradedright = $oldqattempt->quba->get_question_state($slot) == question_state::$gradedright;
                        $manuallygradedright = $oldqattempt->quba->get_question_state($slot) == question_state::$mangrright;
                        $passedquestion = $displayoptions->passed_question;
                        if (!$passedquestion && ($gradedright || $manuallygradedright)) {
                            $displayoptions->passed_question = $oldqattempt->quba->get_question_attempt($slot);
                        }
                    }
                }
            }
        }

        if ($this->customgrading() && !isset($displayoptions->passed)) {
            $displayoptions->viewcustomgrading = 1;
            $displayoptions->quizattemptid = $this->get_attemptid();
            if ($this->get_attempt_number() > 1) {
                $prevoutput = [];
                $displayoptions->displayAnswerOnly = true;

                // Remove the most recent attempt as this is for custom grade they already see it.
                array_pop($userattempts);
                foreach ($userattempts as $key => $userattempt) {
                    $qattempt = new quiz_attempt($userattempt, $quiz, $coursemodule, $course);
                    $prevoutput[] = $qattempt->quba->render_question($slot, $displayoptions, $number);
                }
                $displayoptions->displayAnswerOnly = false;
                $displayoptions->viewprevanswers = 1;
                $displayoptions->prevanswers = $prevoutput;
            }
        } else {
            $displayoptions->viewprevanswers = 0;
        }

        if ($seq === null) {
            $output = $this->quba->render_question($slot, $displayoptions, $number);
        } else {
            $output = $this->quba->render_question_at_step($slot, $seq, $displayoptions, $number);
        }

        if ($slot != $originalslot) {
            $this->get_question_attempt($slot)->set_max_mark($originalmaxmark);
        }

        return $output;
    }

    /**
     * Ensure that any previously answered correctly questions are shifted to the new attempt.
     *
     * @param $timestamp
     * @param $processsubmitted
     * @param $timefinish
     * @return void
     * @throws coding_exception
     */
    public function process_finish($timestamp, $processsubmitted, $timefinish = null) {
        // Do the overriden things.
        if ($this->disablecorrect()) {
            $qattempt = $this->get_last_complete_attempt();
            $commentstring = get_string('manualgradecomment', 'local_quizadditionalbehaviour');
            foreach ($this->get_slots('all') as $slot) {
                if ($qattempt[$slot]->correct) {
                    $this->manual_grade_question($slot, $commentstring, $qattempt[$slot]->maxMark, 1);
                }
            }
        }
    }

    /**
     * Ensure that the overridden quiz_attempt object is returned.
     *
     * @param $attemptid
     * @return quiz_attempt|core_quiz_attempt
     */
    public static function create($attemptid) {
        return self::create_helper(['id' => $attemptid]);
    }

    /**
     * Ensures that an overridden quiz_attempt object that has the additional behaviour
     * working is returned.
     *
     * @param $conditions
     * @return quiz_attempt
     * @throws coding_exception
     * @throws dml_exception
     */
    protected static function create_helper($conditions) {
        global $DB;

        $attempt = $DB->get_record('quiz_attempts', $conditions, '*', MUST_EXIST);
        $quiz = quiz_access_manager::load_quiz_and_settings($attempt->quiz);
        $course = $DB->get_record('course', array('id' => $quiz->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $course->id, false, MUST_EXIST);

        // Update quiz with override information.
        $quiz = quiz_update_effective_access($quiz, $attempt->userid);

        return new quiz_attempt($attempt, $quiz, $cm, $course);
    }

    /**
     * Manually grades a question. This is for quiz_attempts where a question was previously
     * marked as correct.
     *
     * @param $slot
     * @param $comment
     * @param $mark
     * @param $commentformat
     * @return void
     * @throws dml_transaction_exception
     * @throws coding_exception
     */
    public function manual_grade_question($slot, $comment, $mark, $commentformat = null) {
        global $DB;
        $this->quba->manual_grade($slot, $comment, $mark, $commentformat);
        core_question_engine::save_questions_usage_by_activity($this->quba);

        $transaction = $DB->start_delegated_transaction();
        $this->process_submitted_actions(time());
        $transaction->allow_commit();

        $params = [
            'objectid' => $this->get_question_attempt($slot)->get_question()->id,
            'courseid' => $this->get_courseid(),
            'context' => context_module::instance($this->get_cmid()),
            'other' => [
                'quizid' => $this->get_quizid(),
                'attemptid' => $this->get_attemptid(),
                'slot' => $slot,
            ],
        ];

        $event = question_manually_graded::create($params);
        $event->trigger();
    }

    /**
     * Checks if custom grading can happen.
     *
     * @return bool
     * @throws coding_exception
     */
    public function customgrading() {
        $quiz = $this->quizobj->get_quiz();
        $quizcontext = $this->get_quizobj()->get_context();
        $customgrading = $quiz->customgrading ?? false;
        $cangradequiz = has_capability('mod/quiz:grade', $quizcontext);
        return (!empty($customgrading) && $cangradequiz);
    }

    /**
     * Checks if this attempt needs to disable any correct responses.
     *
     * @return bool
     */
    public function disablecorrect() {
        return (!empty($this->quizobj->get_quiz()->disablecorrect) && $this->get_attempt_number() > 1);
    }

    /**
     * Checks if this quiz is configured to disable showing the correct response for the student.
     * @return bool
     */
    public function disableshowcorrectforstudent() {
        return (!empty($this->quizobj->get_quiz()->disableshowcorrectforstudent));
    }

    /**
     * Gets the last complete quiz_attempt to help facilitate the correctness of the previous quiz_attempt.
     *
     * @return array
     * @throws coding_exception
     */
    public function get_last_complete_attempt() {
        if (!$this->disablecorrect()) {
            return [];
        }
        $userattempts = [];
        $quiz = $this->get_quiz();
        foreach (quiz_get_user_attempts($quiz->id, $this->attempt->userid, 'finished') as $key => $value) {
            $userattempts[$value->attempt] = $value;
        }
        if (!($this->attempt->attempt > 1)) {
            return [];
        }
        $attempt = $userattempts[(int)$this->attempt->attempt - 1];
        $coursemodule = $this->get_cm();
        $course = $this->get_course();
        $qattempt = new quiz_attempt($attempt, $quiz, $coursemodule, $course);
        $slots = $qattempt->get_slots();
        $quba = $qattempt->quba;
        $qdata = [];
        foreach ($slots as $slot) {
            $qdata[$slot] = new stdClass();
            $qdata[$slot]->correct = false;
            $qdata[$slot]->state = $qattempt->quba->get_question_state($slot);
            $qdata[$slot]->timecreated = $qattempt->get_question_attempt($slot)->get_last_step()->get_timecreated();
            $questionstate = $quba->get_question_state($slot);
            if ($questionstate == question_state::$gradedright || $questionstate == question_state::$mangrright) {
                $qdata[$slot]->correct = true;
                $qdata[$slot]->maxMark = $qattempt->quba->get_question_max_mark($slot);
            }
        }
        return $qdata;
    }

    /**
     * Overridden load_questions so that the default question engine function
     * load_questions_usage_by_activity can be called.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function load_questions() {
        global $DB;

        if (isset($this->quba)) {
            throw new coding_exception('This quiz attempt has already had the questions loaded.');
        }

        $this->quba = local_question_engine::load_questions_usage_by_activity($this->attempt->uniqueid);
        $this->slots = $DB->get_records('quiz_slots',
            ['quizid' => $this->get_quizid()], 'slot',
            'slot, id, requireprevious, questionid, includingsubcategories');
        $this->sections = array_values($DB->get_records('quiz_sections',
            ['quizid' => $this->get_quizid()], 'firstslot'));

        $this->link_sections_and_slots();
        $this->determine_layout();
        $this->number_questions();
    }
}

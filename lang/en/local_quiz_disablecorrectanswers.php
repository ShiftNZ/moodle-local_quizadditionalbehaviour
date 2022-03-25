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
 * English language strings for local_quiz_disablecorrectanswers.
 *
 * @package     local_quiz_disablecorrectanswers
 * @author      Donald Barrett <donaldb@skills.org.nz>
 * @copyright   2022 onwards, Skills Consulting Group Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// No direct access.
defined('MOODLE_INTERNAL') || die();

// Default langstring.
$string['pluginname'] = 'Disable correctly answered questions in a quiz attempt';
$string['additionalquestionbehaviour'] = 'Additional question behaviour';

$string['configdisablealreadycorrectquestions']             = 'Only allow completion of each question once.';
$string['disablealreadycorrectquestions']                   = 'Disable questions already correct';
$string['disablealreadycorrectquestions_help']              = 'Will stop user\'s from having to recomplete questions';

$string['configdisablealreadycorrectquestions_showcorrect'] = 'Show correct answer when only allow completion of each question once.';
$string['disablealreadycorrectquestions_showcorrect']       = 'Show correct answer with disabled correct';
$string['disablealreadycorrectquestions_showcorrect_help']  = 'Will show the users the correct question answer for disabled questions';

$string['configdisableshowcorrectforstudent']               = 'Prevents students from seeing specific question marks.';
$string['disableshowcorrectforstudent']                     = 'Disable "Whether correct" for students';
$string['disableshowcorrectforstudent_help']                = 'Prevents students from seeing specific question marks. For example the ticks for each choice in a multiple choice question';

$string['previouslycompleted']                              = "Previously Completed";
$string['alreadyansweredcorrectly']                         = "Already answered correctly";
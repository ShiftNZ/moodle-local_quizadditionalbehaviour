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
 * Question engine override for local_quizadditionalbehaviour.
 *
 * @package     local_quizadditionalbehaviour
 * @author      Donald Barrett <donaldb@skills.org.nz>
 * @copyright   2022 onwards, Skills Consulting Group Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_quizadditionalbehaviour\question;

use local_quizadditionalbehaviour\question\question_engine_data_mapper as local_question_engine_data_mapper;
use moodle_database;
use question_engine as core_question_engine;
use \question_usage_by_activity;
use coding_exception;
use dml_exception;

/**
 * Overridden question_engine class to force the use of an overridden question_engine_data_mapper.
 */
class question_engine extends core_question_engine {
    /**
     * Overridden to return the overridden quba.
     *
     * @param int $qubaid the id of the usage to load
     * @param moodle_database|null $db a database connection. Defaults to global $DB.
     * @return question_usage_by_activity
     * @throws dml_exception;
     * @throws coding_exception
     */
    public static function load_questions_usage_by_activity($qubaid, moodle_database $db = null) {
        $dm = new local_question_engine_data_mapper($db);
        return $dm->load_questions_usage_by_activity($qubaid);
    }
}

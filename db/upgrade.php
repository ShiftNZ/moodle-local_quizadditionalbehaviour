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
 * Upgrade script for local_quizadditionalbehaviour.
 *
 * @package     local_quizadditionalbehaviour
 * @author      Donald Barrett <donaldb@skills.org.nz>
 * @copyright   2022 onwards, Skills Consulting Group Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// No direct access.
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function.
 *
 * @param int $oldversion $plugin->version number.
 * @return void
 * @throws coding_exception
 * @throws dml_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_local_quizadditionalbehaviour_upgrade($oldversion) {
    global $DB;
    if ($oldversion < 2022032406) {
        // Remove the old "adv" settings.
        $advancedsettings = [
            'customgrading_adv',
            'disablecorrect_adv',
            'disablecorrectshowcorrect_adv',
            'disableshowcorrectforstudent_adv'
        ];
        list($sqlwhere, $sqlparams) = $DB->get_in_or_equal($advancedsettings);
        $sqlparams[] = 'quiz';
        $DB->delete_records_select('config_plugins', "name $sqlwhere AND plugin = ?", $sqlparams);
        upgrade_plugin_savepoint(true, 2022032406, 'local', 'quizadditionalbehaviour');
    }
}

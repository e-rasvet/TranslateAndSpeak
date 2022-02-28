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
 * Essay question type upgrade code.
 *
 * @package    qtype
 * @subpackage translateandspeak
 * @copyright  2018 Kochi-Tech.ac.jp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the translateandspeak question type.
 *
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_translateandspeak_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    // Automatically generated Moodle v3.5.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2021121300) {
        $table = new xmldb_table('qtype_tas_responses');
        
        $field = new xmldb_field('targetanswer', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021121300, 'qtype', 'translateandspeak');

    }

    if ($oldversion < 2021121400) {
        $table = new xmldb_table('qtype_tas_responses');

        $field = new xmldb_field('targetanswerjp', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021121400, 'qtype', 'translateandspeak');

    }

    if ($oldversion < 2022012000) {
        $table = new xmldb_table('qtype_tas_responses');

        $field = new xmldb_field('cmid', XMLDB_TYPE_INTEGER, '10', null, false, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, false, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022012000, 'qtype', 'translateandspeak');

    }
    
    return true;
}

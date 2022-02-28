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
 * translateandspeak question settings.
 *
 * @package    qtype
 * @subpackage translateandspeak
 * @copyright  2018 Kochi-Tech.ac.jp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** @var Admin $ADMIN */

if ($ADMIN->fulltree) {

    $playerAudio = array("fullPlayer" => new lang_string('fullplayer', 'qtype_translateandspeak'),
            "shortPlayer" => new lang_string('shortPlayer', 'qtype_translateandspeak'));

    $settings->add(new admin_setting_configselect('qtype_translateandspeak/audioplayer',
            new lang_string('audioplayer', 'qtype_translateandspeak'),
            new lang_string('audioplayerdescription', 'qtype_translateandspeak'), 'fullplayer', $playerAudio));


}

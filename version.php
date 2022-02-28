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
 * translateandspeak question type version information.
 *
 * @package    qtype
 * @subpackage translateandspeak
 * @copyright  2021 Paul Daniels, Igor Nikulin

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** @var Plugin $plugin */
$plugin->component = 'qtype_translateandspeak';
$plugin->version   = 2022012000;

$plugin->requires  = 2018062504;

$plugin->maturity  = MATURITY_ALPHA;

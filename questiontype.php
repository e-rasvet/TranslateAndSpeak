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
 * Question type class for the translateandspeak question type.
 *
 * @package    qtype
 * @subpackage translateandspeak
 * @copyright  2021 Paul Daniels, Igor Nikulin

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/translateandspeak/question.php');


/**
 * The translateandspeak question type.
 *
 * @copyright  2021 Paul Daniels, Igor Nikulin

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_translateandspeak extends question_type {

    /**
     * data used by export_to_xml (among other things possibly
     *
     * @return array
     */
    public function extra_question_fields() {
        return array('qtype_tas_options', 'sample_answer', 'showtranscriptionfl', 'showpercentscorefl', 'saveresponseasaudiofl', 'showanalysisfl', 'showspeakingfl');
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        global $DB;
        $fs = get_file_storage();

        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_translateandspeak', 'normalaudio', $questionid);
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_translateandspeak', 'slowaudio', $questionid);

        $this->move_files_in_combined_feedback($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        global $DB;
        $fs = get_file_storage();

        parent::delete_files($questionid, $contextid);

        $fs->delete_area_files($contextid, 'qtype_translateandspeak', 'normalaudio', $questionid);
        $fs->delete_area_files($contextid, 'qtype_translateandspeak', 'slowaudio', $questionid);

        $this->delete_files_in_combined_feedback($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    public function save_question_options($question) {
        global $DB;

        $context = $question->context;
        $result = new stdClass();

        $this->save_question_answers($question);

        {
            $options = $DB->get_record('qtype_tas_options', array('questionid' => $question->id));
            if (!$options) {
                $options = new stdClass();
                $options->questionid = $question->id;
                $options->sample_answer = '';
                $options->showtranscriptionfl = 0;
                $options->showpercentscorefl = 0;
                $options->saveresponseasaudiofl = 0;
                $options->showanalysisfl = 0;
                $options->showspeakingfl = 0;
                $options->id = $DB->insert_record('qtype_tas_options', $options);
            }

            $options->sample_answer = $question->sample_answer;
            $options->showtranscriptionfl = $question->showtranscriptionfl;
            $options->showpercentscorefl = $question->showpercentscorefl;
            $options->saveresponseasaudiofl = $question->saveresponseasaudiofl;
            $options->showanalysisfl = $question->showanalysisfl;
            $options->showspeakingfl = $question->showspeakingfl;

            $options = $this->save_combined_feedback_helper($options, $question, $context, true);
            $DB->update_record('qtype_tas_options', $options);
        }

    }

    protected function is_answer_empty($questiondata, $key) {
        return html_is_blank($questiondata->answer[$key]['text']) || trim($questiondata->answer[$key]) == '';
    }

    protected function fill_answer_fields($answer, $questiondata, $key, $context) {
        // $answer->answer = $this->import_or_save_files($questiondata->answer[$key],
        //         $context, 'question', 'answer', $answer->id);
        // $answer->answerformat = $questiondata->answer[$key]['format'];
        $answer->answer = trim($questiondata->answer[$key]);
        return $answer;
    }

    public function delete_question($questionid, $contextid) {
        global $DB;

        $DB->delete_records('qtype_tas_options', array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_combined_feedback($question, $questiondata, true);
        $question->questions = $questiondata->options->answers;
    }

    public function get_random_guess_score($questiondata) {

        return 0;
    }

    public function get_possible_responses($questiondata) {

        return array();
    }


    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $fs = get_file_storage();
        $contextid = $question->contextid;

        $output = parent::export_to_xml($question, $format);
        foreach ($question->options->itemsettings as $set) {
            $output .= "      <translateandspeaksetting>\n";
            $output .= '        <sample_answer>' . $set->sample_answer . "</sample_answer>\n";
            $output .= '        <showtranscriptionfl>' . $set->showtranscriptionfl . "</showtranscriptionfl>\n";
            $output .= '        <showpercentscorefl>' . $set->showpercentscorefl . "</showpercentscorefl>\n";
            $output .= '        <saveresponseasaudiofl>' . $set->saveresponseasaudiofl . "</saveresponseasaudiofl>\n";
            $output .= '        <showanalysisfl>' . $set->showanalysisfl . "</showanalysisfl>\n";
            $output .= '        <showspeakingfl>' . $set->showspeakingfl . "</showspeakingfl>\n";
            $output .= "     </translateandspeaksetting>\n";
        }

        if ($question->options->shuffleanswers) {
            $output .= "    <shuffleanswers/>\n";
        }
        $output .= $format->write_combined_feedback($question->options,
                $question->id,
                $question->contextid);
        $files = $fs->get_area_files($contextid, 'qtype_translateandspeak', 'normalaudio', $question->id);
        $output .= "    ".$this->write_files($files, 2)."\n";
        $files = $fs->get_area_files($contextid, 'qtype_translateandspeak', 'slowaudio', $question->id);
        $output .= "    ".$this->write_files($files, 2)."\n";

        return $output;
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {
        if (!isset($data['@']['type']) || $data['@']['type'] != 'translateandspeak') {
            return false;
        }

        $question = $format->import_headers($data);
        $question->qtype = 'translateandspeak';

        $question->shuffleanswers = array_key_exists('shuffleanswers',
                $format->getpath($data, array('#'), array()));

        $filexml = $format->getpath($data, array('#', 'file'), array());
        $question->normalaudio = $format->import_files_as_draft($filexml);
        $question->slowaudio = $format->import_files_as_draft($filexml);

        $question->isimport = true;
        $question->itemsettings = [];
        if (isset($data['#']['translateandspeaksetting'])) {
            foreach ($data['#']['translateandspeaksetting'] as $key => $setxml) {
                $question->itemsettings[$key]['sample_answer'] = $format->getpath($setxml, array('#', 'sample_answer', 0, '#'), 0);
                $question->itemsettings[$key]['showtranscriptionfl'] = $format->getpath($setxml, array('#', 'showtranscriptionfl', 0, '#'), 0);
                $question->itemsettings[$key]['showpercentscorefl'] = $format->getpath($setxml, array('#', 'showpercentscorefl', 0, '#'), 0);
                $question->itemsettings[$key]['saveresponseasaudiofl'] = $format->getpath($setxml, array('#', 'saveresponseasaudiofl', 0, '#'), 0);
                $question->itemsettings[$key]['showanalysisfl'] = $format->getpath($setxml, array('#', 'showanalysisfl', 0, '#'), 0);
                $question->itemsettings[$key]['showspeakingfl'] = $format->getpath($setxml, array('#', 'showspeakingfl', 0, '#'), 0);
            }
        }

        $format->import_combined_feedback($question, $data, true);
        $format->import_hints($question, $data, true, false,
                $format->get_format($question->questiontextformat));

        return $question;
    }


}

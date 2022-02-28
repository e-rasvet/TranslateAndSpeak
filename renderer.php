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
 * translateandspeak question renderer class.
 *
 * @package    qtype
 * @subpackage translateandspeak
 * @copyright  2021 Paul Daniels, Igor Nikulin

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


require_once(dirname(__FILE__) . '/lib.php');

/**
 * Generates the output for translateandspeak questions.
 *
 * @copyright  2021 Paul Daniels, Igor Nikulin

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class qtype_translateandspeak_renderer extends qtype_renderer {
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        global $USER;

        $result = "";

        $question = $qa->get_question();
        $config = get_config('qtype_translateandspeak');

        $questiontext = $question->format_questiontext($qa);
        $placeholder = false;

        if (preg_match('/_____+/', $questiontext, $matches)) {
            $placeholder = $matches[0];
        }
        $input = '**subq controls go in here**';

        if ($placeholder) {
            $questiontext = substr_replace($questiontext, $input,
                    strpos($questiontext, $placeholder), strlen($placeholder));
        }

        $result .= html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        if (!$options->readonly) {
            $result .= html_writer::start_tag('div');
            $result .= html_writer::start_tag('div');
            $result .= html_writer::start_tag('div');
            $result .= html_writer::tag('p', get_string('japanesetext', 'qtype_translateandspeak'));
            $result .= html_writer::tag('a', get_string('startrecording', 'qtype_translateandspeak'), array("href" => "#", "onclick" => "return false", "id" => 'translateandspeak-listening-btnJp-' . $question->id, "class" => "translateandspeak_button_green"));
            $result .= html_writer::end_tag('div');
            $result .= html_writer::tag('div', "", array('id' => 'translateandspeak-listening-text-jp-' . $question->id));
            $result .= html_writer::end_tag('div');
        }


        /*
         * Input Answer box settings
         */
        $answerID = $qa->get_qt_field_name('answer');
        $answerCurrent = $qa->get_last_qt_var('answer');
        $inputattributes = array(
                'name' => $answerID,
                'value' => $answerCurrent,
                'id' => $answerID,
                'class' => 'form-control',
                'autocapitalize' => 'none',
                'spellcheck'     => 'false',
                'style' => 'font-size: medium;',
                'rows' => '2',
                'cols' => 60,
        );

        /*
         * Input TargetAnswer box settings
         */
        $targetAnswerID = $qa->get_qt_field_name('targetanswer');
        $targetAnswerCurrent = $qa->get_last_qt_var('targetanswer');
        $inputattributesTargetAnswer = array(
                'name' => $targetAnswerID,
                'value' => $targetAnswerCurrent,
                'id' => $targetAnswerID,
                'class' => 'form-control',
                'autocapitalize' => 'none',
                'spellcheck'     => 'false',
                'style' => 'font-size: medium;',
                'rows' => '2',
                'cols' => 60,
        );

        /*
         * Input TargetAnswerJapan box settings
         */
        $targetAnswerJapanID = $qa->get_qt_field_name('targetanswerjp');
        $targetAnswerJapanCurrent = $qa->get_last_qt_var('targetanswerjp');
        $inputattributesTargetAnswerJapan = array(
                'name' => $targetAnswerJapanID,
                'value' => $targetAnswerJapanCurrent,
                'id' => $targetAnswerJapanID,
                'class' => 'form-control',
                'autocapitalize' => 'none',
                'spellcheck'     => 'false',
                'style' => 'font-size: medium;',
                'rows' => '2',
                'cols' => 60,
        );

        if (!$options->readonly) {
            /* TargetAnswerJapan Layout */
            $result .= html_writer::start_tag('div', array('id' => 'translateandspeak_box_jp_' . $question->id));
            $result .= html_writer::start_tag('div', array('class' => 'ablock form-inline'));
            $result .= html_writer::tag('label', get_string('TargetAnswerJapan', 'qtype_translateandspeak'),
                    array('for' => $inputattributesTargetAnswerJapan['id']));
            $result .= html_writer::end_tag('div');
            $result .= html_writer::tag('div', html_writer::tag('textarea', '', $inputattributesTargetAnswerJapan),
                    array('class' => 'answer'));
            $result .= html_writer::end_tag('div');

            /* TargetAnswer Layout */
            $result .= html_writer::start_tag('div', array('id' => 'translateandspeak_box_en_' . $question->id, 'style' => 'display: none'));
            $result .= html_writer::start_tag('div', array('class' => 'ablock form-inline'));
            $result .= html_writer::tag('label', get_string('TargetAnswer', 'qtype_translateandspeak'),
                    array('for' => $inputattributesTargetAnswer['id']));
            $result .= html_writer::tag('label', qtype_translateandspeak_loading_animation(35), array("id" => 'translateandspeak-translation-animation-' . $question->id, 'style' => 'display: none'));
            $result .= html_writer::tag('label', get_string('translationpleasewait', 'qtype_translateandspeak'), array("id" => 'translateandspeak-translation-inprocess-' . $question->id, 'style' => 'display: none'));

            $result .= html_writer::end_tag('div');
            $result .= html_writer::tag('div', html_writer::tag('textarea', '', $inputattributesTargetAnswer),
                    array('class' => 'answer'));
            $result .= html_writer::end_tag('div');

            /* English recording btn */

            $result .= html_writer::start_tag('div', array("style" => "display: none", "id" => 'translateandspeak-translation-entext-' . $question->id));
            $result .= html_writer::tag('p', get_string('englishtext', 'qtype_translateandspeak'));
            $result .= html_writer::tag('a', get_string('startrecording', 'qtype_translateandspeak'), array("href" => "#", "onclick" => "return false", "id" => 'translateandspeak-listening-btnEn-' . $question->id, "class" => "translateandspeak_button_green"));
            $result .= html_writer::end_tag('div');
            $result .= html_writer::tag('div', "", array('id' => 'translateandspeak-listening-text-en-' . $question->id));

            /* Answer Layout */
            $result .= html_writer::start_tag('div', array('id' => 'translateandspeak_box_answer_' . $question->id, 'style' => 'display: none'));
            $result .= html_writer::start_tag('div', array('class' => 'ablock form-inline'));
            $result .= html_writer::tag('label', get_string('Answer', 'qtype_translateandspeak'),
                    array('for' => $inputattributes['id']));
            $result .= html_writer::end_tag('div');
            $result .= html_writer::tag('div', html_writer::tag('textarea', '', $inputattributes),
                    array('class' => 'answer'));
            $result .= html_writer::end_tag('div');

        } else {
            $answer = $qa->get_last_qt_var("answer");
            $result .= html_writer::tag('label', get_string('myanswer', 'qtype_translateandspeak') . ": " . $answer);
        }

        /*
         * HTML grading box
         */
        $boxname = $qa->get_qt_field_name('boxname');
        $gradename = $qa->get_qt_field_name('grade');
        {
            $label = 'grade';
            $currentanswer = $qa->get_last_qt_var($label);
            $inputattributes = array(
                    'name' => $gradename,
                    'value' => $currentanswer,
                    'id' => $gradename,
                    'size' => 10,
                    'class' => 'form-control d-inline',
                    'readonly' => 'readonly',
                    'style' => 'border: 0px; background-color: transparent;',
            );

            $input = html_writer::empty_tag('input', $inputattributes);

            if (!$options->readonly) {
                $result .= html_writer::start_tag('div', array('class' => 'ablock form-inline', 'style'=>'display:none;', 'id' => $boxname));

                $result .= html_writer::start_tag('div', array('class' => 'ablock form-inline'));

                $result .= html_writer::tag('label', get_string('score', 'qtype_translateandspeak',
                        html_writer::tag('span', $input, array('class' => 'answer'))),
                        array('for' => $inputattributes['id']));
                $result .= html_writer::end_tag('div');

                $result .= html_writer::end_tag('div');
            }

        }

        if (!$options->readonly) {
            $this->page->requires->js_call_amd('qtype_translateandspeak/script', 'init',
                    [$qa->get_outer_question_div_unique_id(), $options->readonly, $question->id, $qa->get_usage_id(),
                            $qa->get_slot(), $gradename, $answerID, $targetAnswerID, $targetAnswerJapanID, $boxname, $this->page->cm->id, $USER->id]);
        }

        return $result;
    }


    /**
     * @param question_attempt $qa
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function specific_feedback(question_attempt $qa) {
        global $DB, $USER;

        $question = $qa->get_question();
        $answer = $qa->get_last_qt_var('answer');
        $targetAnswer = $qa->get_last_qt_var('targetanswer');
        $targetAnswerJP = $qa->get_last_qt_var('targetanswerjp');
        $grade = $qa->get_last_qt_var('grade');

        //$grade = [];
        //$grade['gradePercent'] = $qa->get_last_qt_var('grade');

        $result = '';
        $result .= html_writer::start_tag('div', array('class' => 'ablock'));

        /*
         * Feed Back report
         *
         */

        $state = $qa->get_state();

        if (!$state->is_finished()) {
            $response = $qa->get_last_qt_data();
            if (!$qa->get_question()->is_gradable_response($response)) {
                return '';
            }
            list($notused, $state) = $qa->get_question()->grade_response($response);
        }


        /*
         * Print Previouse answers
         */

        $result .= $this->correct_response($qa);

        $answers = qtype_translateandspeak_printAnswersTable($qa);

        $table = new html_table();
        $table->head = array('Date', 'Answer', 'Rate');
        $table->data = array();

        $answer = ""; //Clear last answer

        foreach ($answers as $k => $v) {
            $table->data[] = array($v->time, $v->answer, $v->rate);
            $answer .= $v->answer . " ";
        }

        $result .= html_writer::table($table);


        /*
         * Analised form
         */
        $anl = qtype_translateandspeak_printanalizeform($answer);
        unset($anl['laters']);

        $table = new html_table();
        $table->head = array('Analysis', 'Result');
        $table->data = array();

        foreach ($anl as $k => $v) {
            $table->data[] = array(get_string($k, 'qtype_translateandspeak'), $v);
        }

        $result .= html_writer::table($table);


        return $result;
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();

        return get_string('correctansweris', 'qtype_translateandspeak',
                s($question->clean_response(qtype_translateandspeak_get_correct_response($qa))));
    }


    protected static function get_url_for_audio(question_attempt $qa, $filearea, $itemid = 0) {
        $question = $qa->get_question();
        $qubaid = $qa->get_usage_id();
        $slot = $qa->get_slot();
        $fs = get_file_storage();
        if ($filearea == 'normalaudio' || $filearea == 'slowaudio') {
            $itemid = $question->id;
        }
        $componentname = $question->qtype->plugin_name();
        $draftfiles = $fs->get_area_files($question->contextid, $componentname,
                $filearea, $itemid, 'id');
        if ($draftfiles) {
            foreach ($draftfiles as $file) {
                if ($file->is_directory()) {
                    continue;
                }
                $url = moodle_url::make_pluginfile_url($question->contextid, $componentname,
                        $filearea, "$qubaid/$slot/{$itemid}", '/',
                        $file->get_filename());
                return $url->out();
            }
        }
        return null;
    }
}

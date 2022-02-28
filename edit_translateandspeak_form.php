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
 * Defines the editing form for the translateandspeak question type.
 *
 * @package    qtype
 * @subpackage translateandspeak
 * @copyright  2021 Paul Daniels, Igor Nikulin

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * translateandspeak question editing form definition.
 *
 * @copyright  2021 Paul Daniels, Igor Nikulin

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_translateandspeak_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        $mform->getElement('questiontext')->setValue(array('text'=>get_string('defaultquestiontext','qtype_'.$this->qtype())));


        $mform->addElement('checkbox', 'showtranscriptionfl', get_string('showtranscriptionfl','qtype_'.$this->qtype()), get_string('showtranscriptionflLabel','qtype_'.$this->qtype()));
        $mform->setDefault('showtranscriptionfl', 0);

        $mform->addElement('checkbox', 'showpercentscorefl', get_string('showshowpercentscorefl','qtype_'.$this->qtype()), '&nbsp;');
        $mform->setDefault('showpercentscorefl', 0);

        $mform->addElement('checkbox', 'saveresponseasaudiofl', get_string('showsaveresponseasaudiofl','qtype_'.$this->qtype()), '&nbsp;');
        $mform->setDefault('saveresponseasaudiofl', 0);

        $mform->addElement('checkbox', 'showanalysisfl', get_string('showanalysisfl','qtype_'.$this->qtype()), '&nbsp;');
        $mform->setDefault('showanalysisfl', 0);

        $mform->addElement('checkbox', 'showspeakingfl', get_string('showspeakingfl','qtype_'.$this->qtype()), '&nbsp;');
        $mform->setDefault('showspeakingfl', 0);

    }

    /**
     * Options shared by all file pickers in the form.
     *
     * @return array Array of filepicker options.
     */
    public static function file_picker_options() {
        $filepickeroptions = array();
        $filepickeroptions['accepted_types'] = array('audio');
        $filepickeroptions['maxbytes'] = 0;
        $filepickeroptions['maxfiles'] = 1;
        $filepickeroptions['subdirs'] = 0;
        return $filepickeroptions;
    }


    protected function get_per_answer_fields($mform, $label, $gradeoptions,
            &$repeatedoptions, &$answersoption) {
        $repeated = array();
        $repeated[] = $mform->createElement('textarea', 'answer', get_string('comment', 'qtype_translateandspeak') . ' ' . $label,
                array('rows' => 3, 'cols' => 65), $this->editoroptions);
        //$repeated[] = $mform->createElement('textarea', 'feedback', get_string('feedback'), array('rows' => 1, 'cols' => 65), $this->editoroptions);
        // $repeatedoptions['answer']['type'] = PARAM_RAW;
        $answersoption = 'answers';

        return $repeated;
    }


    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        $question = $this->data_preprocessing_combined_feedback($question, true);
        $question = $this->data_preprocessing_hints($question, true, true);
        $draftitemid = file_get_submitted_draft_itemid('normalaudio');

        file_prepare_draft_area($draftitemid, $this->context->id, 'qtype_translateandspeak',
                'normalaudio', !empty($question->id) ? (int) $question->id : null,
                self::file_picker_options());
        $question->normalaudio = $draftitemid;

        $draftitemid = file_get_submitted_draft_itemid('slowaudio');

        file_prepare_draft_area($draftitemid, $this->context->id, 'qtype_translateandspeak',
                'slowaudio', !empty($question->id) ? (int) $question->id : null,
                self::file_picker_options());
        $question->slowaudio = $draftitemid;

        //$question->sample_answer = $question->options->sample_answer;
        $question->showtranscriptionfl = (isset($question->options->showtranscriptionfl)) ? $question->options->showtranscriptionfl : 0;
        $question->showpercentscorefl = (isset($question->options->showpercentscorefl)) ? $question->options->showpercentscorefl : 0;
        $question->saveresponseasaudiofl = (isset($question->options->saveresponseasaudiofl)) ? $question->options->saveresponseasaudiofl : 0;
        $question->showanalysisfl = (isset($question->options->showanalysisfl)) ? $question->options->showanalysisfl : 0;
        $question->showspeakingfl = (isset($question->options->showspeakingfl)) ? $question->options->showspeakingfl : 0;

        $this->data_preprocessing_answers($question, true);

        return $question;
    }

    public function qtype() {
        return 'translateandspeak';
    }



    protected function data_preprocessing_answers($question, $withanswerfiles = false) {

        if (empty($question->options->answers)) {
            return $question;
        }

        $key = 0;
        foreach ($question->options->answers as $answer) {
            $question->answer[$key] = $answer->answer;

            // $draftitemid = file_get_submitted_draft_itemid('answer['.$key.']');
            // $question->answer[$key]['text'] = file_prepare_draft_area(
            //   $draftitemid,
            //   $this->context->id,
            //   'question',
            //   'answer',
            //   !empty($answer->id) ? (int) $answer->id : null,
            //   $this->fileoptions,
            //   $answer->answer
            // );
            // $question->answer[$key]['itemid'] = $draftitemid;
            // $question->answer[$key]['format'] = $answer->answerformat;

            unset($this->_form->_defaultValues["fraction[{$key}]"]);
            $key++;
        }

    }


}

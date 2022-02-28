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
 * translateandspeak question definition class.
 *
 * @package    qtype
 * @subpackage translateandspeak
 * @copyright  2021 Paul Daniels, Igor Nikulin

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Represents a translateandspeak question.
 *
 * @copyright  2021 Paul Daniels, Igor Nikulin

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_translateandspeak_question extends question_graded_automatically_with_countback {

    public $answers = array();

    public function get_expected_data() {
        return array(
                'answer' => PARAM_RAW_TRIMMED,
                'targetanswer' => PARAM_RAW_TRIMMED,
                'targetanswerjp' => PARAM_RAW_TRIMMED,
                'grade' => PARAM_RAW_TRIMMED,);
    }

    public function summarise_response(array $response) {
        if (isset($response['answer'])) {
            return $response['answer'];
        } else {
            return null;
        }
    }

    public function un_summarise_response(string $summary) {
        if (!empty($summary)) {
            return ['answer' => $summary];
        } else {
            return [];
        }
    }

    public function is_complete_response(array $response) {
        return array_key_exists('answer', $response) &&
                ($response['answer'] || $response['answer'] === '0');
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseenterananswer', 'qtype_translateandspeak');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {

        return question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'answer');
    }


    public function get_answers() {
        return $this->answers;
    }

    public function compare_response_with_answer(array $response, question_answer $answer) {
        if (!array_key_exists('answer', $response) || is_null($response['answer'])) {
            return false;
        }

        return self::compare_string_with_wildcard(
                $response['answer'], $answer->answer, !$this->usecase);
    }

    public static function compare_string_with_wildcard($string, $pattern, $ignorecase) {

        // Normalise any non-canonical UTF-8 characters before we start.
        $pattern = self::safe_normalize($pattern);
        $string = self::safe_normalize($string);

        // Break the string on non-escaped runs of asterisks.
        // ** is equivalent to *, but people were doing that, and with many *s it breaks preg.
        $bits = preg_split('/(?<!\\\\)\*+/', $pattern);

        // Escape regexp special characters in the bits.
        $escapedbits = array();
        foreach ($bits as $bit) {
            $escapedbits[] = preg_quote(str_replace('\*', '*', $bit), '|');
        }
        // Put it back together to make the regexp.
        $regexp = '|^' . implode('.*', $escapedbits) . '$|u';

        // Make the match insensitive if requested to.
        if ($ignorecase) {
            $regexp .= 'i';
        }

        return preg_match($regexp, trim($string));
    }

    /**
     * Normalise a UTf-8 string to FORM_C, avoiding the pitfalls in PHP's
     * normalizer_normalize function.
     * @param string $string the input string.
     * @return string the normalised string.
     */
    protected static function safe_normalize($string) {
        if ($string === '') {
            return '';
        }

        if (!function_exists('normalizer_normalize')) {
            return $string;
        }

        $normalised = normalizer_normalize($string, Normalizer::FORM_C);
        if (is_null($normalised)) {
            // An error occurred in normalizer_normalize, but we have no idea what.
            debugging('Failed to normalise string: ' . $string, DEBUG_DEVELOPER);
            return $string; // Return the original string, since it is the best we have.
        }

        return $normalised;
    }

    public function get_correct_response() {
        return array();
    }

    public function clean_response($answer) {
        // Break the string on non-escaped asterisks.
        $bits = preg_split('/(?<!\\\\)\*/', $answer);

        // Unescape *s in the bits.
        $cleanbits = array();
        foreach ($bits as $bit) {
            $cleanbits[] = str_replace('\*', '*', $bit);
        }

        // Put it back together with spaces to look nice.
        return trim(implode(' ', $cleanbits));
    }

    public function check_file_access($qa, $options, $component, $filearea,
            $args, $forcedownload) {
        if ($filearea == 'normalaudio' || $filearea == 'slowaudio') {
            $validfilearea = true;
        } else {
            $validfilearea = false;
        }
        if ($component == 'qtype_translateandspeak' && $validfilearea) {
            $question = $qa->get_question();
            $itemid = reset($args);
            if ($filearea == 'normalaudio' || $filearea == 'slowaudio') {
                return $itemid == $question->id;
            } else {
                return false;
            }
        } else {
            return parent::check_file_access($qa, $options, $component,
                    $filearea, $args, $forcedownload);
        }

    }

    public function grade_response(array $response) {
        $fraction = 0.01 * (float) $response['grade'];
        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

    public function compute_final_grade($responses, $totaltries) {
        return 0;
    }

    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        $question = $qa->get_question();
        return question_engine::make_archetypal_behaviour($preferredbehaviour, $qa);
        /*
         *         $question = $qa->get_question();
        if (empty(count($question->questions)) && $question->auto_score == "target_teacher") {
            return question_engine::make_behaviour('manualgraded', $qa, $preferredbehaviour);
        } else {
            return question_engine::make_archetypal_behaviour($preferredbehaviour, $qa);
        }
         */
    }
}

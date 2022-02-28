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
 * Serve question type files
 *
 * @since      2.0
 * @package    qtype_translateandspeak
 * @copyright  2021 Paul Daniels, Igor Nikulin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Checks file access for translateandspeak questions.
 *
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 * @package  qtype_translateandspeak
 * @category files
 */
function qtype_translateandspeak_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    question_pluginfile($course, $context, 'qtype_translateandspeak', $filearea, $args, $forcedownload, $options);
}

/**
 * @param $ans
 * @param $qa
 * @param null $targetAnswer
 * @return array
 * @throws dml_exception
 */
function qtype_translateandspeak_compare_answer($answer, $targetAnswer, $qa) {
    global $DB, $CFG;

    $question = $qa->get_question();
    $qid = $question->id;

    $text1 = strtolower(preg_replace("/[^a-zA-Z0-9\\s]/", "", $targetAnswer));
    $text2 = strtolower(preg_replace("/[^a-zA-Z0-9\\s]/", "", $answer));

    $text1 = trim(strtolower(preg_replace('/\s+/', ' ', $text1)));
    $text2 = trim(strtolower(preg_replace('/\s+/', ' ', $text2)));

    $percent = qtype_translateandspeak_similar_text($text1, $text2);

    if ($percent > 95) {
        $percent = 100;
    }

    //$answStat = qtype_translateandspeak_printanalizeform($answer);

    $result = array(
            "gradePercent" => round($percent),
            "grade" => round($percent / 100, 2),
            "text1" => $text1,
            "text2" => $text2,
    );

    return $result;
}

/**
 * @param $text1
 * @param $text2
 * @param string $lang
 * @return float|mixed
 */
function qtype_translateandspeak_similar_text($text1, $text2, $lang = "en") {

    $text1 = strip_tags($text1);
    $text2 = strip_tags($text2);

    $text1 = preg_replace('/[^a-zA-Z\s]+/', '', $text1);
    $text1 = preg_replace('!\s+!', ' ', $text1);

    $text2 = preg_replace('/[^a-zA-Z\s]+/', '', $text2);
    $text2 = preg_replace('!\s+!', ' ', $text2);

    $text1 = strtolower($text1);
    $text2 = strtolower($text2);

    //if (strstr($lang, "en")) {
    //    $res = qtype_translateandspeak_cmp_phon($text1, $text2);
    //    $percent = $res['percent'];
    //} else {
    //$sim = similar_text($text1, $text2, $percent);
    $percent = qtype_translateandspeak_levenshtein_percent($text1, $text2);
    $percent = round($percent);
    //}

    return $percent;
}

/**
 * @param $text1
 * @param $text2
 * @param string $lang
 * @return float|mixed
 */
function qtype_translateandspeak_similar_text_original($text1, $text2, $lang = "en") {

    $text1 = strip_tags($text1);
    $text2 = strip_tags($text2);

    $text1 = preg_replace('!\s+!', ' ', $text1);

    $text2 = preg_replace('!\s+!', ' ', $text2);

    $text1 = strtolower($text1);
    $text2 = strtolower($text2);

    //if ($lang == "en") {
    //    $res = qtype_translateandspeak_cmp_phon($text1, $text2);
    //    $percent = $res['percent'];
    //} else {
    //$sim = similar_text($text1, $text2, $percent);
    $percent = qtype_translateandspeak_levenshtein_percent($text1, $text2);
    $percent = round($percent);
    //}

    return $percent;
}

/**
 * @param $spoken
 * @param $target
 * @return array
 */
function qtype_translateandspeak_cmp_phon($spoken, $target) {
    global $CFG;

    if (!isset($CFG->pron_dict_loaded)) {
        $lines = explode("\n", file_get_contents($CFG->dirroot . '/question/type/translateandspeak/pron-dict.txt'));

        $pron_dict = array();

        foreach ($lines as $line) {
            $elements = explode(",", $line);
            $pron_dict[$elements[0]] = $elements[1];
        }

        $CFG->pron_dict_loaded = $pron_dict;
    } else {
        $pron_dict = $CFG->pron_dict_loaded;
    }

    if (isset($lines)) {
        foreach ($lines as $line) {
            $elements = explode(",", $line);
            $pron_dict[$elements[0]] = $elements[1];
        }
    }

    // Set up two objects, spoken and target

    $spoken_obj = new stdClass;
    $target_obj = new stdClass;

    $spoken_obj->raw = $spoken;
    $spoken_obj->clean = strtolower(preg_replace("/[^a-zA-Z0-9' ]/", "", $spoken_obj->raw));
    $spoken_obj->words = array_filter(explode(" ", $spoken_obj->clean));
    $spoken_obj->phonetic = array();

    // Convert each spoken word to phonetic script

    foreach ($spoken_obj->words as $word) {
        if (array_key_exists(strtoupper($word), $pron_dict)) {
            $spoken_obj->phonetic[] = $pron_dict[strtoupper($word)];
        } else {
            $spoken_obj->phonetic[] = $word;
        }
    }

    $target_obj->raw = $target;
    $target_obj->clean = strtolower(preg_replace("/[^a-zA-Z0-9' ]/", "", $target_obj->raw));
    $target_obj->words = array_filter(explode(" ", $target_obj->clean));
    $target_obj->phonetic = array();

    // Convert each target word to phonetic script

    foreach ($target_obj->words as $word) {
        if (array_key_exists(strtoupper($word), $pron_dict)) {
            $target_obj->phonetic[] = $pron_dict[strtoupper($word)];
        } else {
            $target_obj->phonetic[] = $word;
        }
    }

    // Check for matches

    $matched = array();
    $unmatched = array();
    $score = 0;

    foreach ($target_obj->phonetic as $index => $word) {
        if (in_array($word, $spoken_obj->phonetic)) {
            $score++;
            if (!in_array($target_obj->words[$index], $matched)) {
                $matched[] = $target_obj->words[$index];
            }
        } else if (!in_array($word, $spoken_obj->phonetic)) {
            if (!in_array($target_obj->words[$index], $unmatched)) {
                $unmatched[] = $target_obj->words[$index];
            }
        }
    }

    /*
     * New unmached calculation system
     */
    foreach ($spoken_obj->phonetic as $index => $word) {
        if (!in_array($word, $target_obj->phonetic)) {
            if (!in_array($spoken_obj->words[$index], $unmatched)) {
                $unmatched[] = $spoken_obj->words[$index];
            }
        }
    }
    //$percent=round($score/count($spoken_obj->words)*100);  //Old
    $percent = round(count($matched) / (count($matched) + count($unmatched)) * 100);  //New

    if (count($spoken_obj->phonetic) == 0) {
        $percent = 100;
    }

    return array("spoken" => $spoken_obj, "target" => $target_obj, "matched" => $matched, "unmatched" => $unmatched,
            "percent" => $percent);
}

/**
 * @param $text
 * @return array
 */
function qtype_translateandspeak_printanalizeform($text) {
    $data = array();

    $text = strip_tags($text);

    if (empty($text)) {
        return array(
                "wordcount" => 0,
                "worduniquecount" => 0,
                "numberofsentences" => 0,
                "averagepersentence" => 0,
                "hardwords" => 0,
                "hardwordspersent" => 0,
                "lexicaldensity" => 0,
                "fogindex" => 0,
                "laters" => 0
        );
    }

    $data['wordcount'] = qtype_translateandspeak_wordcount($text);
    $data['worduniquecount'] = qtype_translateandspeak_worduniquecount($text);
    $data['numberofsentences'] = qtype_translateandspeak_numberofsentences($text);
    if ($data['numberofsentences'] == 0 || empty($data['numberofsentences'])) {
        $data['numberofsentences'] = 1;
    }
    $data['averagepersentence'] = qtype_translateandspeak_averagepersentence($text, $data['wordcount'], $data['numberofsentences']);
    list ($data['hardwords'], $data['hardwordspersent']) = qtype_translateandspeak_hardwords($text, $data['wordcount']);
    $data['lexicaldensity'] = qtype_translateandspeak_lexicaldensity($text, $data['wordcount'], $data['worduniquecount']);
    $data['fogindex'] = qtype_translateandspeak_fogindex($text, $data['averagepersentence'], $data['hardwordspersent']);
    $data['laters'] = qtype_translateandspeak_laters($text);

    return $data;
}

function qtype_translateandspeak_printAnswersTable(question_attempt $qa) {
    global $DB;

    $data = array();
    $hint = 0;

    $answer = $DB->get_record("qtype_tas_responses", array("usageid" => $qa->get_usage_id(), "slotid" => $qa->get_slot()));

    //if ($answers = $DB->get_records("qtype_tas_responses", array("usageid" => $qa->get_usage_id(), "slotid" => $qa->get_slot()),
    if ($answers = $DB->get_records("qtype_tas_responses", array("cmid" => $answer->cmid, "userid" => $answer->userid), "time ASC")) {
        foreach ($answers as $answer) {
            $analise = qtype_translateandspeak_compare_answer($answer->answer, $answer->targetanswer, $qa);

            $data[$answer->usageid] = new stdClass();

            $data[$answer->usageid]->time = date("m.d.Y", $answer->time);
            $data[$answer->usageid]->answer = $answer->answer;
            $data[$answer->usageid]->rate = $analise['gradePercent'] . "%";
        }

    }

    return $data;

}

function qtype_translateandspeak_get_correct_response(question_attempt $qa) {
    global $DB;

    //$question = $qa->get_question();
    //$answer = $DB->get_record('qtype_tas_options', array('questionid' => $question->id));

    return $qa->get_last_qt_var('targetanswer');
}

/**
 * @param $text
 * @return mixed
 */
function qtype_translateandspeak_wordcount($text) {
    return str_word_count($text);
}

/**
 * @param $text
 * @return int
 */
function qtype_translateandspeak_worduniquecount($text) {
    $words = str_word_count($text, 1);
    $words_ = array();

    foreach ($words as $word) {
        if (!in_array($word, $words_)) {
            $words_[] = strtolower($word);
        }
    }
    return count($words_);
}

/**
 * @param $text
 * @return int
 */
function qtype_translateandspeak_numberofsentences($text) {
    $text = strip_tags($text);
    $noneed = array("\r", "\n", ".0", ".1", ".2", ".3", ".4", ".5", ".6", ".7", ".8", ".9");
    foreach ($noneed as $noneed_) {
        $text = str_replace($noneed_, " ", $text);
    }
    $text = str_replace("!", ".", $text);
    $text = str_replace("?", ".", $text);
    $textarray = explode(".", $text);
    $textarrayf = array();
    foreach ($textarray as $textarray_) {
        if (!empty($textarray_) && strlen($textarray_) > 5) {
            $textarrayf[] = $textarray_;
        }
    }
    $count = count($textarrayf);
    return $count;
}

/**
 * @param $text
 * @param $words
 * @param $sentences
 * @return float|int
 */
function qtype_translateandspeak_averagepersentence($text, $words, $sentences) {
    if ($sentences == 0 || empty($sentences)) {
        return 0;
    }
    $count = round($words / $sentences, 2);
    return $count;
}

/**
 * @param $text
 * @param $word
 * @param $wordunic
 * @return float|int
 */
function qtype_translateandspeak_lexicaldensity($text, $word, $wordunic) {
    if ($word == 0 || empty($word)) {
        return 0;
    }
    $count = round(($wordunic / $word) * 100, 2);
    return $count;
}

/**
 * @param $text
 * @param $averagepersentence
 * @param $hardwordspersent
 * @return float
 */
function qtype_translateandspeak_fogindex($text, $averagepersentence, $hardwordspersent) {
    $count = round(($averagepersentence + $hardwordspersent) * 0.4, 2);
    return $count;
}

/**
 * @param $text
 * @return array
 */
function qtype_translateandspeak_laters($text) {
    $words = str_word_count($text, 1);
    $words_ = array();
    $result = array();

    $max = 1;

    foreach ($words as $word) {
        if (!in_array($word, $words_)) {
            $words_[] = strtolower($word);
            if (strlen($word) > $max) {
                $max = strlen($word);
            }
        }
    }

    for ($i = 1; $i <= $max; $i++) {
        foreach ($words as $word) {
            if (strlen($word) == $i) {
                if (!isset($result[$i])) {
                    $result[$i] = 0;
                }
                $result[$i]++;
            }
        }
    }
    return $result;
}

/**
 * @param $text
 * @param $wordstotal
 * @return array
 */
function qtype_translateandspeak_hardwords($text, $wordstotal) {
    $syllables = 0;
    $words = explode(' ', $text);
    for ($i = 0; $i < count($words); $i++) {
        if (qtype_translateandspeak_count_syllables($words[$i]) > 2) {
            $syllables++;
        }
    }

    if ($syllables == 0) {
        return array(0, 0);
    }

    $score = round(($syllables / $wordstotal) * 100, 2);
    return array($syllables, $score);
}

/**
 * @param $word
 * @return float|int
 */
function qtype_translateandspeak_count_syllables($word) {
    $nos = strtoupper($word);
    $syllables = 0;
    $before = strlen($nos);
    if ($before >= 2) {
        $nos = str_replace(array('AA', 'AE', 'AI', 'AO', 'AU',
                'EA', 'EE', 'EI', 'EO', 'EU', 'IA', 'IE', 'II', 'IO',
                'IU', 'OA', 'OE', 'OI', 'OO', 'OU', 'UA', 'UE',
                'UI', 'UO', 'UU'), "", $nos);
        $after = strlen($nos);
        $diference = $before - $after;
        if ($before != $after) {
            $syllables += $diference / 2;
        }
        if ($nos[strlen($nos) - 1] == "E") {
            $syllables--;
        }
        if ($nos[strlen($nos) - 1] == "Y") {
            $syllables++;
        }
        $before = $after;
        $nos = str_replace(array('A', 'E', 'I', 'O', 'U'), "", $nos);
        $after = strlen($nos);
        $syllables += ($before - $after);
    } else {
        $syllables = 0;
    }
    return $syllables;
}

/**
 * @param $input
 * @param $words
 * @return float
 */
function qtype_translateandspeak_levenshtein_percent($input, $words) {
    $shortest = -1;
    $lev = levenshtein($input, $words);

    if ($lev == 0) {
        $closest = $words;
        $shortest = 0;
    }

    if ($lev <= $shortest || $shortest < 0) {
        $closest = $words;
        $shortest = $lev;
    }

    $percent = 1 - levenshtein($input, $closest) / max(strlen($input), strlen($closest));

    $percent = round($percent * 100, 0);

    return $percent;
}


function qtype_translateandspeak_loading_animation($size = 50){
    return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin:auto;background:transparent;display:block;" width="'.$size.'px" height="'.$size.'px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
<g transform="rotate(0 50 50)">
  <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#0a0a0a">
    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.9166666666666666s" repeatCount="indefinite"></animate>
  </rect>
</g><g transform="rotate(30 50 50)">
  <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#0a0a0a">
    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.8333333333333334s" repeatCount="indefinite"></animate>
  </rect>
</g><g transform="rotate(60 50 50)">
  <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#0a0a0a">
    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.75s" repeatCount="indefinite"></animate>
  </rect>
</g><g transform="rotate(90 50 50)">
  <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#0a0a0a">
    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.6666666666666666s" repeatCount="indefinite"></animate>
  </rect>
</g><g transform="rotate(120 50 50)">
  <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#0a0a0a">
    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.5833333333333334s" repeatCount="indefinite"></animate>
  </rect>
</g><g transform="rotate(150 50 50)">
  <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#0a0a0a">
    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.5s" repeatCount="indefinite"></animate>
  </rect>
</g><g transform="rotate(180 50 50)">
  <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#0a0a0a">
    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.4166666666666667s" repeatCount="indefinite"></animate>
  </rect>
</g><g transform="rotate(210 50 50)">
  <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#0a0a0a">
    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.3333333333333333s" repeatCount="indefinite"></animate>
  </rect>
</g><g transform="rotate(240 50 50)">
  <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#0a0a0a">
    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.25s" repeatCount="indefinite"></animate>
  </rect>
</g><g transform="rotate(270 50 50)">
  <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#0a0a0a">
    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.16666666666666666s" repeatCount="indefinite"></animate>
  </rect>
</g><g transform="rotate(300 50 50)">
  <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#0a0a0a">
    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.08333333333333333s" repeatCount="indefinite"></animate>
  </rect>
</g><g transform="rotate(330 50 50)">
  <rect x="47" y="24" rx="3" ry="6" width="6" height="12" fill="#0a0a0a">
    <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="0s" repeatCount="indefinite"></animate>
  </rect>
</g>
</svg>';
}

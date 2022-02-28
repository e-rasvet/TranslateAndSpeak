<?php

$action = filter_var(trim($_REQUEST['action']), FILTER_SANITIZE_STRING);
$text = filter_var(trim($_REQUEST['text']), FILTER_SANITIZE_STRING);

if ($action == 'translateJPEN') {
    if (empty($text)) {
        $text = 'こんにちは';
    }

    /*
     * This code didn't works if I add moodle config requests
     */
    $url = 'https://api-free.deepl.com/v2/translate';
    $data = array('auth_key' => '09585106-babd-a9ec-8d5f-161db6cf99a5:fx', 'text' => $text, 'target_lang' => 'EN');

    $options = array(
            'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
            )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) { /* Handle error */ }

    $json = json_decode($result);

    $response = [];
    $response['code'] = "200";
    $response['transcript'] = $text;
    $response['translate'] = $json->translations[0]->text;

    header("Content-Type:application/json");
    echo json_encode($response);
} else {

    require_once '../../../config.php';
    require_once 'lib.php';
    require_once($CFG->libdir . '/questionlib.php');

    $action  = optional_param('action', 0, PARAM_TEXT);
    $answer  = optional_param('answer', 0, PARAM_TEXT);
    $targetAnswer  = optional_param('targetanswer', 0, PARAM_TEXT);
    $targetAnswerJapan  = optional_param('targetanswerjp', 0, PARAM_TEXT);
    $usageid  = optional_param('usageid', 0, PARAM_INT);
    $slotid = optional_param('slotid', 1, PARAM_INT);
    $hint = optional_param('hint', 0, PARAM_INT);
    $cmid = optional_param('cmid', 0, PARAM_INT);
    $userid = optional_param('userid', 0, PARAM_INT);


    if ($action == "compareAnswer") {
        $quba = question_engine::load_questions_usage_by_activity($usageid);
        $qa = $quba->get_question_attempt($slotid);

        $data = qtype_translateandspeak_compare_answer($answer, $targetAnswer, $qa);

        $add = (object)[];
        $add->usageid = $usageid;
        $add->slotid = $slotid;
        $add->answer = $answer;
        $add->targetanswer = trim($targetAnswer);
        $add->targetanswerjp = trim($targetAnswerJapan);
        $add->cmid = $cmid;
        $add->userid = $userid;
        $add->time = time();

        $DB->insert_record("qtype_tas_responses", $add);

        echo json_encode($data);
    }
}

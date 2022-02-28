define(['jquery'], function ($) {
    var questionManager = {
        init: function (containerId, readOnly, questionID, usageID, slotID, gradeID, answerID, targetAnswerID, targetAnswerJpID, boxID, cmID, userID) {
            let typeRoot = M.cfg.wwwroot + "/question/type/translateandspeak";

            console.log("Init: " + slotID + " | " + usageID + " | " + readOnly + " | " + gradeID);


            var jpspeech = "";
            const buttonJp = document.getElementById("translateandspeak-listening-btnJp-" + questionID);
            const buttonEn = document.getElementById("translateandspeak-listening-btnEn-" + questionID);
            const resultEn = document.getElementById("translateandspeak-listening-text-en-" + questionID);
            const resultJp = document.getElementById("translateandspeak-listening-text-jp-" + questionID);
            const translationAnimation = "translateandspeak-translation-animation-" + questionID;
            const translationInprocess = "translateandspeak-translation-inprocess-" + questionID;
            const translationEnglishBtn = "translateandspeak-translation-entext-" + questionID;
            const translationBoxEn = "translateandspeak_box_en_" + questionID;
            const translationBoxJp = "translateandspeak_box_jp_" + questionID;
            const translationBoxAnswer = "translateandspeak_box_answer_" + questionID;

            let listening = false;
            let speechToTextLang = "";
            let button = "";
            let translateText = "";
            let JapanText = "";
            let result = resultJp;

            const SpeechRecognition =
                window.SpeechRecognition || window.webkitSpeechRecognition;

            if (typeof SpeechRecognition !== "undefined") {
                const recognition = new SpeechRecognition();

                const stop = () => {
                    recognition.stop();
                    button.textContent = "Start recording.";

                    console.log(jpspeech);

                    if (speechToTextLang == "jp") {
                        document.getElementById(translationAnimation).style.display = 'block';
                        document.getElementById(translationInprocess).style.display = 'block';
                        document.getElementById(translationBoxEn).style.display = 'block';

                        document.getElementById(targetAnswerJpID).value = jpspeech;

                        JapanText = jpspeech;

                        $.getJSON(typeRoot + "/ajax.php", {action: "translateJPEN", text: jpspeech},
                            function (data) {
                                document.getElementById(translationAnimation).style.display = 'none';
                                document.getElementById(translationInprocess).style.display = 'none';
                                document.getElementById(translationEnglishBtn).style.display = 'block';

                                console.log(data);
                                console.log(data.translate);

                                translateText = data.translate;

                                document.getElementById(targetAnswerID).value = translateText;

                                speakText(translateText, 'en-US');
                            });
                    } else {
                        document.getElementById(translationBoxAnswer).style.display = 'block';
                        document.getElementById(answerID).value = jpspeech;

                        $.getJSON(typeRoot + "/ajax.php", {action: "compareAnswer", targetanswerjp: JapanText, targetanswer: translateText, answer: jpspeech, usageid: usageID, slotid: slotID, cmid: cmID, userid: userID},
                            function (json) {
                                console.log(json);

                                document.getElementById(gradeID).value = json.gradePercent;
                                document.getElementById(boxID).style.display = 'block';
                            });
                    }
                };

                const start = () => {
                    if (speechToTextLang == "jp") {
                        button = buttonJp;
                        result = resultJp;
                        recognition.lang = "ja-JP";
                    } else {
                        button = buttonEn;
                        result = resultEn;
                        recognition.lang = "en-US";
                    }

                    jpspeech = "";
                    recognition.start();

                    console.log(recognition.lang);

                    button.textContent = "Stop recording.";
                };

                const onResult = event => {
                    result.innerHTML = "";
                    jpspeech = "";
                    for (const res of event.results) {
                        const text = document.createTextNode(res[0].transcript);
                        const p = document.createElement("p");
                        if (res.isFinal) {
                            p.classList.add("final");
                        }
                        p.appendChild(text);
                        result.appendChild(p);

                        jpspeech = jpspeech + " " + res[0].transcript;
                    }
                };
                recognition.continuous = true;
                recognition.interimResults = true;
                recognition.addEventListener("result", onResult);
                buttonJp.addEventListener("click", event => {
                    speechToTextLang = "jp";

                    if (listening) {
                        buttonJp.classList.add('translateandspeak_button_green');
                        buttonJp.classList.remove('translateandspeak_button_red');
                    } else {
                        buttonJp.classList.add('translateandspeak_button_red');
                        buttonJp.classList.remove('translateandspeak_button_green');
                    }

                    listening ? stop() : start();
                    listening = !listening;
                });
                buttonEn.addEventListener("click", event => {
                    speechToTextLang = "en";

                    if (listening) {
                        buttonEn.classList.add('translateandspeak_button_green');
                        buttonEn.classList.remove('translateandspeak_button_red');
                    } else {
                        buttonEn.classList.add('translateandspeak_button_red');
                        buttonEn.classList.remove('translateandspeak_button_green');
                    }

                    listening ? stop() : start();
                    listening = !listening;
                });
            } else {
                buttonJp.remove();
                buttonEn.remove();
                const message = document.getElementById("message");
                message.removeAttribute("hidden");
                message.setAttribute("aria-hidden", "false");
            }

            function speakText(text, lang) {
                var msg = new SpeechSynthesisUtterance();
                var voices = speechSynthesis.getVoices();
                msg.text = text;
                msg.lang = lang;

                speechSynthesis.speak(msg);
            }
        },
    }
    return {
        init: questionManager.init
    };
});
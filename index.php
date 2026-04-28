<?php
session_start();
include "php/name_config.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Tell search engines not to index the site -->
    <meta name="robots" content="noindex, nofollow">
    
    <meta charset="utf-8">
    <title>VivaAI - AI Speaking Test</title>
	
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Practice English conversation using AI.">
	
    <!-- Image -->
    <link rel="shortcut icon" type="image/png" href="assets/e-icon.png">
	
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	
    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="css/w3.css">
    <link rel="stylesheet" href="css/vivaai.css">
	
</head>

<body>
    <div class="top-nav">
        <div class="top-nav-inner">
            <button class="nav-tab active" data-page="home-page" onclick="switchPage('home-page')">Home</button>
            <button class="nav-tab" data-page="dashboard-page" onclick="switchPage('dashboard-page')">Dashboard</button>
            <button class="nav-tab" data-page="test-page" onclick="switchPage('test-page')">Speaking Test</button>
            <button class="nav-tab" data-page="interview-page" onclick="switchPage('interview-page')">Interview Mode</button>
            <button class="nav-tab" data-page="settings-page" onclick="switchPage('settings-page')">Settings</button>
        </div>
    </div>

    <div class="app-shell">
        <div id="home-page" class="spa-page active-page">
            <div class="saas-card hero">
                <h1>AI Speaking Test & Interview Simulator</h1>
                <p>Practice speaking, get real feedback, improve faster.</p>
                <div class="hero-buttons">
                    <button class="btn-primary" onclick="openModeAndStart('speaking')">Start Speaking Test</button>
                    <button class="btn-secondary" onclick="openModeAndStart('interview')">Try Interview Mode</button>
                </div>
                <div class="feature-grid">
                    <div class="feature-item">Real-time feedback</div>
                    <div class="feature-item">AI scoring</div>
                    <div class="feature-item">Voice practice</div>
                </div>
            </div>
        </div>

        <div id="dashboard-page" class="spa-page">
            <div class="saas-card">
                <h3>Performance Dashboard</h3>
                <div class="dashboard-grid">
                    <div class="metric-card"><h4>Average Score</h4><p id="dash-average">0.0</p></div>
                    <div class="metric-card"><h4>Last Test Score</h4><p id="dash-last">0.0</p></div>
                    <div class="metric-card"><h4>Strongest Skill</h4><p id="dash-strongest">-</p></div>
                    <div class="metric-card"><h4>Weakest Skill</h4><p id="dash-weakest">-</p></div>
                </div>
                <h4 style="margin-top:16px;">Last 5 Attempts</h4>
                <div id="dashboard-attempts"></div>
            </div>
        </div>

        <div id="test-page" class="spa-page">
            <p class="w3-small w3-padding-left w3-text-white hide-on-phone space-letters"><a href="#" target="_blank">GitHub</a></p>
            <div class="container w3-animate-opacity">
        <div id="main-image" class="w3-center w3-round w3-padding w3-text-blue">
			
            <!--
				<i class="fa fa-commenting-o w3-text-red" style="font-size:75px"></i> 
			-->
			
			<h2 class="space-letters"><b>VivaAI Speaking Practice</b></h2>
			
            <h4 class="space-letters"><b>Practice English conversation using AI</b></h4>
		
        </div>
        <main id="chat" class="texts">
            <div class="message-container">
                <span id="first-chat-block" class="set-color1"><b>&#x2022 VivaAI</b></span>
                
                <ul class="lighter-black instruction-text">
					<li>Hi. Welcome to VivaAI.</li>
					<li>Please select your first language in the settings menu.</li>
					<li>For best results please use the Google Chrome browser.</li>
					<li>Also, don't allow your browser to translate this page.</li>
					<li>Chat ideas:<br>
						- Can we role-play a job interview?<br>
						- Who is Beyoncé?<br>
						- Is there life on other planets?
					</li>
					<li>Please be kind. VivaAI can still make mistakes.</li>	
					
                </ul>
            </div>
            <!-- Add more message containers here -->
            <!-- The div for the spinner gets added and deleted here. -->
        </main>
        <div class="sticky-bar">
			
		<button class="" id="start-voicechat-btn" onclick="start_recog(submit_text_to_php, lang_code)">Start Voicechat</button>
		<button type="button" id="start-test-btn">Start Test</button>
		<select id="test-mode-select" class="styled-dropdown" style="margin-left:8px;">
			<option value="speaking" selected>Speaking Test</option>
			<option value="interview">Interview Mode</option>
		</select>
		<select id="test-level-select" class="styled-dropdown" style="margin-left:8px;">
			<option value="beginner">Beginner</option>
			<option value="intermediate" selected>Intermediate</option>
			<option value="advanced">Advanced</option>
		</select>
		<select id="test-category-select" class="styled-dropdown" style="margin-left:8px;">
			<option value="personal" selected>Personal</option>
			<option value="opinion">Opinion</option>
			<option value="abstract">Abstract</option>
			<option value="interview">Interview</option>
		</select>
		<div id="test-timer" class="w3-padding w3-text-white">Timer: 60s</div>
		<div id="test-dashboard" class="w3-padding w3-text-white"></div>
		<div id="test-progress" class="w3-padding w3-text-white">Question 1 of 5</div>
		<div id="test-status" class="w3-padding w3-text-white">Ready</div>
		<label class="w3-text-white" style="margin-left:8px;">
			<input type="checkbox" id="live-feedback-toggle"> Live Feedback Mode
		</label>
			
            <form id="myForm" action="main.php" method="post">
                <input id="user-input" type="text" name="my_message" placeholder="Type or talk in English" autofocus>
                <input type="hidden" name="robotblock">
                <input id="submit-btn" type="submit" value="Send">
                <div class="w3-padding space-letters">
                    <button id="accordion"><i class="fa fa-gear w3-padding-right" style="font-size:25px;color:white"></i>Settings</button>
                    <span class="w3-padding-right" style="cursor: pointer;" onclick="quiet_please()">
                        <i class="fa fa-volume-off" style="font-size:27px"></i>
                        <i class="fa fa-close w3-padding-right" style="font-size:18px"></i>
                    </span>
                    <div id="audioIndicator">
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                    </div>
                    <div id="audioIndicator1">
                        <div class="bar1"></div>
                        <div class="bar1"></div>
                        <div class="bar1"></div>
                    </div>
                </div>
                <div class="wrapper">
                    <div class="form-elements">
                        <div id="panel">
                            <div id="line1" class="radio-group">
                                <label class="radio-option">
                                    <input id="speakid" class="w3-padding" type="radio" name="speak1" value="speak" onclick="toggleRadio(this)">
                                    Auto Speak
                                </label>
                                <label class="radio-option">
                                    <input id="correctid" type="radio" name="correct1" value="correct" onclick="toggleRadio(this)">
                                    Correction
                                </label>
                                <label class="radio-option">
                                    <input id="translateid" type="radio" name="translate1" value="translate" onclick="toggleRadio(this)">
                                    Translation
                                </label>
                            </div>
                            <div id="line2">
                                
                                <div id="dropdown1" class="dropdown-option w3-padding-left w3-padding-bottom">
                                    <select class="styled-dropdown" id="language-select" name='user_language' onchange="updateSelectedOption(this)">
										<option value="Burmese">Burmese (မြန်မာဘာသာ)</option>
                                        <option value="Chinese">Chinese (中文简体)</option>
										<option value="English">English</option>
                                        <option value="French">French (Français)</option>
                                        <option value="German">German (Deutsch)</option>
										<option value="Hindi">Hindi (हिन्दी)</option>
                                        <option value="Japanese">Japanese (日本語)</option>
                                        <option value="Korean">Korean (한국어)</option>
										 <option value="Portuguese">Portuguese</option>
										<option value="Russian">Russian (Русский)</option>
                                        <option value="Spanish">Spanish (Español)</option>
										<option value="Swahili">Swahili (Kiswahili)</option>
                                        <option value="Thai" selected>Thai (ไทย)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
            </div>
        </div>

        <div id="interview-page" class="spa-page">
            <div class="saas-card">
                <h3>Interview Mode</h3>
                <p>Practice recruiter-style questions and get actionable feedback.</p>
                <div class="hero-buttons">
                    <button class="btn-primary" onclick="openModeAndStart('interview')">Start Interview Session</button>
                    <button class="btn-secondary" onclick="switchPage('test-page')">Go to Test Workspace</button>
                </div>
                <div class="feature-grid" style="margin-top:16px;">
                    <div class="feature-item">
                        <b>Interview Question</b>
                        <p id="interview-question-view">No question yet.</p>
                    </div>
                    <div class="feature-item">
                        <b>Improved Answer</b>
                        <p id="interview-improved-view">No data yet.</p>
                    </div>
                    <div class="feature-item">
                        <b>Recruiter Feedback</b>
                        <p id="interview-recruiter-view">No data yet.</p>
                    </div>
                    <div class="feature-item">
                        <b>Confidence Feedback</b>
                        <p id="interview-confidence-view">No data yet.</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="settings-page" class="spa-page">
            <div class="saas-card">
                <h3>Settings</h3>
                <div class="settings-grid">
                    <div class="settings-group">
                        <h4>Behavior</h4>
                        <label><input type="checkbox" id="setting-auto-speak"> Auto Speak</label>
                        <label><input type="checkbox" id="setting-correction"> Correction</label>
                        <label><input type="checkbox" id="setting-translation"> Translation</label>
                        <label><input type="checkbox" id="setting-live-feedback"> Live Feedback Mode</label>
                    </div>
                    <div class="settings-group">
                        <h4>Preferences</h4>
                        <label>Language
                            <select id="setting-language"></select>
                        </label>
                        <label><input type="checkbox" id="setting-theme"> Light Theme</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- The page gets scrolled up to this id. -->
    <div id="vivaai-bottom"></div>
    <!-- Onload a click is simulated on this to scroll the page to id="bottom-bar" -->
    <a href="#vivaai-bottom" id="scroll-page-up"></a>
    <a href="#test100" id="scroll-to-last-message"></a>
    <a href="#assistant-message" id="scroll-to-assistant-message"></a>
	
</body>
</html>




<!-- Import the utils.js file -->
<script src="js/utils.js"></script>

<script>
speechSynthesis.onvoiceschanged = () => {
  const voices = speechSynthesis.getVoices();
  voices
    .filter(v => v.lang === 'en-US')
    .forEach(v => v.name);
};
</script>

<!-- 
Import the config.js file.
The language code varuable (lang_code) is set in this file.
-->
<script src="js/config.js"></script>

<script>
    // Event listener that prevents the form from submitting when
    // the "Settings" button is clicked.
    document.getElementById('accordion').addEventListener('click', function(event) {
        event.preventDefault();
    });

    // JavaScript to toggle the visibility of the panel with a smooth transition
    document.getElementById('accordion').addEventListener('click', function() {
        var panel = document.getElementById('panel');
        if (panel.style.maxHeight) {
            panel.style.maxHeight = null;
        } else {
            panel.style.maxHeight = panel.scrollHeight + "px";
        }
    });
	
	

	
	// *** ON LOAD ***
    // Comment or uncomment these lines to check (select) radio
    // when the page loads.
    // Selects and checks radio buttons when the page loads
    window.onload = function() {
        checkRadioButton('speak1', 'speakid');
        checkRadioButton('correct1', 'correctid');
        checkRadioButton('translate1', 'translateid');
    };
</script>

<script>
    // These names are set in name_config.php
    // That file has been included at the top of this page.
    const bot_name = "<?php echo $bot_name; ?>";
    const user_name = "<?php echo $user_name; ?>";
    // Set the name of the bot in the first chat block
    document.getElementById("first-chat-block").innerHTML = "<b>&#x2022 " + bot_name + "</b>";
</script>

<script>
    let testModeActive = false;
    let testTimerId = null;
    let testTimeLeft = 60;
    let currentTestMode = "speaking";
    let currentQuestion = "";
    let currentQuestionNumber = 1;
    let totalQuestions = 5;
    let isEvaluating = false;
    let isLoadingQuestion = false;
    let lastUserAnswer = "";
    let lastOverallBand = null;
    let repeatAfterAIMode = false;
    let liveFeedbackMode = false;
    let currentEvaluation = null;
    const SCORE_KEYS = ["fluency_score", "grammar_score", "vocabulary_score", "coherence_score", "confidence_score", "overall_band"];
    const FILLER_WORDS = ["um", "uh", "like", "you know"];

    function switchPage(pageId) {
        document.querySelectorAll(".spa-page").forEach(function(el) {
            el.classList.remove("active-page");
        });
        document.querySelectorAll(".nav-tab").forEach(function(btn) {
            btn.classList.remove("active");
            if (btn.getAttribute("data-page") === pageId) {
                btn.classList.add("active");
            }
        });
        const page = document.getElementById(pageId);
        if (page) {
            page.classList.add("active-page");
        }
        if (pageId === "dashboard-page") {
            updateProgressDashboard();
        }
        if (pageId === "settings-page") {
            populateSettingsPage();
        }
        if (pageId === "interview-page") {
            updateInterviewPreview();
        }
    }

    function openModeAndStart(mode) {
        document.getElementById("test-mode-select").value = mode === "interview" ? "interview" : "speaking";
        switchPage("test-page");
        startSpeakingTest();
    }

    function updateInterviewPreview() {
        const q = document.getElementById("interview-question-view");
        const i = document.getElementById("interview-improved-view");
        const r = document.getElementById("interview-recruiter-view");
        const c = document.getElementById("interview-confidence-view");
        if (q) q.textContent = currentQuestion || "No question yet.";
        if (i) i.textContent = (currentEvaluation && currentEvaluation.improved_answer) ? currentEvaluation.improved_answer : "No data yet.";
        if (r) r.textContent = (currentEvaluation && currentEvaluation.recruiter_feedback) ? currentEvaluation.recruiter_feedback : "No data yet.";
        if (c) c.textContent = (currentEvaluation && currentEvaluation.confidence_feedback) ? currentEvaluation.confidence_feedback : "No data yet.";
    }

    function updateTimerUi() {
        const timer = document.getElementById("test-timer");
        timer.textContent = "Timer: " + testTimeLeft + "s";
    }

    function setTestStatus(text) {
        const el = document.getElementById("test-status");
        el.textContent = text;
    }

    function showToast(message) {
        const toast = document.getElementById("toast-message");
        toast.textContent = message;
        toast.classList.add("show-toast");
        setTimeout(function() {
            toast.classList.remove("show-toast");
        }, 2500);
    }

    function updateQuestionProgress(questionNo, total) {
        currentQuestionNumber = questionNo;
        totalQuestions = total;
        document.getElementById("test-progress").textContent = "Question " + questionNo + " of " + total;
    }

    function setInputDisabled(disabled) {
        document.getElementById("user-input").disabled = disabled;
        document.getElementById("submit-btn").disabled = disabled;
    }

    function stopTestTimer() {
        if (testTimerId) {
            clearInterval(testTimerId);
            testTimerId = null;
        }
    }

    function startTestTimer(seconds) {
        stopTestTimer();
        testTimeLeft = seconds;
        updateTimerUi();
        setInputDisabled(false);
        setTestStatus("Listening...");

        testTimerId = setInterval(function() {
            testTimeLeft -= 1;
            updateTimerUi();

            if (testTimeLeft <= 0) {
                stopTestTimer();
                setInputDisabled(true);
                addMessageToChat({
                    sender: bot_name,
                    text: "<p><b>Time is up.</b> Click Start Test to try again.</p>"
                });
                setTestStatus("Time ended");
            }
        }, 1000);
    }

    function startSpeakingTest() {
        if (isLoadingQuestion || isEvaluating) return;
        currentTestMode = document.getElementById("test-mode-select").value;
        const selectedLevel = document.getElementById("test-level-select").value;
        const selectedCategory = document.getElementById("test-category-select").value;
        isLoadingQuestion = true;
        setInputDisabled(true);
        setTestStatus("Next question loading...");
        const formData = new FormData();
        formData.append("action", "start_test");
        formData.append("test_mode", currentTestMode);
        formData.append("test_level", selectedLevel);
        formData.append("test_category", selectedCategory);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "main.php", true);
        xhr.onload = function() {
            isLoadingQuestion = false;
            if (xhr.status !== 200) {
                showToast("AI temporarily unavailable. Please try again.");
                setTestStatus("Ready");
                setInputDisabled(false);
                return;
            }

            const response = JSON.parse(xhr.responseText);
            if (!response.success) {
                return;
            }

            testModeActive = true;
            currentQuestion = response.question;
            updateInterviewPreview();
            updateQuestionProgress(response.question_number || 1, response.total_questions || 5);
            addMessageToChat({
                sender: bot_name,
                text: "<p class='question-fade'><b>Question:</b> " + currentQuestion + "</p>"
            });
            startTestTimer(response.time_limit || 60);
        };
        xhr.send(formData);
    }

    function scoreBar(label, score) {
        const clamped = Math.max(0, Math.min(9, Number(score || 0)));
        const widthPercent = (clamped / 9) * 100;
        return "<div class='score-row'><div class='score-label'>" + label + " <b>" + clamped + "/9</b></div><div class='score-bar'><div class='score-fill' style='width:" + widthPercent + "%'></div></div></div>";
    }

    function renderList(items, cssClass) {
        if (!items || items.length === 0) {
            return "<p class='" + cssClass + "'>No items.</p>";
        }
        return "<ul>" + items.map(function(item) {
            return "<li class='" + cssClass + "'>" + item + "</li>";
        }).join("") + "</ul>";
    }

    function buildMistakesTable(mistakes) {
        if (!mistakes || mistakes.length === 0) {
            return "<p>No major mistakes found.</p>";
        }
        let rows = mistakes.map(function(item) {
            return "<tr><td>" + (item.original || "") + "</td><td>" + (item.corrected || "") + "</td><td>" + (item.reason || "") + "</td></tr>";
        }).join("");
        return "<div class='mistakes-table-wrapper'><table class='mistakes-table'><thead><tr><th>Original</th><th>Corrected</th><th>Reason</th></tr></thead><tbody>" + rows + "</tbody></table></div>";
    }

    function formatEvaluationHtml(evaluation) {
        const fillerWords = (evaluation.filler_words_detected || []).join(", ");
        const hints = [];
        if ((evaluation.filler_words_detected || []).length >= 2) {
            hints.push("Too many fillers");
        }
        if ((evaluation.fluency_score || 0) <= 5) {
            hints.push("Try shorter sentences");
        }

        let starBlock = "";
        if (evaluation.star_answer) {
            starBlock = "<div class='eval-card'><b>STAR Answer</b><p><b>Situation:</b> " + (evaluation.star_answer.situation || "") + "</p><p><b>Task:</b> " + (evaluation.star_answer.task || "") + "</p><p><b>Action:</b> " + (evaluation.star_answer.action || "") + "</p><p><b>Result:</b> " + (evaluation.star_answer.result || "") + "</p></div>";
        }

        return "<div class='eval-section'>" +
            "<div class='eval-card result-slide'>" +
                scoreBar("Fluency", evaluation.fluency_score) +
                scoreBar("Grammar", evaluation.grammar_score) +
                scoreBar("Vocabulary", evaluation.vocabulary_score) +
                scoreBar("Coherence", evaluation.coherence_score) +
                scoreBar("Confidence", evaluation.confidence_score) +
                scoreBar("Overall Band", evaluation.overall_band) +
            "</div>" +
            "<div class='eval-card'><b>Strengths</b>" + renderList(evaluation.strengths || [], "strength-item") + "</div>" +
            "<div class='eval-card'><b>Weaknesses</b>" + renderList(evaluation.weaknesses || [], "weakness-item") + "</div>" +
            "<div class='eval-card'><b>Band Explanation</b><p>" + (evaluation.band_explanation || "") + "</p></div>" +
            "<div class='eval-card'><b>Improvement Tips</b>" + renderList(evaluation.improvement_tips || [], "strength-item") + "</div>" +
            "<div class='eval-card improved-answer'><b>Improved Answer</b><p>" + (evaluation.improved_answer || "") + "</p></div>" +
            "<div class='eval-card'><b>Your Original Answer</b><p>" + (lastUserAnswer || "") + "</p></div>" +
            "<div class='eval-card'><b>Speech Quality</b><p>Fillers: " + (fillerWords || "none") + "</p><p>" + hints.join(" | ") + "</p></div>" +
            "<div class='eval-card'><b>Mistake Corrections</b>" + buildMistakesTable(evaluation.mistakes || []) + "</div>" +
            "<div class='eval-card'><button type='button' class='eval-action-btn' onclick='listenImprovedAnswer()'>Listen to improved version</button> <button type='button' class='eval-action-btn' onclick='repeatAfterAI()'>Repeat after AI</button></div>" +
            "<div class='eval-card'><button type='button' class='eval-action-btn' onclick='getCoachingTips()'>Get Coaching Tips</button></div>" +
            starBlock +
            "<div class='eval-card'><b>Confidence Feedback</b><p>" + (evaluation.confidence_feedback || "") + "</p></div>" +
            "<div class='eval-card'><b>Recruiter Feedback</b><p>" + (evaluation.recruiter_feedback || "") + "</p></div>" +
        "</div>";
    }

    function getCoachingTips() {
        if (!lastUserAnswer || !currentEvaluation) return;
        setTestStatus("Analyzing...");
        const payload = new FormData();
        payload.append("action", "get_coaching_tips");
        payload.append("user_answer", lastUserAnswer);
        payload.append("evaluation_json", JSON.stringify(currentEvaluation));

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "main.php", true);
        xhr.onload = function() {
            setTestStatus("Ready");
            if (xhr.status !== 200) {
                showToast("AI temporarily unavailable. Please try again.");
                return;
            }
            const resp = JSON.parse(xhr.responseText);
            if (!resp.success || !resp.coaching) {
                showToast("AI temporarily unavailable. Please try again.");
                return;
            }
            const tips = resp.coaching.suggestions || [];
            const question = resp.coaching.practice_question || "";
            addMessageToChat({
                sender: bot_name,
                text: "<div class='eval-card'><b>Coaching Tips</b><ul>" + tips.map(t => "<li>" + t + "</li>").join("") + "</ul><p><b>Practice Question:</b> " + question + "</p></div>"
            });
        };
        xhr.send(payload);
    }

    function loadScoreHistory() {
        try {
            return JSON.parse(localStorage.getItem("vivaai_test_scores") || "[]");
        } catch (e) {
            return [];
        }
    }

    function saveScoreHistory(history) {
        localStorage.setItem("vivaai_test_scores", JSON.stringify(history));
    }

    function updateProgressDashboard() {
        const history = loadScoreHistory();
        const dashboard = document.getElementById("test-dashboard");
        const dashboardMain = document.getElementById("dashboard-attempts");
        const dashAvg = document.getElementById("dash-average");
        const dashLast = document.getElementById("dash-last");
        const dashStrong = document.getElementById("dash-strongest");
        const dashWeak = document.getElementById("dash-weakest");
        if (!history.length) {
            dashboard.innerHTML = "Dashboard: No tests yet";
            if (dashboardMain) {
                dashboardMain.innerHTML = "<div class='empty-state'>No data yet</div>";
            }
            if (dashAvg) dashAvg.textContent = "0.0";
            if (dashLast) dashLast.textContent = "0.0";
            if (dashStrong) dashStrong.textContent = "-";
            if (dashWeak) dashWeak.textContent = "-";
            return;
        }

        const avgOverall = history.reduce((sum, item) => sum + Number(item.overall_band || 0), 0) / history.length;
        const trend = history.length > 1 && history[history.length - 1].overall_band > history[history.length - 2].overall_band ? "Increasing" :
            history.length > 1 && history[history.length - 1].overall_band < history[history.length - 2].overall_band ? "Decreasing" : "Stable";

        const perSkill = {};
        SCORE_KEYS.forEach(function(key) {
            const avg = history.reduce((sum, item) => sum + Number(item[key] || 0), 0) / history.length;
            perSkill[key] = avg;
        });

        const sorted = Object.keys(perSkill).sort(function(a, b) { return perSkill[b] - perSkill[a]; });
        const strongestSkill = sorted[0].replace("_score", "").replace("_", " ");
        const weakestSkill = sorted[sorted.length - 1].replace("_score", "").replace("_", " ");
        dashboard.innerHTML = "Dashboard: Avg " + avgOverall.toFixed(1) + " | Trend: " + trend +
            "<br>Your strongest area: " + strongestSkill +
            "<br>Focus on improving: " + weakestSkill;

        if (dashAvg) dashAvg.textContent = avgOverall.toFixed(1);
        if (dashLast) dashLast.textContent = Number(history[history.length - 1].overall_band || 0).toFixed(1);
        if (dashStrong) dashStrong.textContent = strongestSkill;
        if (dashWeak) dashWeak.textContent = weakestSkill;
        if (dashboardMain) {
            dashboardMain.innerHTML = history.map(function(item, idx) {
                const val = Number(item.overall_band || 0);
                const width = Math.max(2, Math.min(100, (val / 9) * 100));
                return "<div class='attempt-row'><span>#" + (idx + 1) + "</span><div class='attempt-bar' style='width:" + width + "%'></div><span>" + val.toFixed(1) + "</span></div>";
            }).join("");
        }
    }

    function updateProgressHistory(evaluation) {
        const history = loadScoreHistory();
        const snapshot = {
            fluency_score: Number(evaluation.fluency_score || 0),
            grammar_score: Number(evaluation.grammar_score || 0),
            vocabulary_score: Number(evaluation.vocabulary_score || 0),
            coherence_score: Number(evaluation.coherence_score || 0),
            confidence_score: Number(evaluation.confidence_score || 0),
            overall_band: Number(evaluation.overall_band || 0)
        };
        history.push(snapshot);
        while (history.length > 5) {
            history.shift();
        }
        saveScoreHistory(history);
        updateProgressDashboard();
    }

    function listenImprovedAnswer() {
        const blocks = document.querySelectorAll(".improved-answer p");
        if (!blocks.length) return;
        const text = blocks[blocks.length - 1].innerText || "";
        if (text) {
            speak(
                text,
                window.speech_lang_code || "en-UK",
                window.speech_voice_name || "Serena",
                window.speech_rate || 1
            );
        }
    }

    function repeatAfterAI() {
        const blocks = document.querySelectorAll(".improved-answer p");
        if (!blocks.length) return;
        const text = blocks[blocks.length - 1].innerText || "";
        const input = document.getElementById("user-input");
        input.value = text;
        repeatAfterAIMode = true;
        setTestStatus("Listening...");
        input.focus();
    }

    function highlightFillersRealtime(text) {
        let html = (text || "");
        FILLER_WORDS.forEach(function(word) {
            const pattern = new RegExp("\\b" + word.replace(" ", "\\s+") + "\\b", "gi");
            html = html.replace(pattern, "<span class='filler-highlight'>$&</span>");
        });
        return html;
    }

    function saveSettings() {
        const settings = {
            autoSpeak: !!document.getElementById("setting-auto-speak").checked,
            correction: !!document.getElementById("setting-correction").checked,
            translation: !!document.getElementById("setting-translation").checked,
            liveFeedback: !!document.getElementById("setting-live-feedback").checked,
            language: document.getElementById("setting-language").value || "English",
            lightTheme: !!document.getElementById("setting-theme").checked
        };
        localStorage.setItem("vivaai_settings", JSON.stringify(settings));
        applySettingsToControls(settings);
    }

    function getSavedSettings() {
        try {
            return JSON.parse(localStorage.getItem("vivaai_settings") || "{}");
        } catch (e) {
            return {};
        }
    }

    function applySettingsToControls(settings) {
        const autoSpeak = document.getElementById("speakid");
        const correction = document.getElementById("correctid");
        const translation = document.getElementById("translateid");
        const language = document.getElementById("language-select");
        const liveToggle = document.getElementById("live-feedback-toggle");

        autoSpeak.checked = !!settings.autoSpeak;
        correction.checked = !!settings.correction;
        translation.checked = !!settings.translation;
        liveFeedbackMode = !!settings.liveFeedback;
        liveToggle.checked = liveFeedbackMode;
        if (settings.language && language.querySelector("option[value='" + settings.language + "']")) {
            language.value = settings.language;
        }

        if (settings.lightTheme) {
            document.body.classList.add("light-theme");
        } else {
            document.body.classList.remove("light-theme");
        }
    }

    function populateSettingsPage() {
        const settings = getSavedSettings();
        const langSelect = document.getElementById("setting-language");
        if (langSelect.options.length === 0) {
            Array.from(document.getElementById("language-select").options).forEach(function(opt) {
                const clone = opt.cloneNode(true);
                langSelect.appendChild(clone);
            });
        }
        document.getElementById("setting-auto-speak").checked = !!settings.autoSpeak;
        document.getElementById("setting-correction").checked = !!settings.correction;
        document.getElementById("setting-translation").checked = !!settings.translation;
        document.getElementById("setting-live-feedback").checked = !!settings.liveFeedback;
        document.getElementById("setting-theme").checked = !!settings.lightTheme;
        if (settings.language) {
            langSelect.value = settings.language;
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("start-test-btn").addEventListener("click", startSpeakingTest);
        document.getElementById("live-feedback-toggle").addEventListener("change", function(e) {
            liveFeedbackMode = !!e.target.checked;
        });
        ["setting-auto-speak","setting-correction","setting-translation","setting-live-feedback","setting-language","setting-theme"].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener("change", saveSettings);
            }
        });

        applySettingsToControls(getSavedSettings());
        updateTimerUi();
        updateProgressDashboard();
        switchPage("home-page");
    });
</script>

<script>
    // PHP Ajax Code
    ///////////////////
    var form = document.getElementById('myForm');
	
    form.onsubmit = function(event) {
        // Prevent the default form submission behavior
        event.preventDefault();

        if (testModeActive) {
            if (isEvaluating || isLoadingQuestion) {
                return;
            }
            var formDataTest = new FormData(form);
            form.reset();

            var testAnswer = formDataTest.get("my_message");
            if (testAnswer == "") {
                return;
            }
            lastUserAnswer = String(testAnswer || "").replace(/\s{2,}/g, " ... ");
            isEvaluating = true;
            setInputDisabled(true);
            setTestStatus("Analyzing...");

            addMessageToChat({
                sender: user_name,
                text: formatResponse(lastUserAnswer)
            });

            create_spinner_div();

            var xhrTest = new XMLHttpRequest();
            xhrTest.open('POST', form.action, true);
            xhrTest.onload = function() {
                if (xhrTest.status !== 200) {
                    delete_spinner_div();
                    isEvaluating = false;
                    setInputDisabled(false);
                    setTestStatus("Ready");
                    showToast("AI temporarily unavailable. Please try again.");
                    return;
                }

                var responseTest = JSON.parse(xhrTest.responseText);
                delete_spinner_div();

                if (!responseTest.success) {
                    isEvaluating = false;
                    setInputDisabled(false);
                    setTestStatus("Ready");
                    showToast("AI temporarily unavailable. Please try again.");
                    return;
                }

                stopTestTimer();

                addMessageToChat({
                    sender: bot_name,
                    text: formatEvaluationHtml(responseTest.evaluation)
                });
                currentEvaluation = responseTest.evaluation || null;
                updateInterviewPreview();
                updateProgressHistory(responseTest.evaluation || {});

                if (repeatAfterAIMode && lastOverallBand !== null) {
                    const newBand = Number((responseTest.evaluation && responseTest.evaluation.overall_band) || 0);
                    addMessageToChat({
                        sender: bot_name,
                        text: "<p><b>You improved from " + Number(lastOverallBand).toFixed(1) + " → " + newBand.toFixed(1) + "</b></p>"
                    });
                    repeatAfterAIMode = false;
                }
                lastOverallBand = Number((responseTest.evaluation && responseTest.evaluation.overall_band) || 0);

                if (responseTest.evaluation && responseTest.evaluation.error) {
                    addMessageToChat({
                        sender: bot_name,
                        text: "<p class='weakness-item'><b>Evaluator warning:</b> " + (responseTest.evaluation.error_message || "Unable to parse evaluation") + "</p>"
                    });
                }

                if (responseTest.has_more_questions && responseTest.next_question) {
                    isLoadingQuestion = true;
                    setTestStatus("Next question loading...");
                    currentQuestion = responseTest.next_question;
                    updateInterviewPreview();
                    updateQuestionProgress(responseTest.question_number || currentQuestionNumber + 1, responseTest.total_questions || totalQuestions);
                    addMessageToChat({
                        sender: bot_name,
                        text: "<p class='question-fade'><b>Question:</b> " + currentQuestion + "</p>"
                    });
                    startTestTimer(responseTest.time_limit || 60);
                    isLoadingQuestion = false;
                    isEvaluating = false;
                } else {
                    testModeActive = false;
                    setInputDisabled(true);
                    const history = loadScoreHistory();
                    let strongest = "n/a";
                    let weakest = "n/a";
                    if (history.length) {
                        const avgByKey = {};
                        SCORE_KEYS.forEach(function(key) {
                            avgByKey[key] = history.reduce((sum, item) => sum + Number(item[key] || 0), 0) / history.length;
                        });
                        const sortedKeys = Object.keys(avgByKey).sort(function(a, b) { return avgByKey[b] - avgByKey[a]; });
                        strongest = sortedKeys[0].replace("_score", "").replace("_", " ");
                        weakest = sortedKeys[sortedKeys.length - 1].replace("_score", "").replace("_", " ");
                    }
                    addMessageToChat({
                        sender: bot_name,
                        text: "<div class='eval-card'><p><b>Test completed.</b></p><p>Average score: " + (history.length ? (history.reduce((s, h) => s + Number(h.overall_band || 0), 0) / history.length).toFixed(1) : "0.0") + "</p><p>Strongest skill: " + strongest + "</p><p>Weakest skill: " + weakest + "</p><button type='button' class='eval-action-btn' onclick='startSpeakingTest()'>Take Test Again</button></div>"
                    });
                    setTestStatus("Ready");
                    isEvaluating = false;
                }
            };

            formDataTest.append("action", "submit_test_answer");
            formDataTest.append("test_mode", currentTestMode);
            formDataTest.append("test_level", document.getElementById("test-level-select").value);
            formDataTest.append("test_category", document.getElementById("test-category-select").value);
            xhrTest.send(formDataTest);
            return;
        }
		
        // Get the form data
        var formData = new FormData(form);
		
        // Clear the form input
        form.reset();
		
        // Get the value of my_message
        var $my_message = formData.get("my_message");
		
        // This will prevent the form from submitting
        // if the user input field is empty.
        if ($my_message == "") {
            return; // Exit the function if the condition is not met
        }
		
        // Format the input into paragraphs. This
        // adds paragraph html to the students chat.
        // Useful for preserving readable multi-line user input.
        // to be formatted into separate paragraphs.
        $my_message = formatResponse($my_message);
		
        var input_message = {
            sender: user_name,
            text: $my_message
        };
		
        // Add a user message to the chat
        addMessageToChat(input_message);
		
        // Show the spinner while waiting for the response from openai
        create_spinner_div();
		
        // Scroll the page up by clicking on a div at the bottom of the page.
        simulateClick('scroll-page-up');
		
        // Delete the id from the message container.
        // It will get added again when the message container is created.
        var element = document.getElementById("assistant-message");
        element.removeAttribute("id");
		
        // Send an AJAX request to the server to process the form data
        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);
        xhr.onload = function() {
			
            if (xhr.status === 200) {
				
                var response = JSON.parse(xhr.responseText);
				
                var check_text = response.check_array;
				
				// Make these variables global by attaching
				// them to the window object.
				window.speech_lang_code = response.speech_lang_code;
				window.speech_voice_name = response.speech_voice_name;
				window.speech_rate = response.speech_rate;
				
				
                var response_text = response.chat_text;
                var text_to_speak = response.text_to_speak;
                var speak_status = response.speak_status;
                var translation_language = response.translation_language;
                let correctedUserMessage = response.corrected_text;
				
                if (correctedUserMessage !== 'none' && correctedUserMessage !== 'api_error') {
                    correctedUserMessage = correctedUserMessage;
                }
				
                let translatedChatAgentResponse = response.translated_text;
				
                if (translatedChatAgentResponse !== 'none' && translatedChatAgentResponse !== 'api_error') {
                    translatedChatAgentResponse = replaceItemsInString(translatedChatAgentResponse);
					
					// Remove the escape backslashes (\")
					translatedChatAgentResponse = removeEscapeSlashes(translatedChatAgentResponse);
					
					// Remove newline chracters (\n\n)
					translatedChatAgentResponse = removeNewlines(translatedChatAgentResponse);
                }
				
				
                let chatAgentResponse = response.chat_text;
				
                // Remove emojis
                chatAgentResponse = removeEmojis(chatAgentResponse);
				
                let correctedText, translatedText, chatText, finalText;
				
                // Handle corrected user message
                if (correctedUserMessage !== 'none') {
                    correctedText = `<p class='lighter-black'><i>Correction: ${correctedUserMessage}</i></p>`;
                } else {
                    correctedText = "";
                }
				
                // Handle translated chat agent response
                if (translatedChatAgentResponse !== 'none') {
                    translatedText = `<p class='lighter-black'>${translatedChatAgentResponse}</p>`;
                } else {
                    translatedText = "";
                }
				
                // For Deaf Accessibility.
                // Deaf people won't know that the audio is on
                // and the assistant is speaking.
                if (speak_status == 'selected') {
                    // Handle chat agent response
                    chatText = `<p class="clickable" onclick="speakText(this.innerHTML)">${chatAgentResponse}<i class="fa fa-volume-up w3-text-teal display-block" style="font-size:18px"></i></p>`;
                } else {
                    // Handle chat agent response
                    chatText = `<p class="clickable" onclick="speakText(this.innerHTML)">${chatAgentResponse}<i class="fa fa-volume-off w3-text-teal display-block" style="font-size:18px"></i></p>`;
                }
				
                // Combine all parts into final text
                finalText = correctedText + chatText + translatedText;
				
                var input_message = {
                    sender: bot_name,
                    text: finalText
                };
				
                // Add the 'selected' attribute to the dropdown menu
                updateSelectedLanguage(translation_language);
				
                // *** Remove any html and then speak *** //
                ////////////////////////////////////////////
                let cleaned_text = removeHtmlTags(text_to_speak);
				
                // Remove any emojis
                cleaned_text = removeEmojis(cleaned_text);
				
                if (speak_status == 'selected') {
                    speak(cleaned_text, speech_lang_code, speech_voice_name, speech_rate);	
                }
				
                // Delete the div containing the spinner
                delete_spinner_div();
				
                // Add the assistant message to the chat
                addMessageToChat(input_message);
				
                // Scroll the page up by clicking on a div at the bottom of the page.
                // ***** Change this to click on the bot message div, then delete the div id ****
                simulateClick('scroll-to-assistant-message');
				
                // Delete the id from the message container.
                // It will get added again when the message container is created.
                var element = document.getElementById("assistant-message");
				
                element.removeAttribute("id");
				
                // Only put the cursor into the input field
                // if the user is not using a cellphone.
                // If the cursor is in the input field on a phone then the keyboard
                // gets displayed. This affects the page scrolling to the bot message.
                var screenWidth = window.screen.width;
                var screenHeight = window.screen.height;
				
                // Assuming a threshold of 768 pixels as a cutoff for mobile devices
                var isMobile = screenWidth <= 768;
                if (isMobile) {
                } else {
                    // Put the cursor in the form input field
                    const inputField = document.getElementById("user-input");
                    inputField.focus();
                }
            }
            } else {
                showToast("AI temporarily unavailable. Please try again.");
            }
        };
        xhr.send(formData);
    };
</script>

<script>
	
// Event listener function
// When the end event is detected, the vent listener
// uses this function to restart the mic.
// In this way the mic always stays on.
// Adding and deleting the event listener is important to
// ensure that the mic stays on, but that it's also off
// when the bot is talking.
function handleEnd () {
  window.recognition.start();
	  
  }
	

function initialize_recognition(lang_code) {
	
	window.SpeechRecognition =
	window.SpeechRecognition || window.webkitSpeechRecognition;
	
	const recognition = new SpeechRecognition();
	
	//recognition.continuous = true;
	
	// *** Comment out this line for better performance on Android. ***
	// When this line is commented out there's no intermediate voice detections,
	// however, the bot works much better on Android.
	//recognition.interimResults = true;
	
	// Set the language you want
	recognition.lang = lang_code; //'ja-JP'; // or 'th-TH' for Thai // en-US
	
	// Make the recognition object available globally
	window.recognition = recognition;
	
	
	// Add event listener
	window.recognition.addEventListener('end', handleEnd);
	
	// Pause (Remove) the event listener
	//window.recognition.removeEventListener('end', handleEnd);
	
	
	window.recognition.start();
	
	
	// Select the button by ID
	const button = document.getElementById("start-voicechat-btn");
	
	// Set the border to yellow
	if (button) {
	    button.style.border = "2px solid orange";
		button.style.borderRadius = "5px";
		button.innerText = "Listening...";
		button.style.letterSpacing = "0.05em";
	}

}




function submit_text_to_php(my_text) {
		// Select the input element by its id
		const inputElement = document.getElementById('user-input');
		
		// Set the value attribute
		inputElement.setAttribute('value', my_text);
		
		// Simulate a click on the form submit button
		// This will send the form to the php code for processing.
		simulateClick('submit-btn');
		
		// Clear the value that was set
		inputElement.setAttribute('value', "");
	}

	
	
	

// Source: Speech Recognition App Using Vanilla JavaScript
// https://www.youtube.com/watch?v=-k-PgvbktX4

function start_recog(submit_text_to_php, lang_code) {
	
	
	initialize_recognition(lang_code);

	const texts = document.querySelector(".texts");
	
	//window.SpeechRecognition =
	  //window.SpeechRecognition || window.webkitSpeechRecognition;
	
	//const recognition = new SpeechRecognition();
	//recognition.interimResults = true;
	
	
	//window.recognition = recognition;
	
	// Create a temporary p element where the voice detection 
	// will be displayed.
	let p = document.createElement("p");
	// Set the id attribute
	p.setAttribute('id', 'temp_p');
	
	
	recognition.addEventListener("result", (e) => {
		
		
	  texts.appendChild(p);
	  
	  let text = Array.from(e.results)
	    .map((result) => result[0])
	    .map((result) => result.transcript)
	    .join("");
	  
	  if (liveFeedbackMode) {
		p.innerHTML = highlightFillersRealtime(text);
	  } else {
		p.innerText = text;
	  }
	  
	  if (e.results[0].isFinal) {
	
	    	// Delete the temporary p element that 
			// showed the voice detection.
			delete_temp_p();
	  
		  // Format the input into paragraphs. This
		  // adds paragrah html to the user's chat.
		  // It's main use is where the bot's long response needs 
		  // to be formatted into separate paragraphs.
		  text = formatResponse(text);
			
		// Use the form to submit the text to php for processing
		submit_text_to_php(text);
		
	  }
	  
	});


	//makeApiRequest(text);
	//window.recognition.start();
}


</script>

<div id="toast-message" class="toast-message"></div>

<?php
// This is important.
// If this is not done then the session variables will still
// be available even after the tab is closed. By doing this the
// session variables get deleted when the tab is closed.
// You can print out the message history to confirm that the
// session variable has been deleted: print_r($_SESSION['message_history']);

// remove all session variables
session_unset();

// destroy the session
session_destroy();
?>

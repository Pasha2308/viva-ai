<?php
session_start();

include "php/name_config.php";
include "php/php_utils_revised.php";



// ERROR LOGGING
// Php errors are logged to a file named: php-errors.log
// This file will be automatically created the first time
// an error occurs.


//-----------
// Settings
//-----------

// Set how fast the text is spoken
$speech_rate = 1;


// English
$bot_language = "English";
$speech_lang_code = "en-UK";

/* 
It's important to choose a voice that can speak
the selected language i.e. that matches the lang code.
This is the JS code that you can run to get the available
voices. Change the language code to suit.

<script>
speechSynthesis.onvoiceschanged = () => {
  const voices = speechSynthesis.getVoices();
  voices
    .filter(v => v.lang === 'en-US')
    .forEach(v => v.name);
};
</script>
*/
$speech_voice_name = "Serena"; 


/*
// Spanish
$bot_language = "Spanish";
$speech_lang_code = "es-ES";
$speech_voice_name = "Jorge";
*/


// If the message history session variable does NOT existt
if (!isset($_SESSION['message_history'])) {
	
	// Create a message_history list
	$_SESSION['message_history'] = array();
	$message_history = $_SESSION['message_history'];
	
	// Randomly set the assistant style for this session.
	$mood_list = array('bubbly', 'contemplative', 'cheerful');
	$length = count($mood_list);
	$limit = $length - 1; 
	$randomNumber = random_int(0, $limit); // the $limit is inclusive
	$mood = $mood_list[$randomNumber];
	
	// Remember: The system message is only set once in the message history.
	$_SESSION['emotion'] = $mood;
	
} else {
	
	// Assign the session variable
	$message_history = $_SESSION['message_history'];
	
	
	// Remember: The system message is only set once in the message history.
	$mood = $_SESSION['emotion'];
}





// This function cleans and secures the user input
function test_input(&$data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = strip_tags($data);
		//$data = htmlentities($data);
		
		return $data;
	}

function generate_question($level, $category) {
	$level = trim((string) $level);
	$category = trim((string) $category);
	if ($level === "") { $level = "intermediate"; }
	if ($category === "") { $category = "personal"; }

	$system_prompt = "You are an IELTS speaking examiner. Generate exactly one natural speaking-test question. Return plain text only.";
	$user_prompt = "Generate one {$level} level {$category} speaking question. Keep it concise and natural.";
	$question = call_groq_api($user_prompt, $system_prompt);

	if ($question === "Error: Unable to fetch response" || trim($question) === "") {
		return "Tell me about a recent experience that was meaningful to you.";
	}

	return trim($question);
}

function adjust_level_by_score($level, $overall_band) {
	$levels = array("beginner", "intermediate", "advanced");
	$current_index = array_search($level, $levels, true);
	if ($current_index === false) {
		$current_index = 1;
	}

	$score = (float) $overall_band;
	if ($score > 7 && $current_index < 2) {
		$current_index += 1;
	}
	if ($score < 5 && $current_index > 0) {
		$current_index -= 1;
	}

	return $levels[$current_index];
}

// Speaking test mode endpoints
if (isset($_REQUEST["action"])) {
	$action = $_REQUEST["action"];

	if ($action === "start_test") {
		$mode = isset($_REQUEST["test_mode"]) ? $_REQUEST["test_mode"] : "speaking";
		$mode = test_input($mode);
		$level = isset($_REQUEST["test_level"]) ? test_input($_REQUEST["test_level"]) : "intermediate";
		$category = isset($_REQUEST["test_category"]) ? test_input($_REQUEST["test_category"]) : "personal";

		$_SESSION["test_mode"] = $mode;
		$_SESSION["test_level"] = $level;
		$_SESSION["test_category"] = $category;
		$_SESSION["test_questions"] = array();
		$_SESSION["test_index"] = 0;
		$_SESSION["test_total_questions"] = 5;
		$first_question = generate_question($level, $category);
		$_SESSION["test_questions"][] = $first_question;

		echo json_encode(array(
			"success" => true,
			"mode" => $mode,
			"question" => $first_question,
			"time_limit" => 60,
			"has_more_questions" => true,
			"question_number" => 1,
			"total_questions" => 5
		));
		exit;
	}

	if ($action === "submit_test_answer") {
		$answer = isset($_REQUEST["my_message"]) ? $_REQUEST["my_message"] : "";
		$answer = test_input($answer);

		$questions = isset($_SESSION["test_questions"]) ? $_SESSION["test_questions"] : array();
		$index = isset($_SESSION["test_index"]) ? (int) $_SESSION["test_index"] : 0;
		$current_question = isset($questions[$index]) ? $questions[$index] : "";

		$answer_context = "Question: " . $current_question . "\nUser Answer: " . $answer;
		$evaluation = evaluate_answer_with_groq($answer_context);

		$index = $index + 1;
		$_SESSION["test_index"] = $index;
		$total_questions = isset($_SESSION["test_total_questions"]) ? (int) $_SESSION["test_total_questions"] : 5;

		$level = isset($_SESSION["test_level"]) ? $_SESSION["test_level"] : "intermediate";
		$level = adjust_level_by_score($level, $evaluation["overall_band"] ?? 0);
		$_SESSION["test_level"] = $level;
		$category = isset($_SESSION["test_category"]) ? $_SESSION["test_category"] : "personal";
		if (isset($_SESSION["test_mode"]) && $_SESSION["test_mode"] === "interview") {
			$category = "interview";
		}

		$has_more = $index < $total_questions;
		$next_question = null;
		if ($has_more) {
			$next_question = generate_question($level, $category);
			$_SESSION["test_questions"][] = $next_question;
		}

		echo json_encode(array(
			"success" => true,
			"mode" => isset($_SESSION["test_mode"]) ? $_SESSION["test_mode"] : "speaking",
			"evaluation" => $evaluation,
			"next_question" => $next_question,
			"time_limit" => 60,
			"has_more_questions" => $has_more,
			"question_number" => min($index + 1, $total_questions),
			"current_answer_number" => $index,
			"total_questions" => $total_questions
		));
		exit;
	}

	if ($action === "get_coaching_tips") {
		$user_answer = isset($_REQUEST["user_answer"]) ? test_input($_REQUEST["user_answer"]) : "";
		$evaluation_raw = isset($_REQUEST["evaluation_json"]) ? $_REQUEST["evaluation_json"] : "{}";
		$evaluation = json_decode($evaluation_raw, true);
		if (!is_array($evaluation)) {
			$evaluation = array();
		}

		$coaching = get_coaching_tips_with_groq($user_answer, $evaluation);
		echo json_encode(array(
			"success" => true,
			"coaching" => $coaching
		));
		exit;
	}
}
	


// This code is triggered when the user submits a message.
// The form data arrives here via Ajax.
if (isset($_REQUEST["my_message"]) && empty($_REQUEST["robotblock"])) {
	
	
	
	// Initialize variables
	$corrected_user_message = "none";
	$translated_response = "none";
	
	
	// Check the status of the radio buttons
	if (isset($_REQUEST["speak1"])) {
		$speak_request = 'selected';
	} else {
		$speak_request = 'not_selected';	
	}
	
	if (isset($_REQUEST["correct1"])) {
		$correction_request = 'selected';
	} else {
		$correction_request = 'not_selected';	
	}
	
	if (isset($_REQUEST["translate1"])) {
		$translation_request = 'selected';
	} else {
		$translation_request = 'not_selected';	
	}
	
	
	
	// Get the user's first language
	$translation_language = $_REQUEST["user_language"];
	
	
	// Get the user's message
	$user_message = $_REQUEST["my_message"];
	
	
	
	// Clean and secure the user's text input
	$user_message = test_input($user_message);
	
	
	// Make a copy of the user message without any corrections.
	// If correction fails, keep and use the raw user message.
	$uncorrected_user_message = $user_message;
	
	
	// Run correction step
	
	

		
// Correction prompt
$correction_system_prompt = <<<EOT
You are a highly skilled {$bot_language} language proofreader. You will be given {$bot_language} text delimited by triple hash tags (###). You task is to correct the spelling, punctuation and grammar errors. Think step by step. Return your corrected text. If the original text does not contain any errors then respond with: ---. 
	Respond in a consistent format. Output a JSON string with the following schema:
{
"correction": <"Your corrected version of the user_message or ---.">
}
	
EOT;
		
		// Remove any html	
		$user_message = strip_tags($user_message);
		
		$text_to_proofread = "###" . $user_message . "###";
		$corrected_user_message_list = run_agent_without_memory($correction_system_prompt, $text_to_proofread);
		
		// Process the response
		if ($corrected_user_message_list[0] != "is_plain_text") {

			// It is json
			$corrected_user_message = $corrected_user_message_list[1]["correction"];
			$corrected_user_message = trim($corrected_user_message);
		} else {

			// It is plain text
			$corrected_user_message = $corrected_user_message_list[1];
			$corrected_user_message = trim($corrected_user_message);
		}
		
		
		// Extract the text from the string
		$corrected_user_message = replaceItemsInString($corrected_user_message);
		
	

	
	
	
	
	// Run response generation step
	
	
	// If correction returns '---', use original user input.
	
	
	
	
	// Sometimes the model outputs two dashes ('--') instead of three dashes ('---')
	if ($corrected_user_message == '---' || $corrected_user_message == '--') {
		$input_message = $uncorrected_user_message;
	} else {
		$input_message = $corrected_user_message;
	}
	
	
	
$assistant_system_prompt = <<<EOT
You are a friendly {$bot_language} language teacher. You always respond in {$bot_language}.
You don't have a name.
Your role is to help users practice {$bot_language} through natural conversation.
The user's words are captured through speech recognition, which may contain mistakes. Be understanding and adapt to possible errors in their speech.
Your replies are converted into speech using SpeechSynthesis, so keep your sentences clear, natural, and easy to pronounce.
You speak with a friendly, casual, and approachable female voice.
At the start of the conversation, always greet the user warmly and introduce yourself as an AI teacher here to help them practice {$bot_language}.
Keep the conversation flowing in a natural, relaxed way — like a friend chatting — not like an assistant offering help.
Make comments, share little thoughts, and react naturally to the user's messages.
Avoid robotic language. Stay human-like and engaging.
Keep your responses concise.
EOT;


	
	$my_message1 = array("text" => $input_message);
	$parts_list = array();
	$parts_list[] = $my_message1;
	$message_history[] = array("role" => "user", "parts" => $parts_list);
	
	$assistant_response_list = run_agent_with_memory($assistant_system_prompt, $message_history);
	// This response is always plain text
	$assistant_response = $assistant_response_list[1];
	
	
	// This text will be spoken out loud
	$text_to_speak = test_input($assistant_response);
	
	// Update the chat history
	$message_dict = array("text" => $assistant_response);
	$parts_list = array();
	$parts_list[] = $message_dict;
	$message_history[] = array("role" => "model", "parts" => $parts_list);
	
	$_SESSION['message_history'] = $message_history;
	
	
	
	
	
	
	
	// Run translation step

	
	if ($translation_request == 'selected' && $user_message != 'api_error' && $user_message != 'Sorry. Something went wrong. Please try again.') {
			
		
// Translation prompt
$translation_system_prompt = <<<EOT
You are a highly skilled {$translation_language} translator. You will be given text. You task is to translate the text into {$translation_language}. Return your translated text.
	Respond in a consistent format. Output a JSON string with the following schema:
{
"translation": "<Your translated version of the text.>"
}
	
EOT;
		
		// Remove any html
		$assistant_response = strip_tags($assistant_response);
		$translated_response_list = run_agent_without_memory($translation_system_prompt, $assistant_response);
		
		
		// Process the response
		if ($translated_response_list[0] != "is_plain_text") {
			// It is json
			$translated_response = $translated_response_list[1]["translation"];
		} else {
			// It is plain text
			$translated_response = $translated_response_list[1];
		}
	
	} else {
		
		$translated_response = 'none';
		
	}
	
	
	
	
	
	//------------------------
	// Create the output text
	//------------------------
	// This is sent to the main 
	// web page via Ajax.
	
	
	// Correction is always calculated.
	// If the user did not ask to display the
	// corrected text then setting $corrected_user_message = 'none'
	// causes the correction to not be displayed on the page.
	if ($correction_request != 'selected') {
		
		$corrected_user_message = 'none';
	}
	
	
	$check_array = array(
		'user_message' => $user_message,
		'corrected_user_message' => $corrected_user_message,
		'input_message' => $input_message,
		'uncorrected_user_message' => $uncorrected_user_message,
		'assistant_response' => $assistant_response, 
		"translated_response" => $translated_response);
	
	
	
	$response = array('success' => true, 
		'check_array' => $check_array,
		'speech_lang_code' => $speech_lang_code,
		'speech_voice_name' => $speech_voice_name,
		'speech_rate' => $speech_rate,
		'check_text' => $user_message,
		'translation_language' => $translation_language, 
		'check_variable' => $mood, 
		'text_to_speak' => $text_to_speak, 
		'speak_status' => $speak_request,
		'chat_text' => $assistant_response, 
		'corrected_text' => $corrected_user_message,
		"translated_text" => $translated_response);
	
  	echo json_encode($response);
	
	
}

?>
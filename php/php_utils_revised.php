<?php

function load_env_file() {
    static $loaded = false;
    static $cache = [];

    if ($loaded) {
        return $cache;
    }

    $loaded = true;
    $envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . ".env";
    if (!file_exists($envPath)) {
        return $cache;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return $cache;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === "" || strpos($line, "#") === 0) {
            continue;
        }

        $eqPos = strpos($line, "=");
        if ($eqPos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $eqPos));
        $value = trim(substr($line, $eqPos + 1));
        if ($key === "") {
            continue;
        }

        // Strip surrounding single or double quotes.
        if (
            strlen($value) >= 2 &&
            (($value[0] === '"' && substr($value, -1) === '"') ||
             ($value[0] === "'" && substr($value, -1) === "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $cache[$key] = $value;
        putenv($key . "=" . $value);
    }

    return $cache;
}

function get_env_value($key) {
    $env = load_env_file();
    if (array_key_exists($key, $env)) {
        return $env[$key];
    }
    return null;
}

/**
 * Call Groq Chat Completions API and return only model text.
 *
 * @param string $user_input The user input
 * @param string|null $system_prompt Optional system prompt
 * @return string
 */
function call_groq_api($user_input, $system_prompt = null) {
    $timestamp = date('Y-m-d H:i:s');
    $file_path = "php-errors.log";
    $apiKey = get_env_value('GROQ_API_KEY');

    if (empty($apiKey)) {
        error_log($timestamp . " GROQ_API_KEY missing or empty\n", 3, $file_path);
        return "Error: Unable to fetch response";
    }

    if ($system_prompt === null) {
        $system_prompt = "You are an English speaking coach.";
    }

    $attempt_plan = [
        ["attempt" => 1, "model" => "llama-3.1-70b-versatile"],
        ["attempt" => 2, "model" => "llama-3.1-70b-versatile"],
        ["attempt" => 3, "model" => "llama-3.1-8b-instant"]
    ];

    foreach ($attempt_plan as $plan) {
        $attempt = $plan["attempt"];
        $model = $plan["model"];
        $payload = [
            "model" => $model,
            "messages" => [
                ["role" => "system", "content" => $system_prompt],
                ["role" => "user", "content" => (string) $user_input]
            ]
        ];

        $jsonPayload = json_encode($payload);
        if ($jsonPayload === false) {
            error_log($timestamp . " JSON encode error: " . json_last_error_msg() . "\n", 3, $file_path);
            return "Error: Unable to fetch response";
        }

        if ($attempt > 1) {
            error_log($timestamp . " Groq retry attempt {$attempt} using model {$model}\n", 3, $file_path);
        }
        $curl = curl_init("https://api.groq.com/openai/v1/chat/completions");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$apiKey}",
            "Content-Type: application/json"
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        $result = curl_exec($curl);
        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlErrNo = curl_errno($curl);
        $curlError = $curlErrNo ? curl_error($curl) : '';
        curl_close($curl);

        if ($curlErrNo) {
            error_log($timestamp . " Groq cURL error with {$model} ({$curlErrNo}): {$curlError}\n", 3, $file_path);
            continue;
        }

        if (in_array($httpStatusCode, [429, 500, 502, 503], true)) {
            error_log($timestamp . " Groq retryable HTTP error {$httpStatusCode} on attempt {$attempt} model {$model}\n", 3, $file_path);
            continue;
        }

        if ($httpStatusCode >= 400 || $result === false) {
            error_log($timestamp . " Groq HTTP error with {$model}: {$httpStatusCode}; response: {$result}\n", 3, $file_path);
            continue;
        }

        $decodedResult = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log($timestamp . " Groq JSON decode error with {$model}: " . json_last_error_msg() . "\n", 3, $file_path);
            continue;
        }

        if (!isset($decodedResult["choices"][0]["message"]["content"])) {
            error_log($timestamp . " Groq response missing choices.message.content with {$model}\n", 3, $file_path);
            continue;
        }

        error_log($timestamp . " Groq model selected: {$model}\n", 3, $file_path);
        return trim((string) $decodedResult["choices"][0]["message"]["content"]);
    }

    return "Error: Unable to fetch response";
}

function evaluation_fallback($message = "Unable to parse evaluator JSON") {
    return [
        "error" => true,
        "error_message" => $message,
        "fluency_score" => 0,
        "grammar_score" => 0,
        "vocabulary_score" => 0,
        "coherence_score" => 0,
        "confidence_score" => 0,
        "hesitation_score" => 0,
        "overall_band" => 0,
        "strengths" => [],
        "weaknesses" => [],
        "improved_answer" => "Error: Unable to fetch response",
        "mistakes" => [],
        "filler_words_detected" => [],
        "improvement_tips" => [],
        "band_explanation" => "",
        "star_answer" => ["situation" => "", "task" => "", "action" => "", "result" => ""],
        "confidence_feedback" => "",
        "recruiter_feedback" => ""
    ];
}

function clamp_band_score($value) {
    return max(0, min(9, (int) $value));
}

function analyze_answer_metrics($answer) {
    $text = strtolower(trim((string) $answer));
    $plain = preg_replace('/[^a-z0-9\s\.\!\?]/i', ' ', $text);
    $tokens = preg_split('/\s+/', trim($plain));
    $tokens = array_values(array_filter($tokens, function($t) { return $t !== ''; }));
    $word_count = count($tokens);

    $sentences = preg_split('/[.!?]+/', $text);
    $sentences = array_values(array_filter(array_map('trim', $sentences), function($s) { return $s !== ''; }));
    $sentence_count = max(1, count($sentences));
    $avg_sentence_length = $word_count > 0 ? ($word_count / $sentence_count) : 0;

    $freq = [];
    foreach ($tokens as $token) {
        if (!isset($freq[$token])) {
            $freq[$token] = 0;
        }
        $freq[$token] += 1;
    }
    $unique_ratio = $word_count > 0 ? (count($freq) / $word_count) : 0;
    $max_repeat = empty($freq) ? 0 : max($freq);

    $grammar_error_count = 0;
    $grammar_patterns = [
        '/\bi\s+is\b/i',
        '/\bhe\s+go\b/i',
        '/\bshe\s+go\b/i',
        '/\bthey\s+is\b/i',
        '/\bwe\s+was\b/i',
        '/\bdoesn\'t\s+likes\b/i',
        '/\bdon\'t\s+likes\b/i',
        '/\ba\s+[aeiou]/i'
    ];
    foreach ($grammar_patterns as $pattern) {
        preg_match_all($pattern, $answer, $m);
        $grammar_error_count += count($m[0]);
    }
    $grammar_error_count += substr_count($answer, '  ');

    return [
        "word_count" => $word_count,
        "avg_sentence_length" => $avg_sentence_length,
        "unique_ratio" => $unique_ratio,
        "max_repeat" => $max_repeat,
        "grammar_error_count" => $grammar_error_count
    ];
}

function calculate_overall_band($scores) {
    $weights = [
        "fluency_score" => 0.2,
        "grammar_score" => 0.25,
        "vocabulary_score" => 0.2,
        "coherence_score" => 0.2,
        "confidence_score" => 0.1,
        "hesitation_score" => 0.05
    ];
    $sum = 0.0;
    foreach ($weights as $k => $w) {
        $sum += clamp_band_score($scores[$k] ?? 0) * $w;
    }
    return round($sum, 1);
}

function decode_evaluation_json($raw) {
    $clean = trim((string) $raw);
    $clean = preg_replace('/```json/i', '', $clean);
    $clean = str_replace('```', '', $clean);
    $clean = trim($clean);

    $firstBrace = strpos($clean, "{");
    $lastBrace = strrpos($clean, "}");
    if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
        $clean = substr($clean, $firstBrace, $lastBrace - $firstBrace + 1);
    }

    $decoded = json_decode($clean, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return $decoded;
    }

    $repaired = preg_replace('/,\s*([\]}])/m', '$1', $clean);
    $decoded = json_decode($repaired, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return $decoded;
    }

    return null;
}

/**
 * Evaluate a speaking/interview answer and return structured JSON as array.
 *
 * @param string $answer
 * @return array
 */
function evaluate_answer_with_groq($answer) {
    $schema_prompt = <<<EOT
You are an IELTS speaking examiner. Evaluate strictly and professionally.
Return STRICT JSON only. Do not include markdown, backticks, or any explanation.
Use this exact schema:
{
  "fluency_score": 1-9,
  "grammar_score": 1-9,
  "vocabulary_score": 1-9,
  "coherence_score": 1-9,
  "confidence_score": 1-9,
  "hesitation_score": 1-9,
  "overall_band": 1-9,
  "strengths": ["string"],
  "weaknesses": ["string"],
  "improved_answer": "string",
  "mistakes": [
    {
      "type": "grammar | vocabulary | fluency",
      "original": "string",
      "corrected": "string",
      "reason": "string"
    }
  ],
  "filler_words_detected": ["um", "uh", "like", "you know"],
  "improvement_tips": ["string"],
  "band_explanation": "string",
  "star_answer": {
    "situation": "string",
    "task": "string",
    "action": "string",
    "result": "string"
  },
  "confidence_feedback": "string",
  "recruiter_feedback": "string"
}
Penalize excessive filler words and hesitations.
EOT;

    $raw = call_groq_api($answer, $schema_prompt);
    if ($raw === "Error: Unable to fetch response") {
        return evaluation_fallback("Groq API request failed");
    }

    $decoded = decode_evaluation_json($raw);
    if (!is_array($decoded)) {
        error_log(date('Y-m-d H:i:s') . " Invalid JSON from model. Raw: " . (string) $raw . "\n", 3, "php-errors.log");
        return evaluation_fallback("Invalid JSON from model");
    }

    $result = [
        "error" => false,
        "error_message" => "",
        "fluency_score" => clamp_band_score($decoded["fluency_score"] ?? 0),
        "grammar_score" => clamp_band_score($decoded["grammar_score"] ?? 0),
        "vocabulary_score" => clamp_band_score($decoded["vocabulary_score"] ?? 0),
        "coherence_score" => clamp_band_score($decoded["coherence_score"] ?? 0),
        "confidence_score" => clamp_band_score($decoded["confidence_score"] ?? 0),
        "hesitation_score" => clamp_band_score($decoded["hesitation_score"] ?? 0),
        "overall_band" => clamp_band_score($decoded["overall_band"] ?? 0),
        "strengths" => is_array($decoded["strengths"] ?? null) ? $decoded["strengths"] : [],
        "weaknesses" => is_array($decoded["weaknesses"] ?? null) ? $decoded["weaknesses"] : [],
        "improved_answer" => (string) ($decoded["improved_answer"] ?? ""),
        "mistakes" => is_array($decoded["mistakes"] ?? null) ? $decoded["mistakes"] : [],
        "filler_words_detected" => is_array($decoded["filler_words_detected"] ?? null) ? $decoded["filler_words_detected"] : [],
        "improvement_tips" => is_array($decoded["improvement_tips"] ?? null) ? $decoded["improvement_tips"] : [],
        "band_explanation" => (string) ($decoded["band_explanation"] ?? ""),
        "star_answer" => is_array($decoded["star_answer"] ?? null) ? $decoded["star_answer"] : ["situation" => "", "task" => "", "action" => "", "result" => ""],
        "confidence_feedback" => (string) ($decoded["confidence_feedback"] ?? ""),
        "recruiter_feedback" => (string) ($decoded["recruiter_feedback"] ?? "")
    ];

    // Apply deterministic realism constraints based on the actual answer.
    $metrics = analyze_answer_metrics($answer);
    if ($metrics["grammar_error_count"] >= 3) {
        $result["grammar_score"] = min($result["grammar_score"], 5);
    }
    if ($metrics["word_count"] < 25 || $metrics["avg_sentence_length"] < 6) {
        $result["fluency_score"] = min($result["fluency_score"], 5);
    }
    if ($metrics["unique_ratio"] < 0.45 || $metrics["max_repeat"] >= 5) {
        $result["vocabulary_score"] = min($result["vocabulary_score"], 6);
    }

    $result["overall_band"] = calculate_overall_band($result);
    return $result;
}

function get_coaching_tips_with_groq($user_answer, $evaluation) {
    $eval_json = json_encode($evaluation);
    if ($eval_json === false) {
        $eval_json = "{}";
    }

    $prompt = <<<EOT
You are a speaking coach. Based on the user answer and evaluation JSON, return STRICT JSON only:
{
  "suggestions": ["tip1", "tip2", "tip3"],
  "practice_question": "one speaking question"
}
No markdown. No extra text.

User answer:
{$user_answer}

Evaluation:
{$eval_json}
EOT;

    $raw = call_groq_api($prompt, "You are an English speaking coach.");
    if ($raw === "Error: Unable to fetch response") {
        return [
            "suggestions" => [
                "Practice speaking in 20-second chunks without stopping.",
                "Use simple but correct sentence patterns.",
                "Replace filler words with short pauses."
            ],
            "practice_question" => "Describe a recent decision you made and why."
        ];
    }

    $parsed = decode_evaluation_json($raw);
    if (!is_array($parsed)) {
        return [
            "suggestions" => [
                "Practice speaking in 20-second chunks without stopping.",
                "Use simple but correct sentence patterns.",
                "Replace filler words with short pauses."
            ],
            "practice_question" => "Describe a recent decision you made and why."
        ];
    }

    return [
        "suggestions" => is_array($parsed["suggestions"] ?? null) ? array_slice($parsed["suggestions"], 0, 3) : [],
        "practice_question" => (string) ($parsed["practice_question"] ?? "")
    ];
}

/**
 * Run agent without memory
 *
 * @param string $system_message The system message
 * @param string $prompt The prompt
 * @return array The output type and text
 */
function run_agent_without_memory($system_message, $prompt) {
    $response_text = call_groq_api($prompt, $system_message);
    return ["is_plain_text", $response_text];
}

/**
 * Run agent with memory
 *
 * @param string $system_message The system message
 * @param array $message_history The message history
 * @return array The output type and text
 */
function run_agent_with_memory($system_message, $message_history) {
    $user_input = "";
    if (is_array($message_history)) {
        for ($i = count($message_history) - 1; $i >= 0; $i--) {
            if (
                isset($message_history[$i]["role"]) &&
                $message_history[$i]["role"] === "user" &&
                isset($message_history[$i]["parts"][0]["text"])
            ) {
                $user_input = (string) $message_history[$i]["parts"][0]["text"];
                break;
            }
        }
    }

    $response_text = call_groq_api($user_input, $system_message);
    return ["is_plain_text", $response_text];
}

// Function to remove items from a JSON string
// before it gets displayed on the page.
function replaceItemsInString($inputString) {
    $itemsToReplace = array("```", "json", "{", "}", '"correction": "', '"translation": "', "#");
    
    $modifiedString = $inputString;
    foreach ($itemsToReplace as $item) {
        $modifiedString = str_replace($item, "", $modifiedString);
    }
    
    $modifiedString = trim($modifiedString);
    
    // Use substr to get the string from the start up to the second last character
    $modifiedString = substr($modifiedString, 0, -1);
    
    $modifiedString = removeEmojis($modifiedString);
    
    return $modifiedString;
}

// Function to remove emojis from text
function removeEmojis($text) {
    $emojiPatterns = array(
        '/[\x{1F600}-\x{1F64F}]/u',  // Emoticons
        '/[\x{1F300}-\x{1F5FF}]/u',  // Miscellaneous Symbols and Pictographs
        '/[\x{1F680}-\x{1F6FF}]/u',  // Transport and Map Symbols
        '/[\x{1F700}-\x{1F77F}]/u',  // Alchemical Symbols
        '/[\x{1F780}-\x{1F7FF}]/u',  // Geometric Shapes Extended
        '/[\x{1F800}-\x{1F8FF}]/u',  // Supplemental Arrows-C
        '/[\x{1F900}-\x{1F9FF}]/u',  // Supplemental Symbols and Pictographs
        '/[\x{1FA00}-\x{1FA6F}]/u',  // Chess Symbols
        '/[\x{1FA70}-\x{1FAFF}]/u',  // Symbols and Pictographs Extended-A
        '/[\x{2600}-\x{26FF}]/u',    // Miscellaneous Symbols
        '/[\x{2700}-\x{27BF}]/u',    // Dingbats
        '/[\x{FE00}-\x{FE0F}]/u',    // Variation Selectors
        '/[\x{1F1E6}-\x{1F1FF}]/u',  // Flags
    );

    foreach ($emojiPatterns as $pattern) {
        $text = preg_replace($pattern, '', $text);
    }

    return $text;
}

?>

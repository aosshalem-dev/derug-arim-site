<?php
/**
 * Prompt Loader Utility
 * Loads prompts from text files and replaces placeholders
 */

/**
 * Load a prompt from file and replace placeholders
 * 
 * @param string $promptName Name of the prompt file (without .txt extension)
 * @param array $replacements Array of placeholder => value replacements
 * @return string The prompt with placeholders replaced
 */
function loadPrompt($promptName, $replacements = []) {
    $promptFile = __DIR__ . '/../prompts/' . $promptName . '.txt';
    
    if (!file_exists($promptFile)) {
        throw new Exception("Prompt file not found: $promptFile");
    }
    
    $prompt = file_get_contents($promptFile);
    
    // Replace placeholders
    foreach ($replacements as $placeholder => $value) {
        $prompt = str_replace('{' . $placeholder . '}', $value, $prompt);
    }
    
    return $prompt;
}

/**
 * Load system message from file
 * 
 * @param string $promptName Name of the prompt (without _system suffix)
 * @return string The system message
 */
function loadSystemMessage($promptName) {
    $systemFile = __DIR__ . '/../prompts/' . $promptName . '_system.txt';
    
    if (!file_exists($systemFile)) {
        // Fallback to default if system file doesn't exist
        return '';
    }
    
    return trim(file_get_contents($systemFile));
}





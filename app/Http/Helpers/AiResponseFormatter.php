<?php

namespace App\Helpers;

use Illuminate\Support\HtmlString;

class AiResponseFormatter
{
    /**
     * Formats AI responses to properly display code blocks and newlines.
     * It detects triple backtick code blocks and wraps them in appropriate HTML,
     * escaping the code content to prevent XSS and ensure proper rendering.
     * Non-code newlines are converted to <br> tags.
     *
     * @param string $text The raw text response from the AI.
     * @return \Illuminate\Support\HtmlString|string The formatted HTML string.
     */
    public static function format(string $text): HtmlString|string
    {
        // Regular expression to find code blocks: ```[language]\ncode\n``` or ```\ncode\n```
        // (?:...) non-capturing group for optional language part
        // ([\s\S]*?) captures the code content (non-greedy, including newlines)
        $codeBlockRegex = '/```(?:\w+)?\n([\s\S]*?)\n```/';

        // Use preg_replace_callback to process each matched code block
        $formattedText = preg_replace_callback($codeBlockRegex, function($matches) {
            // $matches[1] contains the actual code content
            $codeContent = htmlspecialchars($matches[1]); // Escape HTML entities in code to display literally

            // Return the HTML structure for the code block
            // Note: The 'copyCode(this)' JavaScript function needs to be available in your frontend.
            return '
                <div class="code-block-container">
                    <div class="code-block-header">
                        <span>Code</span>
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    </div>
                    <pre>' . $codeContent . '</pre>
                </div>
            ';
        }, $text);

        // Convert any remaining newlines outside of code blocks into <br> tags
        // This ensures regular text maintains its line breaks
        $finalFormattedText = nl2br($formattedText);

        // Return as HtmlString to signal Blade that this is raw HTML and should not be escaped again.
        // If you're not using Illuminate\Support\HtmlString, just return the string.
        return new HtmlString($finalFormattedText);
    }
}
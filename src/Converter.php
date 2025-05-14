<?php

namespace MarkdownToMrkdwn;

class Converter
{
    public function convert(string $markdown): string
    {
        // Bold: **text** → *text*
        $markdown = preg_replace('/\*\*(.*?)\*\*/s', '*$1*', $markdown);

        // Italic: *text* or _text_ → _text_
        $markdown = preg_replace('/(?<!\*)\*(?!\*)(.*?)\*(?!\*)/s', '_$1_', $markdown);
        $markdown = preg_replace('/_(.*?)_/s', '_$1_', $markdown);

        // Inline code: `code`
        $markdown = preg_replace('/`([^`]+)`/', '`$1`', $markdown);

        // Links: [text](url) → <url|text>
        $markdown = preg_replace('/\[(.*?)\]\((.*?)\)/', '<$2|$1>', $markdown);

        // Unordered lists: - item or * item → • item
        $markdown = preg_replace('/^\s*[-*+] (.*)/m', '• $1', $markdown);

        // Ordered lists: 1. item → • item
        $markdown = preg_replace('/^\s*\d+\.\s+(.*)/m', '• $1', $markdown);

        // Blockquotes: > quote
        $markdown = preg_replace('/^> ?(.*)/m', '> $1', $markdown);

        // Escape &, <, >
        $markdown = htmlspecialchars($markdown, ENT_NOQUOTES);

        return $markdown;
    }
}

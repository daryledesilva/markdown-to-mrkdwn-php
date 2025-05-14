<?php

namespace MarkdownToMrkdwn;

class Converter
{
    public function convert(string $markdown): string
    {
        // Italic: *text* or _text_ → _text_
        $markdown = preg_replace('/(?<!\*)\*(?!\*)(.*?)\*(?!\*)/s', '_$1_', $markdown);
        $markdown = preg_replace('/_(.*?)_/s', '_$1_', $markdown);

        // Bold: **text** → *text*
        $markdown = preg_replace('/\*\*(.*?)\*\*/s', '*$1*', $markdown);

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

        // Temporarily replace Slack links and blockquotes with placeholders
        $markdown = preg_replace_callback('/<([^|]+)\|([^>]+)>/', function ($matches) {
            return "__slack_link__" . base64_encode($matches[0]) . "__";
        }, $markdown);

        $markdown = preg_replace('/^> ?(.*)/m', '__blockquote__$1__', $markdown);

        // Escape &, <, >
        $markdown = htmlspecialchars($markdown, ENT_NOQUOTES);

        // Restore Slack links and blockquotes
        $markdown = preg_replace_callback('/__slack_link__(.*?)__/', function ($matches) {
            return base64_decode($matches[1]);
        }, $markdown);

        $markdown = preg_replace('/__blockquote__(.*)__/m', '> $1', $markdown);

        return $markdown;
    }
}

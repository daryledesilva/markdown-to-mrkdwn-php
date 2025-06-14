# markdown-to-mrkdwn-php

[![Packagist Version](https://img.shields.io/packagist/v/daryledesilva/markdown-to-mrkdwn-php.svg?style=flat-square)](https://packagist.org/packages/daryledesilva/markdown-to-mrkdwn-php)
[![License: MIT](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](https://opensource.org/licenses/MIT)

A lightweight, dependency-free PHP library for converting Markdown into Slack-compatible mrkdwn. It preserves code blocks, handles tables, blockquotes, lists, and more for seamless Slack messaging.

## 💡 What is this?

An easy-to-use, dependency-free PHP 7.4+ library that converts Markdown—headings, text formatting, lists, tables, blockquotes, and code blocks—into Slack’s mrkdwn format. Ideal for bots, integrations, or any app sending rich text to Slack.

## ✨ Features

```md
- Headings (H1–H6 → `*Heading*`)
- Text formatting:
  - Bold (`**bold**` → `*bold*`)
  - Italic (`*italic*` or `_italic_` → `_italic_`)
  - Strikethrough (`~~strike~~` → `~strike~`)
- Lists:
  - Unordered & ordered (with nesting)
  - Task lists (`- [ ]` / `- [x]` → `• ☐` / `• ☑`)
- Tables (simple text tables with bold headers)
- Links & images:
  - `[text](url)` → `<url|text>`
  - `![alt](url)` → `<url>`
- Code:
  - Inline `` `code` ``
  - Fenced code blocks (```…```), preserving language hint
- Blockquotes (`> quote`)
- Horizontal rules (`---`, `***`, `___` → `──────────`)
```
## 📋 Supported Conversions

| Markdown                   | Slack mrkdwn             |
|----------------------------|--------------------------|
| `# Heading`                | `*Heading*`              |
| `**Bold**`                 | `*Bold*`                 |
| `*Italic*` / `_Italic_`     | `_Italic_`               |
| `~~Strike~~`               | `~Strike~`               |
| `- [ ] Task`               | `• ☐ Task`               |
| `- [x] Task`               | `• ☑ Task`               |
| `- Item` / `1. Item`       | `• Item` / `1. Item`     |
| `` `Inline code` ``        | `` `Inline code` ``      |
| ```lang                    | ```lang                  |
| code                       | code                     |
| ```                        | ```                      |
| `> Quote`                  | `> Quote`                |
| `---` / `***` / `___`      | `──────────`             |

## 🔌 Plugin System

You can extend the converter with custom plugins (global, line or block scope):

```php
use DaryleDeSilva\MarkdownToMrkdwn\Converter;

$converter = new Converter();

// Global plugin (runs on full text)
$converter->registerPlugin(
    'addQuotes',
    fn(string $text) => "\"{$text}\"",
    priority: 10,
    scope: 'global'
);

// Line plugin (before standard conversion)
$converter->registerPlugin(
    'linePrefix',
    fn(string $line) => "[LINE] {$line}",
    priority: 20,
    scope: 'line',
    timing: 'before'
);

echo $converter->convert("**Hello**, world!");
```

### Advanced plugin examples

```php
// Function plugin: convert entire text to uppercase
$converter->registerPlugin(
    name: 'toUpper',
    converter_func: fn(string $text) => strtoupper($text),
    priority: 10,
    scope: 'line',
    timing: 'after'
);

// Regex plugin: mask email addresses
$converter->registerRegexPlugin(
    name: 'maskEmails',
    pattern: '/[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\\.[a-zA-Z0-9-.]+/',
    replacement: '[EMAIL]',
    priority: 20,
    timing: 'after'
);
```

### Error handling

If an error occurs during conversion, the original Markdown text is returned unmodified.

## 🛠 Requirements

* PHP 7.4 or higher

## 📦 Installation

```bash
composer require daryledesilva/markdown-to-mrkdwn-php
```

## 🚀 Usage

```php
use DaryleDeSilva\MarkdownToMrkdwn\Converter;

$converter = new Converter();
echo $converter->convert("**Hello**, [Slack](https://slack.com)!");
```

## 🧪 Testing

```bash
composer install
composer test
```

## 🔗 Testing in Slack

You can preview the converted mrkdwn in Slack’s Block Kit Builder:  
[https://app.slack.com/block-kit-builder/](https://app.slack.com/block-kit-builder/)

## 📄 License

MIT

---

Created by [@daryledesilva](https://github.com/daryledesilva)
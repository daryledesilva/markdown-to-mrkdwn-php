# markdown-to-mrkdwn-php

Convert standard Markdown to Slack's `mrkdwn` format in PHP.

## 💡 What is this?

A PHP 7.4+ package that transforms common Markdown syntax (bold, italic, links, lists, etc.) into Slack's `mrkdwn` format. Useful when rendering Markdown in Slack messages, bots, or webhooks.

## ✨ Features

* Converts `**bold**` to `*bold*`
* Converts `_italic_` or `*italic*` to `_italic_`
* Converts `[text](url)` to `<url|text>`
* Supports inline code and preserves code blocks (with language fences)
* Converts headings (`# Heading` … `###### Heading`) to `*Heading*`
* Converts `~~strikethrough~~` to `~strikethrough~`
* Converts task lists (`- [ ]` / `- [x]`) to `• ☐` / `• ☑`
* Handles unordered & ordered lists (including nested lists)
* Converts horizontal rules (`---`, `***`, `___`) to `──────────`
* Converts tables into Slack-style tables (bolds headers)
* Handles blockquotes (`> quote`)
* Escapes Slack-reserved characters like `&`, `<`, and `>`

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

## 📄 License

MIT

---

Created by [@daryledesilva](https://github.com/daryledesilva)
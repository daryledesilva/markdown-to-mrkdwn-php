# markdown-to-mrkdwn-php

Convert standard Markdown to Slack's `mrkdwn` format in PHP.

## 💡 What is this?

A PHP 7.4+ package that transforms common Markdown syntax (bold, italic, links, lists, etc.) into Slack's `mrkdwn` format. Useful when rendering Markdown in Slack messages, bots, or webhooks.

## ✨ Features

* Converts `**bold**` to `*bold*`
* Converts `_italic_` or `*italic*` to `_italic_`
* Converts `[text](url)` to `<url|text>`
* Supports inline code and code blocks
* Handles blockquotes and lists
* Escapes Slack-reserved characters like `&`, `<`, and `>`

## 🛠 Requirements

* PHP 7.4 or higher

## 📦 Installation

```bash
composer require daryledesilva/markdown-to-mrkdwn-php
```

## 🚀 Usage

```php
use MarkdownToMrkdwn\Converter;

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
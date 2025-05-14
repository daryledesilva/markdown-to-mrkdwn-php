<?php

use PHPUnit\Framework\TestCase;
use MarkdownToMrkdwn\Converter;

class ConverterTest extends TestCase
{
    public function testBold()
    {
        $converter = new Converter();
        $this->assertEquals('*bold*', $converter->convert('**bold**'));
    }

    public function testItalic()
    {
        $converter = new Converter();
        $this->assertEquals('_italic_', $converter->convert('*italic*'));
        $this->assertEquals('_italic_', $converter->convert('_italic_'));
    }

    public function testInlineCode()
    {
        $converter = new Converter();
        $this->assertEquals('`code`', $converter->convert('`code`'));
    }

    public function testLink()
    {
        $converter = new Converter();
        $this->assertEquals('<https://example.com|Example>', $converter->convert('[Example](https://example.com)'));
    }

    public function testUnorderedList()
    {
        $converter = new Converter();
        $this->assertEquals("• Item 1\n• Item 2", $converter->convert("- Item 1\n- Item 2"));
    }

    public function testOrderedList()
    {
        $converter = new Converter();
        $this->assertEquals("• First\n• Second", $converter->convert("1. First\n2. Second"));
    }

    public function testBlockquote()
    {
        $converter = new Converter();
        $this->assertEquals("> Quoted text", $converter->convert("> Quoted text"));
    }

    public function testEscapeCharacters()
    {
        $converter = new Converter();
        $this->assertEquals('Tom &amp; Jerry &lt;3', $converter->convert('Tom & Jerry <3'));
    }
}

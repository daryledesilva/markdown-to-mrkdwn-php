<?php

use PHPUnit\Framework\TestCase;
use DaryleDeSilva\MarkdownToMrkdwn\Converter;

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
        $this->assertEquals("1. First\n2. Second", $converter->convert("1. First\n2. Second"));
    }

    public function testBlockquote()
    {
        $converter = new Converter();
        $this->assertEquals("> Quoted text", $converter->convert("> Quoted text"));
    }

    public function testEscapeCharacters()
    {
        $converter = new Converter();
        $this->assertEquals('Tom & Jerry <3', $converter->convert('Tom & Jerry <3'));
    }

    public function testMarkdownToMrkdwnWithMixedFormatting()
    {
        $converter = new Converter();

        // Input with both italic and bold text
        $input = "This booking was canceled using the *Quick Cancel* button by **Charlotte Sun**.";

        // Expected output after transformations
        $expected = "This booking was canceled using the _Quick Cancel_ button by *Charlotte Sun*.";

        // Apply the conversion function
        $output = $converter->convert($input);

        // Assert that the output matches the expected result
        $this->assertEquals($expected, $output);
    }

    public function testMarkdownToMrkdwnWithBoldItalicAndBlockquote()
    {
        $converter = new Converter();

        // Input with bold, italic, and blockquote
        $input = "The given booking was rejected by **Charlotte Sun**.\n> *No availability - informed partner*";

        // Expected output after transformations
        $expected = "The given booking was rejected by *Charlotte Sun*.\n> _No availability - informed partner_";

        // Apply the conversion function
        $output = $converter->convert($input);

        // Assert that the output matches the expected result
        $this->assertEquals($expected, $output);
    }

    // Tests added to ensure feature parity with the upstream Python package
    public function testHeadings()
    {
        $converter = new Converter();
        $this->assertEquals('*Header 1*', $converter->convert('# Header 1'));
        $this->assertEquals('*Header 2*', $converter->convert('## Header 2'));
        $this->assertEquals('*Header 3*', $converter->convert('### Header 3'));
        $this->assertEquals('*Header 4*', $converter->convert('#### Header 4'));
        $this->assertEquals('*Header 5*', $converter->convert('##### Header 5'));
        $this->assertEquals('*Header 6*', $converter->convert('###### Header 6'));
    }

    public function testStrikethrough()
    {
        $converter = new Converter();
        $this->assertEquals('This is ~strike~ text', $converter->convert('This is ~~strike~~ text'));
    }

    public function testImages()
    {
        $converter = new Converter();
        $this->assertEquals(
            '<http://example.com/img.png>',
            $converter->convert('![alt](http://example.com/img.png)')
        );
    }

    public function testHorizontalRule()
    {
        $converter = new Converter();
        $this->assertEquals('──────────', $converter->convert('---'));
        $this->assertEquals('──────────', $converter->convert('***'));
        $this->assertEquals('──────────', $converter->convert('___'));
    }

    public function testTaskLists()
    {
        $converter = new Converter();
        $input = "- [ ] Task 1\n- [x] Task 2\n- [X] Task 3";
        $expected = "• ☐ Task 1\n• ☑ Task 2\n• ☑ Task 3";
        $this->assertEquals($expected, $converter->convert($input));
    }

    public function testTables()
    {
        $converter = new Converter();
        $markdown = "| H1 | H2 |\n| -- | -- |\n| a | b |\n| c | d |";
        $expected = "*H1* | *H2*\na | b\nc | d";
        $this->assertEquals($expected, $converter->convert($markdown));
    }

    public function testMixedNestedLists()
    {
        $converter = new Converter();
        $input = "- Item 1\n  - Subitem 1.1\n  - Subitem 1.2\n1. Ordered 1\n2. Ordered 2";
        $expected = "• Item 1\n  • Subitem 1.1\n  • Subitem 1.2\n1. Ordered 1\n2. Ordered 2";
        $this->assertEquals($expected, $converter->convert($input));
    }

    public function testCodeBlocks()
    {
        $converter = new Converter();
        $markdown = "```php\n<?php echo 'test';\n```";
        $this->assertEquals($markdown, $converter->convert($markdown));
    }

    public function testCodeBlockWithLanguageAndText()
    {
        $converter = new Converter();
        $markdown = "```cron\n# job\n```";
        $this->assertEquals($markdown, $converter->convert($markdown));
        $mixed = "```cron\n# job\n```\nAfter";
        $expected = "```cron\n# job\n```\nAfter";
        $this->assertEquals($expected, $converter->convert($mixed));
    }

    public function testPluginTimingBeforeAndAfter()
    {
        $converter = new Converter();
        $converter->registerPlugin('before', fn($l) => "[B]$l", 10, 'line', 'before');
        $converter->registerPlugin('after', fn($l) => "{$l}[A]", 10, 'line', 'after');
        $this->assertEquals('[B]*bold*[A]', $converter->convert('**bold**'));
    }

    /** @test Block-scope plugin runs once on entire text. */
    public function testBlockScopePlugin()
    {
        $converter = new Converter();
        $converter->registerPlugin('wrap', fn(string $text) => "START\n{$text}\nEND", 10, 'block');
        $input = "One\nTwo";
        $expected = "START\nOne\nTwo\nEND";
        $this->assertEquals($expected, $converter->convert($input));
    }
}

<?php

namespace DaryleDeSilva\MarkdownToMrkdwn;

class Converter
{
    private string $encoding;
    private bool $inCodeBlock = false;
    private bool $inIndentBlock = false;
    private array $tableReplacements = [];
    private array $patterns = [];
    private array $plugins = [];
    private array $pluginOrder = [];
    private string $tripleStart;
    private string $tripleEnd;

    public function __construct(string $encoding = 'UTF-8')
    {
        $this->encoding = $encoding;
        $this->tripleStart = '%%BOLDITALIC_START%%';
        $this->tripleEnd = '%%BOLDITALIC_END%%';
        $this->patterns = [
            // Task lists
            '/^(\s*)- \[([ ])\] (.+)/m'    => '$1• ☐ $3',
            '/^(\s*)- \[([xX])\] (.+)/m'  => '$1• ☑ $3',
            // Unordered lists
            '/^(\s*)- (.+)/m'             => '$1• $2',
            // Ordered lists
            '/^(\s*)(\d+)\. (.+)/m'       => '$1$2. $3',
            // Images
            '/!\[.*?\]\((.+?)\)/m'        => '<$1>',
            // Italic
            '/(?<!\*)\*([^*\n]+?)\*(?!\*)/m' => '_$1_',
            // Headings (1 to 6 hashes)
            '/^(#{1,6})\s*(.+)$/m'         => '*$2*',
            // Bold with surrounding spaces
            '/(^|\s)~\*\*(.+?)\*\*(\s|$)/m' => '$1 *$2* $3',
            // Bold
            '/(?<!\*)\*\*(.+?)\*\*(?!\*)/m' => '*$1*',
            // Underline as bold
            '/__(.+?)__/m'                => '*$1*',
            // Links
            '/\[(.+?)\]\((.+?)\)/m'       => '<$2|$1>',
            // Inline code
            '/`(.+?)`/m'                  => '`$1`',
            // Blockquote
            '/^> (.+)/m'                  => '> $1',
            // Horizontal rule
            '/^(---|\*\*\*|___)$/m'       => '──────────',
            // Strikethrough
            '/~~(.+?)~~/m'                => '~$1~',
        ];
    }

    /**
     * Register a custom conversion plugin.
     *
     * @param string   $name
     * @param callable $converterFunc function(string): string
     * @param int      $priority      lower numbers run first
     * @param string   $scope         'global', 'line' or 'block'
     * @param string   $timing        'before' or 'after' (only for 'line' scope)
     */
    public function registerPlugin(string $name, callable $converterFunc, int $priority = 50, string $scope = 'line', string $timing = 'after'): void
    {
        if (!in_array($scope, ['global', 'line', 'block'], true)) {
            throw new \InvalidArgumentException("Plugin scope must be 'global', 'line', or 'block'");
        }
        if ($scope === 'line' && !in_array($timing, ['before', 'after'], true)) {
            throw new \InvalidArgumentException("Plugin timing must be 'before' or 'after' for line scope");
        }
        $this->plugins[$name] = [
            'func'     => $converterFunc,
            'priority' => $priority,
            'scope'    => $scope,
            'timing'   => $scope === 'line' ? $timing : null,
        ];
        $this->updatePluginOrder();
    }

    /** Remove a registered plugin; returns true if removed. */
    public function removePlugin(string $name): bool
    {
        if (isset($this->plugins[$name])) {
            unset($this->plugins[$name]);
            $this->updatePluginOrder();
            return true;
        }
        return false;
    }

    /** Get metadata for registered plugins. */
    public function getRegisteredPlugins(): array
    {
        $out = [];
        foreach ($this->plugins as $name => $info) {
            $out[$name] = [
                'priority' => $info['priority'],
                'scope'    => $info['scope'],
                'timing'   => $info['timing'] ?? null,
            ];
        }
        return $out;
    }

    /**
     * Register a simple regex-based line plugin.
     *
     * @param string $name
     * @param string $pattern     PCRE pattern with delimiters
     * @param string $replacement replacement string
     * @param int    $priority
     * @param string $timing      'before' or 'after'
     */
    public function registerRegexPlugin(string $name, string $pattern, string $replacement, int $priority = 50, string $timing = 'after'): void
    {
        $fn = function (string $line) use ($pattern, $replacement): string {
            return preg_replace($pattern, $replacement, $line);
        };
        $this->registerPlugin($name, $fn, $priority, 'line', $timing);
    }

    /** Convert Markdown text to Slack mrkdwn. */
    public function convert(string $markdown): string
    {
        if ($markdown === '') {
            return '';
        }
        try {
            $markdown = trim($markdown);

            $this->tableReplacements = [];
            $markdown = $this->convertTables($markdown);

            // Global plugins
            foreach ($this->pluginOrder as $name) {
                $plugin = $this->plugins[$name];
                if ($plugin['scope'] === 'global') {
                    $markdown = ($plugin['func'])($markdown);
                }
            }

            $lines = explode("\n", $markdown);
            $before = [];
            $after  = [];
            foreach ($this->pluginOrder as $name) {
                $plugin = $this->plugins[$name];
                if ($plugin['scope'] === 'line') {
                    if ($plugin['timing'] === 'before') {
                        $before[] = $plugin['func'];
                    } else {
                        $after[] = $plugin['func'];
                    }
                }
            }

            $outLines = [];
            foreach ($lines as $line) {
                if (
                    substr($line, 0, strlen('%%TABLE_PLACEHOLDER_')) === '%%TABLE_PLACEHOLDER_'
                    && substr($line, -2) === '%%'
                ) {
                    $outLines[] = $line;
                    continue;
                }
                foreach ($before as $fn) {
                    $line = $fn($line);
                }
                $line = $this->convertLine($line);
                foreach ($after as $fn) {
                    $line = $fn($line);
                }
                $outLines[] = $line;
            }
            $result = implode("\n", $outLines);

            // Restore tables
            foreach ($this->tableReplacements as $ph => $table) {
                $result = str_replace($ph, $table, $result);
            }

            // Block plugins
            foreach ($this->pluginOrder as $name) {
                $plugin = $this->plugins[$name];
                if ($plugin['scope'] === 'block') {
                    $result = ($plugin['func'])($result);
                }
            }

            return $result;
        } catch (\Throwable $e) {
            return $markdown;
        }
    }

    /** Compile and replace Markdown tables with placeholders. */
    private function convertTables(string $markdown): string
    {
        // Skip table processing if no pipe character
        if (strpos($markdown, '|') === false) {
            return $markdown;
        }
        $pattern = '/^\|(.+)\|\s*$\n^\|[-:| ]+\|\s*$\n(?:^\|.*\|\s*$\n?)*/m';
        return preg_replace_callback($pattern, function (array $m): string {
            $table = $m[0];
            $lines = explode("\n", trim($table));
            $headers = array_map('trim', explode('|', trim($lines[0], '|')));
            $rows = [];
            for ($i = 2, $len = count($lines); $i < $len; $i++) {
                $rows[] = array_map('trim', explode('|', trim($lines[$i], '|')));
            }
            $out = [];
            $out[] = implode(' | ', array_map(fn($h) => "*{$h}*", $headers));
            foreach ($rows as $row) {
                $out[] = implode(' | ', $row);
            }
            $ph = '%%TABLE_PLACEHOLDER_' . md5($table) . '%%';
            $this->tableReplacements[$ph] = implode("\n", $out);
            return $ph;
        }, $markdown);
    }

    /** Convert a single line of Markdown. */
    private function convertLine(string $line): string
    {
        // Code fence start/end
        if (preg_match('/^```(\w*)$/', $line, $m)) {
            $this->inCodeBlock = !$this->inCodeBlock;
            return $this->inCodeBlock && $m[1] !== '' ? "```{$m[1]}" : '```';
        }

        if ($this->inCodeBlock) {
            return $line;
        }

        // Indented code block (4 spaces or a tab)
        if (!$this->inIndentBlock && preg_match('/^(?: {4}|\t)/', $line)) {
            $this->inIndentBlock = true;
            return $line;
        }
        if ($this->inIndentBlock && preg_match('/^(?: {4}|\t)/', $line)) {
            return $line;
        }
        if ($this->inIndentBlock) {
            // Exit from indented code block
            $this->inIndentBlock = false;
        }
        $line = preg_replace_callback(
            '/(?<!\*)\*\*\*([^*\n]+?)\*\*\*(?!\*)/',
            fn(array $m) => $this->tripleStart . $m[1] . $this->tripleEnd,
            $line
        );
        foreach ($this->patterns as $pattern => $replacement) {
            $line = preg_replace($pattern, $replacement, $line);
        }
        $es = preg_quote($this->tripleStart, '/');
        $ee = preg_quote($this->tripleEnd, '/');
        $line = preg_replace("/{$es}(.*?){$ee}/m", '*_$1_*', $line);
        return rtrim($line);
    }

    /** Update internal plugin execution order by priority. */
    private function updatePluginOrder(): void
    {
        $names = array_keys($this->plugins);
        usort($names, fn($a, $b) => $this->plugins[$a]['priority'] <=> $this->plugins[$b]['priority']);
        $this->pluginOrder = $names;
    }
}

<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\Printer\Markdown;

use Nette\Utils\Strings;
use SebastianBergmann\Diff\Differ;

final readonly class MarkdownDiffer
{
    /**
     * @var string
     * @see https://regex101.com/r/yf7u2L/1
     */
    private const SPACE_AND_NEWLINE_REGEX = '#( ){1,}\\n#';

    public function __construct(
        private Differ $differ
    ) {
    }

    public function diff(string $old, string $new): string
    {
        if ($old === $new) {
            return '';
        }

        $diff = $this->differ->diff($old, $new);
        $diff = $this->removeTrailingWhitespaces($diff);

        return $this->warpToDiffCode($diff);
    }

    /**
     * Removes generated pre-spaces " \n" => "\n"
     */
    private function removeTrailingWhitespaces(string $diff): string
    {
        $diff = Strings::replace($diff, self::SPACE_AND_NEWLINE_REGEX, \PHP_EOL);
        return \rtrim($diff);
    }

    private function warpToDiffCode(string $content): string
    {
        return '```diff' . \PHP_EOL . $content . \PHP_EOL . '```' . \PHP_EOL;
    }
}

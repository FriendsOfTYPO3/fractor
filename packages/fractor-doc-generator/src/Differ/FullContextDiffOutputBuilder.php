<?php

declare(strict_types=1);

namespace a9f\FractorDocGenerator\Differ;

use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOutputBuilderInterface;

/**
 * Renders a diff with full context and without any unified diff metadata.
 *
 * This replaces the previous use of UnifiedDiffOutputBuilder, which sebastian/diff
 * removed in 9.0. StrictUnifiedDiffOutputBuilder cannot take its place here: the
 * options that suppress the file header, the hunk range and the end-of-file marker
 * were only introduced in 9.0, while this package still supports sebastian/diff 4.
 * DiffOutputBuilderInterface is identical across all supported majors.
 */
final class FullContextDiffOutputBuilder implements DiffOutputBuilderInterface
{
    /**
     * @param list<array{0: mixed, 1: int}> $diff
     */
    public function getDiff(array $diff): string
    {
        if ($diff === [] || ! $this->hasChanges($diff)) {
            return '';
        }

        $output = '';

        foreach ($this->insertEndOfFileMarkers($diff) as $entry) {
            $line = (string) $entry[0];

            $output .= match ($entry[1]) {
                Differ::ADDED => '+' . $line,
                Differ::REMOVED => '-' . $line,
                Differ::NO_LINE_END_EOF_WARNING => "\n",
                default => ' ' . $line,
            };
        }

        $lastCharacter = substr($output, -1);
        if ($lastCharacter !== "\n" && $lastCharacter !== "\r") {
            $output .= "\n";
        }

        return $output;
    }

    /**
     * Only checks for ADDED/REMOVED entries, not the type-3 (NO_LINE_END_EOF_WARNING /
     * "line ending differs") entry. A type-3 entry cannot appear on its own: it is only
     * emitted by Differ::detectUnmatchedLineEndings() when the two inputs' line-ending
     * sets differ, and MarkdownDiffer::diff() already early-returns when $old === $new.
     * So for a genuinely different pair of inputs whose only difference is line endings,
     * there is always at least one ADDED/REMOVED entry alongside it; returning '' here
     * for a diff that contains only a type-3 entry is not reachable in practice.
     *
     * @param list<array{0: mixed, 1: int}> $diff
     */
    private function hasChanges(array $diff): bool
    {
        foreach ($diff as $entry) {
            if ($entry[1] === Differ::ADDED || $entry[1] === Differ::REMOVED) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mirrors the marker placement of the removed UnifiedDiffOutputBuilder: after the
     * final entry when that entry is unchanged, otherwise after the last added and the
     * last removed entry. The marker itself renders as a bare newline, so only its
     * position matters.
     *
     * @param list<array{0: mixed, 1: int}> $diff
     * @return list<array{0: mixed, 1: int}>
     */
    private function insertEndOfFileMarkers(array $diff): array
    {
        $marker = ["\n\\ No newline at end of file\n", Differ::NO_LINE_END_EOF_WARNING];
        $lastIndex = count($diff) - 1;

        if ($diff[$lastIndex][1] === Differ::OLD) {
            if (! $this->endsWithNewline($diff[$lastIndex][0])) {
                array_splice($diff, $lastIndex + 1, 0, [$marker]);
            }

            // array_splice() already preserves list-ness at runtime; array_values() is only
            // here because PHPStan 2.0.1 (this project's lowest resolvable version) cannot
            // prove that statically, while 2.2.13 (the highest) can.
            return array_values($diff);
        }

        $pending = [
            Differ::ADDED => true,
            Differ::REMOVED => true,
        ];

        for ($index = $lastIndex; $index >= 0; $index--) {
            if (! isset($pending[$diff[$index][1]])) {
                continue;
            }

            unset($pending[$diff[$index][1]]);

            if (! $this->endsWithNewline($diff[$index][0])) {
                array_splice($diff, $index + 1, 0, [$marker]);
            }

            if ($pending === []) {
                break;
            }
        }

        // array_splice() already preserves list-ness at runtime; array_values() is only
        // here because PHPStan 2.0.1 (this project's lowest resolvable version) cannot
        // prove that statically, while 2.2.13 (the highest) can.
        return array_values($diff);
    }

    private function endsWithNewline(mixed $line): bool
    {
        return str_ends_with((string) $line, "\n");
    }
}

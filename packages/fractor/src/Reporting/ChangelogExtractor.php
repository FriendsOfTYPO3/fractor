<?php

declare(strict_types=1);

namespace a9f\Fractor\Reporting;

use a9f\Fractor\Application\Contract\FractorRule;
use a9f\Fractor\Application\ValueObject\AppliedRule;
use Nette\Utils\Strings;
use Webmozart\Assert\Assert;

final class ChangelogExtractor
{
    /**
     * @param class-string<FractorRule>|AppliedRule::CODE_FORMAT_RULE $ruleClassName
     */
    public function extractChangelogFromRule(string $ruleClassName): ?string
    {
        // the virtual code-formatting rule has no backing class to reflect on
        if ($ruleClassName === AppliedRule::CODE_FORMAT_RULE) {
            return null;
        }

        Assert::isAOf($ruleClassName, FractorRule::class);

        $reflectionClass = new \ReflectionClass($ruleClassName);

        $docComment = $reflectionClass->getDocComment();
        if (! is_string($docComment)) {
            return null;
        }

        // uses '\r?\n' instead of '$' because windows compat
        $pattern = '#' . preg_quote('changelog', '#') . '\s+(?<content>.*?)\r?\n#m';
        $matches = Strings::match($docComment, $pattern);

        return $matches['content'] ?? null;
    }
}

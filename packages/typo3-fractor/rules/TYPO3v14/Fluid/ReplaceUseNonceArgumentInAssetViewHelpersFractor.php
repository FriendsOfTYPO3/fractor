<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v14\Fluid;

use a9f\FractorFluid\Contract\FluidFractorRule;
use Nette\Utils\Strings;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/14.2/Deprecation-100887-UseNonceArgumentInFAssetViewHelpers.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v14\Fluid\ReplaceUseNonceArgumentInAssetViewHelpersFractor\ReplaceUseNonceArgumentInAssetViewHelpersFractorTest
 */
final class ReplaceUseNonceArgumentInAssetViewHelpersFractor implements FluidFractorRule
{
    private const TAG_PATTERN = '#<f:asset\.(?:script|css)\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>#i';

    private const INLINE_PATTERN = '#f:asset\.(?:script|css)(?<args>\((?:[^()"\']++|"[^"]*"|\'[^\']*\'|(?&args))*\))#i';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace the useNonce argument of f:asset.script and f:asset.css with csp',
            [new CodeSample(
                <<<'CODE_SAMPLE'
<f:asset.script identifier="my-script" src="EXT:my_ext/Resources/Public/JavaScript/foo.js" useNonce="1" />
<f:asset.css identifier="my-style" href="EXT:my_ext/Resources/Public/Css/foo.css" useNonce="1" />
{f:asset.script(identifier: 'my-script', src: 'EXT:my_ext/Resources/Public/JavaScript/foo.js', useNonce: 1)}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
<f:asset.script identifier="my-script" src="EXT:my_ext/Resources/Public/JavaScript/foo.js" csp="1" />
<f:asset.css identifier="my-style" href="EXT:my_ext/Resources/Public/Css/foo.css" csp="1" />
{f:asset.script(identifier: 'my-script', src: 'EXT:my_ext/Resources/Public/JavaScript/foo.js', csp: 1)}
CODE_SAMPLE
            )]
        );
    }

    public function refactor(string $fluid): string
    {
        $fluid = Strings::replace(
            $fluid,
            self::TAG_PATTERN,
            static fn (array $match): string => self::migrateTag($match[0])
        );

        return Strings::replace(
            $fluid,
            self::INLINE_PATTERN,
            static fn (array $match): string => self::migrateInline($match[0])
        );
    }

    private static function migrateTag(string $tag): string
    {
        if (preg_match('#\scsp\s*=#', $tag) === 1) {
            return Strings::replace($tag, '#\s+useNonce\s*=\s*(?:"[^"]*"|\'[^\']*\')#', '');
        }

        return Strings::replace($tag, '#(\s)useNonce(\s*=)#', '$1csp$2');
    }

    private static function migrateInline(string $call): string
    {
        return Strings::replace($call, '#([(,\s])([\'"]?)useNonce\2(\s*:)#', '$1$2csp$2$3');
    }
}

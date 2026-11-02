<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v14\Yaml;

use a9f\FractorYaml\Contract\YamlFractorRule;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/14.2/Deprecation-107068-RenameFieldExplanationTextToDescription.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v14\Yaml\ReplaceFieldExplanationTextWithDescriptionFractor\ReplaceFieldExplanationTextWithDescriptionFractorTest
 */
final class ReplaceFieldExplanationTextWithDescriptionFractor implements YamlFractorRule
{
    private const FORM_EDITOR = 'formEditor';

    private const OLD_KEY = 'fieldExplanationText';

    private const NEW_KEY = 'description';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace fieldExplanationText with description in the form editor configuration', [
            new CodeSample(
                <<<'CODE_SAMPLE'
prototypes:
  standard:
    formElementsDefinition:
      MyElement:
        formEditor:
          editors:
            200:
              identifier: placeholder
              label: Placeholder
              fieldExplanationText: 'Enter the placeholder text'
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
prototypes:
  standard:
    formElementsDefinition:
      MyElement:
        formEditor:
          editors:
            200:
              identifier: placeholder
              label: Placeholder
              description: 'Enter the placeholder text'
CODE_SAMPLE
            ),
        ]);
    }

    public function refactor(array $yaml): array
    {
        return $this->traverse($yaml, false);
    }

    /**
     * @param mixed[] $yaml
     * @return mixed[]
     */
    private function traverse(array $yaml, bool $isInsideFormEditor): array
    {
        if ($isInsideFormEditor && array_key_exists(self::OLD_KEY, $yaml)) {
            $yaml = $this->replaceKey($yaml);
        }

        foreach ($yaml as $key => $value) {
            if (is_array($value)) {
                $yaml[$key] = $this->traverse($value, $isInsideFormEditor || $key === self::FORM_EDITOR);
            }
        }

        return $yaml;
    }

    /**
     * @param mixed[] $yaml
     * @return mixed[]
     */
    private function replaceKey(array $yaml): array
    {
        $hasNewKey = array_key_exists(self::NEW_KEY, $yaml);
        $result = [];
        foreach ($yaml as $key => $value) {
            if ($key !== self::OLD_KEY) {
                $result[$key] = $value;
                continue;
            }

            if (! $hasNewKey) {
                $result[self::NEW_KEY] = $value;
            }
        }

        return $result;
    }
}

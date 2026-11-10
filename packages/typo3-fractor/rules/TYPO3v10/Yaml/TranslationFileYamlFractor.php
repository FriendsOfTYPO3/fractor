<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v10\Yaml;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorYaml\Contract\YamlFractorRule;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/10.0/Breaking-87009-UseMultipleTranslationFilesByDefaultInEXTform.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v10\Yaml\TranslationFileYamlFractor\TranslationFileYamlFractorTest
 */
final class TranslationFileYamlFractor implements YamlFractorRule
{
    /**
     * @var string
     */
    private const TRANSLATION_FILE_KEY = 'translationFile';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Use key translationFiles instead of translationFile', [
            new CodeSample(
                <<<'CODE_SAMPLE'
TYPO3:
  CMS:
    Form:
      prototypes:
        standard:
          formElementsDefinition:
            Form:
              renderingOptions:
                translation:
                  translationFile:
                    10: 'EXT:form/Resources/Private/Language/locallang.xlf'
                    20: 'EXT:myextension/Resources/Private/Language/locallang.xlf'
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
TYPO3:
  CMS:
    Form:
      prototypes:
        standard:
          formElementsDefinition:
            Form:
              renderingOptions:
                translation:
                  translationFiles:
                    20: 'EXT:myextension/Resources/Private/Language/locallang.xlf'
CODE_SAMPLE
            ),
        ]);
    }

    public function refactor(array $yaml): array
    {
        return $this->refactorTranslationFile($yaml);
    }

    /**
     * @param mixed[] $yaml
     * @return mixed[]
     * @see https://github.com/TYPO3/typo3/blob/10.4/typo3/sysext/form/Classes/Controller/FormEditorController.php#L653-L689
     */
    private function refactorTranslationFile(array &$yaml): array
    {
        foreach ($yaml as &$section) {
            if (! is_array($section)) {
                continue;
            }

            if (array_key_exists(self::TRANSLATION_FILE_KEY, $section)
                && is_array($section[self::TRANSLATION_FILE_KEY])
            ) {
                $section['translationFiles'] = $this->buildNewTranslations($section[self::TRANSLATION_FILE_KEY]);
                unset($section[self::TRANSLATION_FILE_KEY]);
            }

            $this->refactorTranslationFile($section);
        }

        unset($section);

        return $yaml;
    }

    /**
     * @param array<int, string> $oldTranslations
     *
     * @return array<int, string>
     */
    private function buildNewTranslations(array $oldTranslations): array
    {
        return array_filter(
            $oldTranslations,
            static fn (string $oldTranslationFile): bool => ! \str_starts_with($oldTranslationFile, 'EXT:form')
        );
    }
}

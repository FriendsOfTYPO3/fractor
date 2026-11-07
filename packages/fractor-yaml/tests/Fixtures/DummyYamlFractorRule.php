<?php

declare(strict_types=1);

namespace a9f\FractorYaml\Tests\Fixtures;

use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorYaml\Contract\YamlFractorRule;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;

final class DummyYamlFractorRule implements YamlFractorRule
{
    /**
     * @var string
     */
    private const TRANSLATION_FILE_KEY = 'translationFile';

    public function getRuleDefinition(): RuleDefinition
    {
        throw new BadMethodCallException('Not implemented yet');
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

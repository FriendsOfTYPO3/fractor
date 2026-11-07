<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v14\Yaml;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorYaml\Contract\YamlFractorRule;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/14.0/Breaking-106596-RemoveLegacyFormTemplates.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v14\Yaml\MigrateLegacyFormTemplatesFractor\MigrateLegacyFormTemplatesFractorTest
 */
final class MigrateLegacyFormTemplatesFractor implements YamlFractorRule
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate legacy form templates', [new CodeSample(
            <<<'CODE_SAMPLE'
prototypes:
  standard:
    formElementsDefinition:
      Text:
        variants:
          -
            identifier: template-variant
            condition: 'getRootFormProperty("renderingOptions.templateVariant") == "version2"'
            properties:
              containerClassAttribute: 'form-element form-element-text mb-3'
              elementClassAttribute: form-control
              elementErrorClassAttribute: ~
              labelClassAttribute: form-label
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
prototypes:
  standard:
    formElementsDefinition:
      Text:
        properties:
          containerClassAttribute: 'form-element form-element-text mb-3'
          elementClassAttribute: form-control
          elementErrorClassAttribute: ~
          labelClassAttribute: form-label
CODE_SAMPLE
        )]);
    }

    public function refactor(array $yaml): array
    {
        return $this->traverse($yaml);
    }

    /**
     * @param mixed[] $yaml
     * @return mixed[]
     */
    private function traverse(array $yaml): array
    {
        foreach ($yaml as $key => $value) {
            if (is_array($value)) {
                $yaml[$key] = $this->traverse($value);
            }
        }

        if (isset($yaml['variants']) && is_array($yaml['variants'])) {
            $properties = $yaml['properties'] ?? [];
            $remainingVariants = [];
            $hasChanges = false;

            foreach ($yaml['variants'] as $variant) {
                // Check if the variant condition matches the deprecated one
                if (isset($variant['condition']) && $this->isVersion2Condition($variant['condition'])) {
                    if (isset($variant['properties']) && is_array($variant['properties'])) {
                        // Merge properties: variant properties overwrite existing ones
                        $properties = array_replace_recursive($properties, $variant['properties']);
                    }
                    $hasChanges = true;
                } else {
                    $remainingVariants[] = $variant;
                }
            }

            if ($hasChanges) {
                $yaml['properties'] = $properties;

                if ($remainingVariants === []) {
                    unset($yaml['variants']);
                } else {
                    $yaml['variants'] = $remainingVariants;
                }
            }
        }

        return $yaml;
    }

    private function isVersion2Condition(string $condition): bool
    {
        // Normalize quotes and whitespace for comparison
        $normalized = str_replace(["'", ' '], ['"', ''], $condition);
        return $normalized === 'getRootFormProperty("renderingOptions.templateVariant")=="version2"';
    }
}

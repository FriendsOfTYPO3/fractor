<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v14\Yaml;

use a9f\FractorYaml\Contract\YamlFractorRule;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/14.2/Deprecation-109152-FormDatePickerElement.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v14\Yaml\ReplaceDatePickerFormElementWithDateFractor\ReplaceDatePickerFormElementWithDateFractorTest
 */
final class ReplaceDatePickerFormElementWithDateFractor implements YamlFractorRule
{
    private const PROPERTIES = 'properties';

    private const VARIANTS = 'variants';

    private const OBSOLETE_PROPERTIES = ['dateFormat', 'enableDatePicker'];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace the DatePicker form element with the Date form element and remove its dateFormat and enableDatePicker properties. The Date element renders a native HTML5 date input, so the browser now formats the date and shows the picker.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
type: DatePicker
identifier: date-1
label: 'Pick a date'
properties:
  dateFormat: Y-m-d
  enableDatePicker: true
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
type: Date
identifier: date-1
label: 'Pick a date'
CODE_SAMPLE
                ),
            ]
        );
    }

    public function refactor(array $yaml): array
    {
        if (($yaml['type'] ?? null) === 'DatePicker' && array_key_exists('identifier', $yaml)) {
            $yaml = $this->replaceElement($yaml);
        }

        foreach ($yaml as $key => $value) {
            if (is_array($value)) {
                $yaml[$key] = $this->refactor($value);
            }
        }

        return $yaml;
    }

    /**
     * @param mixed[] $element
     * @return mixed[]
     */
    private function replaceElement(array $element): array
    {
        $element['type'] = 'Date';
        $element = $this->removeObsoleteProperties($element);

        if (! isset($element[self::VARIANTS]) || ! is_array($element[self::VARIANTS])) {
            return $element;
        }

        foreach ($element[self::VARIANTS] as $variantKey => $variant) {
            if (is_array($variant)) {
                $element[self::VARIANTS][$variantKey] = $this->removeObsoleteProperties($variant);
            }
        }

        return $element;
    }

    /**
     * @param mixed[] $element
     * @return mixed[]
     */
    private function removeObsoleteProperties(array $element): array
    {
        if (! isset($element[self::PROPERTIES]) || ! is_array($element[self::PROPERTIES])) {
            return $element;
        }

        foreach (self::OBSOLETE_PROPERTIES as $property) {
            unset($element[self::PROPERTIES][$property]);
        }

        if ($element[self::PROPERTIES] === []) {
            unset($element[self::PROPERTIES]);
        }

        return $element;
    }
}

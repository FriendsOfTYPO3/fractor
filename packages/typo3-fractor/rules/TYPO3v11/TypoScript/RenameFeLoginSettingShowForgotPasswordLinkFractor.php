<?php

declare(strict_types=1);

namespace a9f\Typo3Fractor\TYPO3v11\TypoScript;

use a9f\Fractor\RuleDoc\CodeSample\CodeSample;
use a9f\Fractor\RuleDoc\RuleDefinition;
use a9f\FractorTypoScript\AbstractTypoScriptFractor;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/11.5.x/Important-98122-FixFeloginVariableNameInTypoScriptSetup.html
 * @see \a9f\Typo3Fractor\Tests\TYPO3v11\TypoScript\RenameFeLoginSettingShowForgotPasswordLinkFractor\RenameFeLoginSettingShowForgotPasswordLinkFractorTest
 */
final class RenameFeLoginSettingShowForgotPasswordLinkFractor extends AbstractTypoScriptFractor
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rename plugin.tx_felogin_login.settings.showForgotPasswordLink to plugin.tx_felogin_login.settings.showForgotPassword',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
plugin.tx_felogin_login.settings.showForgotPasswordLink = 1

styles.content.loginform.showForgotPasswordLink = 0
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
plugin.tx_felogin_login.settings.showForgotPassword = 1

styles.content.loginform.showForgotPassword = 0
CODE_SAMPLE
                ),
            ]
        );
    }

    public function refactor(Statement $statement): null|Statement
    {
        if ($this->shouldSkip($statement)) {
            return null;
        }

        /** @var Assignment $statement */
        if ($statement->object->absoluteName === 'plugin.tx_felogin_login.settings.showForgotPasswordLink') {
            $statement->object->relativeName = str_replace(
                'showForgotPasswordLink',
                'showForgotPassword',
                $statement->object->relativeName
            );
            $statement->object->absoluteName = str_replace(
                'showForgotPasswordLink',
                'showForgotPassword',
                $statement->object->absoluteName
            );
            return $statement;
        }

        if ($statement->object->absoluteName === 'styles.content.loginform.showForgotPasswordLink') {
            $statement->object->relativeName = str_replace(
                'showForgotPasswordLink',
                'showForgotPassword',
                $statement->object->relativeName
            );
            $statement->object->absoluteName = str_replace(
                'showForgotPasswordLink',
                'showForgotPassword',
                $statement->object->absoluteName
            );
            return $statement;
        }

        if ($statement->value->value === '{$styles.content.loginform.showForgotPasswordLink}') {
            $statement->value->value = str_replace(
                'showForgotPasswordLink',
                'showForgotPassword',
                $statement->value->value
            );
            return $statement;
        }

        return null;
    }

    private function shouldSkip(Statement $statement): bool
    {
        if (! $statement instanceof Assignment) {
            return true;
        }

        if ($statement->object->absoluteName !== 'plugin.tx_felogin_login.settings.showForgotPasswordLink'
            && $statement->object->absoluteName !== 'styles.content.loginform.showForgotPasswordLink'
            && $statement->value->value !== '{$styles.content.loginform.showForgotPasswordLink}'
        ) {
            return true;
        }

        return false;
    }
}

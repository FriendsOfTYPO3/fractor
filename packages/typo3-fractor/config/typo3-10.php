<?php

declare(strict_types=1);

use a9f\Typo3Fractor\TYPO3v10\FlexForm\RemoveEnableMultiSelectFilterTextfieldFractor;
use a9f\Typo3Fractor\TYPO3v10\Fluid\RemoveNoCacheHashAndUseCacheHashAttributeFluidFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\MigrateLegacyTypoScriptConditionsFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\MigrateTypoScriptMultipleConditionBracketsFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\MigrateTypoScriptPageConditionPipeAccessFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigConcatenateJsAndCssFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigDefaultGetVarsFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigHtmlTagDirFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigHtmlTagLangKeyFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigLanguageAltFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigLanguageFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigLocaleAllFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigSimulateStaticDocumentsFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigSysLanguageIsocodeDefaultFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigSysLanguageIsocodeFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigSysLanguageModeFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigSysLanguageOverlayFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigSysLanguageUidFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigTitleTagFunctionFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigTypolinkCheckRootlineFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigTypolinkEnableLinksAcrossDomainsFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigUsernameSubstTokenFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigUseruidSubstTokenFractor;
use a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveUseCacheHashFromTypolinkTypoScriptFractor;
use a9f\Typo3Fractor\TYPO3v10\Yaml\EmailFinisherYamlFractor;
use a9f\Typo3Fractor\TYPO3v10\Yaml\TranslationFileYamlFractor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(EmailFinisherYamlFractor::class);
    $services->set(TranslationFileYamlFractor::class);
    $services->set(RemoveNoCacheHashAndUseCacheHashAttributeFluidFractor::class);
    $services->set(RemoveUseCacheHashFromTypolinkTypoScriptFractor::class);
    $services->set(MigrateLegacyTypoScriptConditionsFractor::class);
    $services->set(MigrateTypoScriptMultipleConditionBracketsFractor::class);
    $services->set(MigrateTypoScriptPageConditionPipeAccessFractor::class);
    $services->set(RemoveConfigConcatenateJsAndCssFractor::class);
    $services->set(RemoveConfigDefaultGetVarsFractor::class);
    $services->set(RemoveConfigHtmlTagDirFractor::class);
    $services->set(RemoveConfigHtmlTagLangKeyFractor::class);
    $services->set(RemoveConfigLanguageAltFractor::class);
    $services->set(RemoveConfigLanguageFractor::class);
    $services->set(RemoveConfigLocaleAllFractor::class);
    $services->set(RemoveConfigSimulateStaticDocumentsFractor::class);
    $services->set(RemoveConfigSysLanguageIsocodeFractor::class);
    $services->set(RemoveConfigSysLanguageIsocodeDefaultFractor::class);
    $services->set(RemoveConfigSysLanguageModeFractor::class);
    $services->set(RemoveConfigSysLanguageOverlayFractor::class);
    $services->set(RemoveConfigSysLanguageUidFractor::class);
    $services->set(RemoveConfigTitleTagFunctionFractor::class);
    $services->set(RemoveConfigTypolinkCheckRootlineFractor::class);
    $services->set(RemoveConfigTypolinkEnableLinksAcrossDomainsFractor::class);
    $services->set(RemoveConfigUsernameSubstTokenFractor::class);
    $services->set(RemoveConfigUseruidSubstTokenFractor::class);
    $services->set(RemoveEnableMultiSelectFilterTextfieldFractor::class);
};

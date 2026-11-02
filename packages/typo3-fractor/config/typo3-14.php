<?php

declare(strict_types=1);

use a9f\Typo3Fractor\TYPO3v14\Fluid\ChangeLogoutHandlingInFeLoginFractor;
use a9f\Typo3Fractor\TYPO3v14\Fluid\ReplaceUseNonceArgumentInAssetViewHelpersFractor;
use a9f\Typo3Fractor\TYPO3v14\Htaccess\RemoveUploadsFromDefaultHtaccessFractor;
use a9f\Typo3Fractor\TYPO3v14\TypoScript\MigrateTypoScriptConditionGetTSFEFractor;
use a9f\Typo3Fractor\TYPO3v14\TypoScript\MigrateTypoScriptGetDataPathFractor;
use a9f\Typo3Fractor\TYPO3v14\TypoScript\RemoveDuplicateDoktypeRestrictionConfigurationFractor;
use a9f\Typo3Fractor\TYPO3v14\TypoScript\RemoveExposeNonexistentUserInForgotPasswordDialogSettingInFeLoginFractor;
use a9f\Typo3Fractor\TYPO3v14\TypoScript\RemoveExternalOptionFromTypoScriptFractor;
use a9f\Typo3Fractor\TYPO3v14\TypoScript\RemoveFrontendAssetConcatenationAndCompressionFractor;
use a9f\Typo3Fractor\TYPO3v14\TypoScript\RemoveModWebLayoutDefLangBindingFractor;
use a9f\Typo3Fractor\TYPO3v14\TypoScript\RemoveUserTSConfigAuthBeRedirectToURLFractor;
use a9f\Typo3Fractor\TYPO3v14\Yaml\MigrateLegacyFormTemplatesFractor;
use a9f\Typo3Fractor\TYPO3v14\Yaml\ReplaceDatePickerFormElementWithDateFractor;
use a9f\Typo3Fractor\TYPO3v14\Yaml\ReplaceFieldExplanationTextWithDescriptionFractor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(RemoveExposeNonexistentUserInForgotPasswordDialogSettingInFeLoginFractor::class);
    $services->set(RemoveModWebLayoutDefLangBindingFractor::class);
    $services->set(ChangeLogoutHandlingInFeLoginFractor::class);
    $services->set(RemoveUploadsFromDefaultHtaccessFractor::class);
    $services->set(MigrateTypoScriptConditionGetTSFEFractor::class);
    $services->set(RemoveFrontendAssetConcatenationAndCompressionFractor::class);
    $services->set(MigrateTypoScriptGetDataPathFractor::class);
    $services->set(RemoveExternalOptionFromTypoScriptFractor::class);
    $services->set(RemoveUserTSConfigAuthBeRedirectToURLFractor::class);
    $services->set(RemoveDuplicateDoktypeRestrictionConfigurationFractor::class);
    $services->set(MigrateLegacyFormTemplatesFractor::class);
    $services->set(ReplaceUseNonceArgumentInAssetViewHelpersFractor::class);
    $services->set(ReplaceFieldExplanationTextWithDescriptionFractor::class);
    $services->set(ReplaceDatePickerFormElementWithDateFractor::class);
};

# 70 Rules Overview

## AbstractMessageGetSeverityFluidFractor

Migrate to severity property 'value'

- class: [`a9f\Typo3Fractor\TYPO3v12\Fluid\AbstractMessageGetSeverityFluidFractor`](../rules/TYPO3v12/Fluid/AbstractMessageGetSeverityFluidFractor.php)

```diff
-<div class="{severityClassMapping.{status.severity}}">
+<div class="{severityClassMapping.{status.severity.value}}">
     <!-- stuff happens here -->
 </div>
```

<br>

## ChangeLogoutHandlingInFeLoginFractor

Change logout handling in ext:felogin

- class: [`a9f\Typo3Fractor\TYPO3v14\Fluid\ChangeLogoutHandlingInFeLoginFractor`](../rules/TYPO3v14/Fluid/ChangeLogoutHandlingInFeLoginFractor.php)

```diff
-<f:form action="login" actionUri="{actionUri}" target="_top" fieldNamePrefix="">
+<f:form action="login" target="_top" fieldNamePrefix="">
```

<br>

```diff
 <div class="felogin-hidden">
     <f:form.hidden name="logintype" value="logout"/>
+    <f:if condition="{noRedirect} != ''">
+        <f:form.hidden name="noredirect" value="1" />
+    </f:if>
 </div>
```

<br>

## EmailFinisherYamlFractor

Convert single recipient values to array for EmailFinisher

- class: [`a9f\Typo3Fractor\TYPO3v10\Yaml\EmailFinisherYamlFractor`](../rules/TYPO3v10/Yaml/EmailFinisherYamlFractor.php)

```diff
 finishers:
   -
     options:
-      recipientAddress: bar@domain.com
-      recipientName: 'Bar'
+      recipients:
+        bar@domain.com: 'Bar'
```

<br>

## MigrateDeprecatedTypoScriptConditionsFractor

Migrate deprecated globalVar TSFE conditions to v12 syntax

- class: [`a9f\Typo3Fractor\TYPO3v12\TypoScript\MigrateDeprecatedTypoScriptConditionsFractor`](../rules/TYPO3v12/TypoScript/MigrateDeprecatedTypoScriptConditionsFractor.php)

```diff
-[globalVar = TSFE:type = 9818]
+[request.getPageArguments()?.getPageType() == '9818']
     page = PAGE
 [end]
```

<br>

```diff
-[globalVar = TSFE:beUserLogin > 0]
+[getTSFE()?.isBackendUserLoggedIn()]
     page = PAGE
 [end]
```

<br>

## MigrateEmailFlagToEmailTypeFlexFormFractor

Migrate email flag to email type

- class: [`a9f\Typo3Fractor\TYPO3v12\FlexForm\MigrateEmailFlagToEmailTypeFlexFormFractor`](../rules/TYPO3v12/FlexForm/MigrateEmailFlagToEmailTypeFlexFormFractor.php)

```diff
 <T3DataStructure>
     <sheets>
         <sDEF>
             <ROOT>
                 <sheetTitle>Sheet Title</sheetTitle>
                 <type>array</type>
                 <el>
                     <email_field>
                         <config>
-                            <type>input</type>
-                            <eval>trim,email</eval>
-                            <max>255</max>
+                            <type>email</type>
                         </config>
                     </email_field>
                 </el>
             </ROOT>
         </sDEF>
     </sheets>
 </T3DataStructure>
```

<br>

## MigrateEvalIntAndDouble2ToTypeNumberFlexFormFractor

Migrate eval int and double2 to type number

- class: [`a9f\Typo3Fractor\TYPO3v12\FlexForm\MigrateEvalIntAndDouble2ToTypeNumberFlexFormFractor`](../rules/TYPO3v12/FlexForm/MigrateEvalIntAndDouble2ToTypeNumberFlexFormFractor.php)

```diff
 <T3DataStructure>
     <sheets>
         <sDEF>
             <ROOT>
                 <sheetTitle>Sheet Title</sheetTitle>
                 <type>array</type>
                 <el>
                     <int_field>
                         <label>int field</label>
                         <config>
-                            <type>input</type>
-                            <eval>int</eval>
+                            <type>number</type>
                         </config>
                     </int_field>
                     <double2_field>
                         <label>double2 field</label>
                         <config>
-                            <type>input</type>
-                            <eval>double2</eval>
+                            <type>number</type>
+                            <format>decimal</format>
                         </config>
                     </double2_field>
                 </el>
             </ROOT>
         </sDEF>
     </sheets>
 </T3DataStructure>
```

<br>

## MigrateIncludeTypoScriptSyntaxFractor

Migrate INCLUDE_TYPOSCRIPT TypoScript syntax to `@import`

- class: [`a9f\Typo3Fractor\TYPO3v13\TypoScript\MigrateIncludeTypoScriptSyntaxFractor`](../rules/TYPO3v13/TypoScript/MigrateIncludeTypoScriptSyntaxFractor.php)

```diff
-<INCLUDE_TYPOSCRIPT: source="FILE:EXT:my_extension/Configuration/TypoScript/myMenu.typoscript">
+@import 'EXT:my_extension/Configuration/TypoScript/myMenu.typoscript'
```

<br>

```diff
-<INCLUDE_TYPOSCRIPT: source="DIR:EXT:my_extension/Configuration/TypoScript/" extensions="typoscript">
+@import 'EXT:my_extension/Configuration/TypoScript/*.typoscript'
```

<br>

```diff
-<INCLUDE_TYPOSCRIPT: source="DIR:EXT:my_extension/Configuration/TypoScript/" extensions="typoscript,ts">
+@import 'EXT:my_extension/Configuration/TypoScript/*.typoscript'
```

<br>

```diff
-<INCLUDE_TYPOSCRIPT: source="DIR:EXT:my_extension/Configuration/TypoScript/" extensions="setup.typoscript">
+@import 'EXT:my_extension/Configuration/TypoScript/*.setup.typoscript'
```

<br>

```diff
-<INCLUDE_TYPOSCRIPT: source="DIR:EXT:my_extension/Configuration/TypoScript/" extensions="constants.typoscript">
+@import 'EXT:my_extension/Configuration/TypoScript/*.constants.typoscript'
```

<br>

```diff
-<INCLUDE_TYPOSCRIPT: source="DIR:EXT:my_extension/Configuration/TypoScript/" extensions="tsconfig">
+@import 'EXT:my_extension/Configuration/TypoScript/*.tsconfig'
```

<br>

```diff
-<INCLUDE_TYPOSCRIPT: source="FILE:EXT:my_extension/Configuration/TypoScript/user.typoscript" condition="[frontend.user.isLoggedIn]">
+[frontend.user.isLoggedIn]
+    @import 'EXT:my_extension/Configuration/TypoScript/user.typoscript'
+[end]
```

<br>

## MigrateInternalTypeFolderToTypeFolderFlexFormFractor

Migrates TCA internal_type into new new TCA type folder

- class: [`a9f\Typo3Fractor\TYPO3v12\FlexForm\MigrateInternalTypeFolderToTypeFolderFlexFormFractor`](../rules/TYPO3v12/FlexForm/MigrateInternalTypeFolderToTypeFolderFlexFormFractor.php)

```diff
 <T3DataStructure>
     <sheets>
         <sDEF>
             <ROOT>
                 <sheetTitle>Sheet Title</sheetTitle>
                 <type>array</type>
                 <el>
                     <aColumn>
                         <config>
-                            <type>group</type>
-                            <internal_type>folder</internal_type>
+                            <type>folder</type>
                         </config>
                     </aColumn>
                 </el>
             </ROOT>
         </sDEF>
     </sheets>
 </T3DataStructure>
```

<br>

## MigrateItemsIndexedKeysToAssociativeFractor

Migrates indexed item array keys to associative for type select, radio and check

- class: [`a9f\Typo3Fractor\TYPO3v12\FlexForm\MigrateItemsIndexedKeysToAssociativeFractor`](../rules/TYPO3v12/FlexForm/MigrateItemsIndexedKeysToAssociativeFractor.php)

```diff
 <T3DataStructure>
     <sheets>
         <sDEF>
             <ROOT>
                 <sheetTitle>Sheet Title</sheetTitle>
                 <type>array</type>
                 <el>
                     <selectSingleColumn>
                         <config>
                             <type>select</type>
                             <renderType>selectSingle</renderType>
                             <items type="array">
                                 <numIndex index="0" type="array">
-                                    <numIndex index="0"/>
-                                    <numIndex index="1"/>
+                                    <label/>
+                                    <value/>
                                 </numIndex>
                                 <numIndex index="1" type="array">
-                                    <numIndex index="0">Label 1</numIndex>
-                                    <numIndex index="1">1</numIndex>
+                                    <label>Label 1</label>
+                                    <value>1</value>
                                 </numIndex>
                                 <numIndex index="2" type="array">
-                                    <numIndex index="0">Label 2</numIndex>
-                                    <numIndex index="1">2</numIndex>
+                                    <label>Label 2</label>
+                                    <value>2</value>
                                 </numIndex>
                                 <numIndex index="3" type="array">
-                                    <numIndex index="0">Label 3</numIndex>
-                                    <numIndex index="1">3</numIndex>
+                                    <label>Label 3</label>
+                                    <value>3</value>
                                 </numIndex>
                             </items>
                         </config>
                     </selectSingleColumn>
                 </el>
             </ROOT>
         </sDEF>
     </sheets>
 </T3DataStructure>
```

<br>

## MigrateLegacyFormTemplatesFractor

Migrate legacy form templates

- class: [`a9f\Typo3Fractor\TYPO3v14\Yaml\MigrateLegacyFormTemplatesFractor`](../rules/TYPO3v14/Yaml/MigrateLegacyFormTemplatesFractor.php)

```diff
 prototypes:
   standard:
     formElementsDefinition:
       Text:
-        variants:
-          -
-            identifier: template-variant
-            condition: 'getRootFormProperty("renderingOptions.templateVariant") == "version2"'
-            properties:
-              containerClassAttribute: 'form-element form-element-text mb-3'
-              elementClassAttribute: form-control
-              elementErrorClassAttribute: ~
-              labelClassAttribute: form-label
+        properties:
+          containerClassAttribute: 'form-element form-element-text mb-3'
+          elementClassAttribute: form-control
+          elementErrorClassAttribute: ~
+          labelClassAttribute: form-label
```

<br>

## MigrateLegacyTypoScriptConditionsFractor

Migrate legacy TypoScript conditions to Symfony expression language syntax

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\MigrateLegacyTypoScriptConditionsFractor`](../rules/TYPO3v10/TypoScript/MigrateLegacyTypoScriptConditionsFractor.php)

```diff
-[globalVar = GP:debug > 0]
+[request && traverse(request.getQueryParams(), 'debug') > 0]
     page = PAGE
     page.10 = TEXT
     page.10.value = GET/POST parameter "debug" is greater than 0
 [end]
```

<br>

```diff
-[globalVar = GP:tx_news_pi1|news > 0]
+[request && traverse(request.getQueryParams(), 'tx_news_pi1/news') > 0]
     page = PAGE
     page.10 = TEXT
     page.10.value = Nested GET/POST parameter "tx_news_pi1[news]" is set
 [end]
```

<br>

```diff
-[PIDupinRootline = 89,130]
+[89 in tree.rootLineParentIds || 130 in tree.rootLineParentIds]
     page = PAGE
     page.10 = TEXT
     page.10.value = A parent page in the rootline has uid 89 or 130
 [end]
```

<br>

```diff
-[PIDinRootline = 89,130]
+[89 in tree.rootLineIds || 130 in tree.rootLineIds]
     page = PAGE
     page.10 = TEXT
     page.10.value = Page with uid 89 or 130 is in the rootline
 [end]
```

<br>

```diff
-[applicationContext = Development]
+[applicationContext == "Development"]
     page = PAGE
     page.10 = TEXT
     page.10.value = Application context is exactly "Development"
 [end]
```

<br>

```diff
-[applicationContext = Development*]
+[like(applicationContext, "Development*")]
     page = PAGE
     page.10 = TEXT
     page.10.value = Application context starts with "Development"
 [end]
```

<br>

```diff
-[treeLevel = 1,3]
+[tree.level in [1,3]]
     page = PAGE
     page.10 = TEXT
     page.10.value = Current page is on tree level 1 or 3
 [end]
```

<br>

```diff
-[globalVar = TSFE:beUserLogin = 1]
+[getTSFE().beUserLogin == 1]
     page = PAGE
     page.10 = TEXT
     page.10.value = A backend user is logged in on the frontend
 [end]
```

<br>

```diff
-[globalVar = TSFE:id = {$page.home}]
+[page["uid"] == {$page.home}]
     page = PAGE
     page.10 = TEXT
     page.10.value = Current page uid matches the configured home page
 [end]
```

<br>

```diff
-[globalVar = LIT:1 = {$config.enableFeature}]
+[{$config.enableFeature} == 1]
     page = PAGE
     page.10 = TEXT
     page.10.value = TypoScript constant equals the literal value
 [end]
```

<br>

```diff
-[globalVar = BE_USER|user|admin = 1]
+[backend.user.isAdmin]
     page = PAGE
     page.10 = TEXT
     page.10.value = Current backend user is an admin
 [end]
```

<br>

```diff
-[globalString = IENV:HTTP_HOST = example.org]
+[request.getNormalizedParams().getHttpHost() == "example.org"]
     page = PAGE
     page.10 = TEXT
     page.10.value = Current hostname is example.org
 [end]
```

<br>

```diff
-[IP = 123.456.*.*]
+[ip("123.456.*.*")]
     page = PAGE
     page.10 = TEXT
     page.10.value = Visitor IP starts with 123.456
 [end]
```

<br>

```diff
-[hostname = example.org]
+[request.getNormalizedParams().getHttpHost() == "example.org"]
     page = PAGE
     page.10 = TEXT
     page.10.value = Visitor hostname is example.org
 [end]
```

<br>

```diff
-[compatVersion = 9.5.0]
+[compatVersion("9.5.0")]
     page = PAGE
     page.10 = TEXT
     page.10.value = TYPO3 version is at least 9.5.0
 [end]
```

<br>

## MigrateNullFlagFlexFormFractor

Migrate null flag

- class: [`a9f\Typo3Fractor\TYPO3v12\FlexForm\MigrateNullFlagFlexFormFractor`](../rules/TYPO3v12/FlexForm/MigrateNullFlagFlexFormFractor.php)

```diff
 <T3DataStructure>
     <sheets>
         <sDEF>
             <ROOT>
                 <sheetTitle>Sheet Title</sheetTitle>
                 <type>array</type>
                 <el>
                     <aFlexField>
                         <config>
-                            <eval>null</eval>
+                            <nullable>true</nullable>
                         </config>
                     </aFlexField>
                 </el>
             </ROOT>
         </sDEF>
     </sheets>
 </T3DataStructure>
```

<br>

## MigratePasswordAndSaltedPasswordToPasswordTypeFlexFormFractor

Migrate password and salted password to password type

- class: [`a9f\Typo3Fractor\TYPO3v12\FlexForm\MigratePasswordAndSaltedPasswordToPasswordTypeFlexFormFractor`](../rules/TYPO3v12/FlexForm/MigratePasswordAndSaltedPasswordToPasswordTypeFlexFormFractor.php)

```diff
 <T3DataStructure>
     <sheets>
         <sDEF>
             <ROOT>
                 <sheetTitle>Sheet Title</sheetTitle>
                 <type>array</type>
                 <el>
                     <password_field>
                         <config>
-                            <type>input</type>
-                            <eval>trim,password,saltedPassword</eval>
+                            <type>password</type>
                         </config>
                     </password_field>
                     <another_password_field>
                         <config>
-                            <type>input</type>
-                            <eval>trim,password</eval>
+                            <type>password</type>
+                            <hashed>false</hashed>
                         </config>
                     </another_password_field>
                 </el>
             </ROOT>
         </sDEF>
     </sheets>
 </T3DataStructure>
```

<br>

## MigratePluginContentElementAndPluginSubtypesFractorChatGPT

Migrate plugin content element and plugin subtypes (list_type)

- class: [`a9f\Typo3Fractor\TYPO3v13\TypoScript\MigratePluginContentElementAndPluginSubtypesFractorChatGPT`](../rules/TYPO3v13/TypoScript/MigratePluginContentElementAndPluginSubtypesFractorChatGPT.php)

```diff
-tt_content.list.20.examples_pi1 = USER
-tt_content.list.20.examples_pi1 {
-    userFunc = MyVendor\Examples\Controller\ExampleController->example
+tt_content.examples_pi1 =< lib.contentElement
+tt_content.examples_pi1 {
+    20 = USER
+    20 {
+        userFunc = MyVendor\Examples\Controller\ExampleController->example
+    }
+    templateName = Generic
 }

-tt_content.list.20.examples_pi1 < plugin.tx_examples_pi1
+tt_content.examples_pi1.20 < plugin.tx_examples_pi1
```

<br>

```diff
-tt_content.list.20.examples_pi1 < plugin.tx_examples_pi1
+tt_content.examples_pi1.20 < plugin.tx_examples_pi1
```

<br>

## MigratePluginContentElementAndPluginSubtypesFractorGeminiV1

Migrate plugin content element and plugin subtypes (list_type)

- class: [`a9f\Typo3Fractor\TYPO3v13\TypoScript\MigratePluginContentElementAndPluginSubtypesFractorGeminiV1`](../rules/TYPO3v13/TypoScript/MigratePluginContentElementAndPluginSubtypesFractorGeminiV1.php)

```diff
-tt_content.list.20.examples_pi1 = USER
-tt_content.list.20.examples_pi1 {
-    userFunc = MyVendor\Examples\Controller\ExampleController->example
+tt_content.examples_pi1 =< lib.contentElement
+tt_content.examples_pi1 {
+    20 = USER
+    20 {
+        userFunc = MyVendor\Examples\Controller\ExampleController->example
+    }
+    templateName = Generic
 }

-tt_content.list.20.examples_pi1 < plugin.tx_examples_pi1
+tt_content.examples_pi1.20 < plugin.tx_examples_pi1
```

<br>

```diff
-tt_content.list.20.examples_pi1 < plugin.tx_examples_pi1
+tt_content.examples_pi1.20 < plugin.tx_examples_pi1
```

<br>

## MigratePluginContentElementAndPluginSubtypesFractorGeminiV2

Migrate plugin content element and plugin subtypes (list_type)

- class: [`a9f\Typo3Fractor\TYPO3v13\TypoScript\MigratePluginContentElementAndPluginSubtypesFractorGeminiV2`](../rules/TYPO3v13/TypoScript/MigratePluginContentElementAndPluginSubtypesFractorGeminiV2.php)

```diff
-tt_content.list.20.examples_pi1 = USER
-tt_content.list.20.examples_pi1 {
-    userFunc = MyVendor\Examples\Controller\ExampleController->example
+tt_content.examples_pi1 =< lib.contentElement
+tt_content.examples_pi1 {
+    20 = USER
+    20 {
+        userFunc = MyVendor\Examples\Controller\ExampleController->example
+    }
+    templateName = Generic
 }

-tt_content.list.20.examples_pi1 < plugin.tx_examples_pi1
+tt_content.examples_pi1.20 < plugin.tx_examples_pi1
```

<br>

```diff
-tt_content.list.20.examples_pi1 < plugin.tx_examples_pi1
+tt_content.examples_pi1.20 < plugin.tx_examples_pi1
```

<br>

## MigratePluginContentElementAndPluginSubtypesFractor

Migrate plugin content element and plugin subtypes (list_type)

- class: [`a9f\Typo3Fractor\TYPO3v13\TypoScript\MigratePluginContentElementAndPluginSubtypesFractor`](../rules/TYPO3v13/TypoScript/MigratePluginContentElementAndPluginSubtypesFractor.php)

```diff
-tt_content.list.20.examples_pi1 = USER
-tt_content.list.20.examples_pi1 {
+tt_content.examples_pi1 = USER
+tt_content.examples_pi1 {
     userFunc = MyVendor\Examples\Controller\ExampleController->example
 }

-tt_content.list.20.examples_pi1 < plugin.tx_examples_pi1
+tt_content.examples_pi1 < plugin.tx_examples_pi1
```

<br>

## MigrateRenderTypeColorpickerToTypeColorFlexFormFractor

Migrate renderType colorpicker to type color

- class: [`a9f\Typo3Fractor\TYPO3v12\FlexForm\MigrateRenderTypeColorpickerToTypeColorFlexFormFractor`](../rules/TYPO3v12/FlexForm/MigrateRenderTypeColorpickerToTypeColorFlexFormFractor.php)

```diff
 <T3DataStructure>
     <sheets>
         <sDEF>
             <ROOT>
                 <sheetTitle>Sheet Title</sheetTitle>
                 <type>array</type>
                 <el>
                     <a_color_field>
                         <config>
-                            <type>input</type>
-                            <renderType>colorpicker</renderType>
+                            <type>color</type>
                             <required>1</required>
                             <size>20</size>
-                            <max>1234</max>
-                            <eval>trim,null</eval>
                             <valuePicker>
                                 <items type="array">
                                     <numIndex index="0" type="array">
                                         <numIndex index="0">typo3 orange</numIndex>
                                         <numIndex index="1">#FF8700</numIndex>
                                     </numIndex>
                                 </items>
                             </valuePicker>
                         </config>
                     </a_color_field>
                 </el>
             </ROOT>
         </sDEF>
     </sheets>
 </T3DataStructure>
```

<br>

## MigrateRequiredFlagFlexFormFractor

Migrate required flag

- class: [`a9f\Typo3Fractor\TYPO3v12\FlexForm\MigrateRequiredFlagFlexFormFractor`](../rules/TYPO3v12/FlexForm/MigrateRequiredFlagFlexFormFractor.php)

```diff
 <T3DataStructure>
     <sheets>
         <sDEF>
             <ROOT>
                 <sheetTitle>Sheet Title</sheetTitle>
                 <type>array</type>
                 <el>
                     <aColumn>
                         <config>
-                            <eval>trim,required</eval>
+                            <eval>trim</eval>
+                            <required>1</required>
                         </config>
                     </aColumn>
                 </el>
             </ROOT>
         </sDEF>
     </sheets>
 </T3DataStructure>
```

<br>

## MigrateTypeNoneColsToSizeFlexFormFractor

Migrates option cols to size for TCA type none

- class: [`a9f\Typo3Fractor\TYPO3v12\FlexForm\MigrateTypeNoneColsToSizeFlexFormFractor`](../rules/TYPO3v12/FlexForm/MigrateTypeNoneColsToSizeFlexFormFractor.php)

```diff
 <T3DataStructure>
     <sheets>
         <sDEF>
             <ROOT>
                 <sheetTitle>Sheet Title</sheetTitle>
                 <type>array</type>
                 <el>
                     <aColumn>
                         <config>
                             <type>none</type>
-                            <cols>20</cols>
+                            <size>20</size>
                         </config>
                     </aColumn>
                 </el>
             </ROOT>
         </sDEF>
     </sheets>
 </T3DataStructure>
```

<br>

## MigrateTypoScriptConditionGetTSFEFractor

Migrate TypoScript condition function `getTSFE()`

- class: [`a9f\Typo3Fractor\TYPO3v14\TypoScript\MigrateTypoScriptConditionGetTSFEFractor`](../rules/TYPO3v14/TypoScript/MigrateTypoScriptConditionGetTSFEFractor.php)

```diff
-[getTSFE() && getTSFE().id == 42]
+[request?.getPageArguments()?.getPageId() == 42]
     temp.foo = 42
 [end]
```

<br>

## MigrateTypoScriptGetDataPathFractor

Migrate TypoScript getData \"path\"

- class: [`a9f\Typo3Fractor\TYPO3v14\TypoScript\MigrateTypoScriptGetDataPathFractor`](../rules/TYPO3v14/TypoScript/MigrateTypoScriptGetDataPathFractor.php)

```diff
 page.20 = TEXT
 page.20 {
-    data = path : EXT:core/Resources/Public/Icons/Extension.svg
+    data = asset : EXT:core/Resources/Public/Icons/Extension.svg
 }
```

<br>

## MigrateTypoScriptLoginUserAndUsergroupConditionsFractor

Migrate TypoScript loginUser and usergroup conditions (both function-call and equals syntax)

- class: [`a9f\Typo3Fractor\TYPO3v12\TypoScript\MigrateTypoScriptLoginUserAndUsergroupConditionsFractor`](../rules/TYPO3v12/TypoScript/MigrateTypoScriptLoginUserAndUsergroupConditionsFractor.php)

```diff
-[loginUser('*')]
+[frontend.user.isLoggedIn]
     page = PAGE
     page.20 = TEXT
     page.20.value = User is logged in
 [end]
```

<br>

```diff
-[usergroup(11)]
+[11 in frontend.user.userGroupIds]
     page = PAGE
     page.70 = TEXT
     page.70.value = Frontend user is member of group with uid 11
 [end]
```

<br>

```diff
-[loginUser = *]
+[frontend.user.isLoggedIn]
     page = PAGE
     page.10 = TEXT
     page.10.value = User is logged in
 [end]
```

<br>

```diff
-[usergroup = 1,2]
+[1 in frontend.user.userGroupIds || 2 in frontend.user.userGroupIds]
     page = PAGE
     page.30 = TEXT
     page.30.value = User is in group 1 or 2
 [end]
```

<br>

## MigrateTypoScriptMultipleConditionBracketsFractor

Merge multiple TypoScript condition bracket pairs into a single bracket with logical operators inside

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\MigrateTypoScriptMultipleConditionBracketsFractor`](../rules/TYPO3v10/TypoScript/MigrateTypoScriptMultipleConditionBracketsFractor.php)

```diff
-[conditionA] || [conditionB]
+[conditionA || conditionB]
     page = PAGE
 [end]
```

<br>

```diff
-[conditionA] && [conditionB]
+[conditionA && conditionB]
     page = PAGE
 [end]
```

<br>

## MigrateTypoScriptPageConditionPipeAccessFractor

Migrate page pipe access in TypoScript conditions to bracket array access syntax

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\MigrateTypoScriptPageConditionPipeAccessFractor`](../rules/TYPO3v10/TypoScript/MigrateTypoScriptPageConditionPipeAccessFractor.php)

```diff
-[page|uid = 2]
+[page["uid"] == 2]
     page = PAGE
     page.10 = TEXT
     page.10.value = Hello
 [end]
```

<br>

```diff
-[page|layout == 1]
+[page["layout"] == 1]
     page = PAGE
     page.10 = TEXT
     page.10.value = Layout 1
 [end]
```

<br>

## MigrateTypoScriptPageConditionToTraverseFractor

Migrate page["field"] to traverse(page, "field") in TypoScript conditions for safe access

- class: [`a9f\Typo3Fractor\TYPO3v11\TypoScript\MigrateTypoScriptPageConditionToTraverseFractor`](../rules/TYPO3v11/TypoScript/MigrateTypoScriptPageConditionToTraverseFractor.php)

```diff
-[page["uid"] == 1]
+[traverse(page, "uid") == 1]
     page = PAGE
     page.10 = TEXT
     page.10.value = Hello
 [end]
```

<br>

```diff
-[page["backend_layout"] == "pagets__home"]
+[traverse(page, "backend_layout") == "pagets__home"]
     page = PAGE
     page.10 = TEXT
     page.10.value = Home layout
 [end]
```

<br>

## RemoveConfigConcatenateJsAndCssFractor

Remove config.concatenateJsAndCss

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigConcatenateJsAndCssFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigConcatenateJsAndCssFractor.php)

```diff
-config.concatenateJsAndCss = 1
+-
```

<br>

## RemoveConfigDefaultGetVarsFractor

Remove config.defaultGetVars

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigDefaultGetVarsFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigDefaultGetVarsFractor.php)

```diff
-config.defaultGetVars = 1
+-
```

<br>

## RemoveConfigDisablePageExternalUrlFractor

Remove config.disablePageExternalUrl

- class: [`a9f\Typo3Fractor\TYPO3v12\TypoScript\RemoveConfigDisablePageExternalUrlFractor`](../rules/TYPO3v12/TypoScript/RemoveConfigDisablePageExternalUrlFractor.php)

```diff
-config.disablePageExternalUrl = 1
+-
```

<br>

## RemoveConfigDoctypeSwitchFractor

Remove config.doctypeSwitch

- class: [`a9f\Typo3Fractor\TYPO3v12\TypoScript\RemoveConfigDoctypeSwitchFractor`](../rules/TYPO3v12/TypoScript/RemoveConfigDoctypeSwitchFractor.php)

```diff
-config.doctypeSwitch = 1
+-
```

<br>

## RemoveConfigHtmlTagDirFractor

Remove config.htmlTag_dir

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigHtmlTagDirFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigHtmlTagDirFractor.php)

```diff
-config.htmlTag_dir = 1
+-
```

<br>

## RemoveConfigHtmlTagLangKeyFractor

Remove config.htmlTag_langKey

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigHtmlTagLangKeyFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigHtmlTagLangKeyFractor.php)

```diff
-config.htmlTag_langKey = 1
+-
```

<br>

## RemoveConfigLanguageAltFractor

Remove config.language_alt

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigLanguageAltFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigLanguageAltFractor.php)

```diff
-config.language_alt = 1
+-
```

<br>

## RemoveConfigLanguageFractor

Remove config.language

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigLanguageFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigLanguageFractor.php)

```diff
-config.language = 1
+-
```

<br>

## RemoveConfigLocaleAllFractor

Remove config.locale_all

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigLocaleAllFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigLocaleAllFractor.php)

```diff
-config.locale_all = 1
+-
```

<br>

## RemoveConfigMetaCharsetFractor

Remove config.metaCharset

- class: [`a9f\Typo3Fractor\TYPO3v12\TypoScript\RemoveConfigMetaCharsetFractor`](../rules/TYPO3v12/TypoScript/RemoveConfigMetaCharsetFractor.php)

```diff
-config.metaCharset = 1
+-
```

<br>

## RemoveConfigPrefixLocalAnchorsFractor

Remove config.prefixLocalAnchors

- class: [`a9f\Typo3Fractor\TYPO3v8\TypoScript\RemoveConfigPrefixLocalAnchorsFractor`](../rules/TYPO3v8/TypoScript/RemoveConfigPrefixLocalAnchorsFractor.php)

```diff
-config.prefixLocalAnchors = 1
+-
```

<br>

## RemoveConfigRenderCharsetFractor

Remove config.renderCharset

- class: [`a9f\Typo3Fractor\TYPO3v8\TypoScript\RemoveConfigRenderCharsetFractor`](../rules/TYPO3v8/TypoScript/RemoveConfigRenderCharsetFractor.php)

```diff
-config.renderCharset = 1
+-
```

<br>

## RemoveConfigSendCacheHeadersOnlyWhenLoginDeniedInBranchFractor

Remove config.sendCacheHeaders_onlyWhenLoginDeniedInBranch

- class: [`a9f\Typo3Fractor\TYPO3v12\TypoScript\RemoveConfigSendCacheHeadersOnlyWhenLoginDeniedInBranchFractor`](../rules/TYPO3v12/TypoScript/RemoveConfigSendCacheHeadersOnlyWhenLoginDeniedInBranchFractor.php)

```diff
-config.sendCacheHeaders_onlyWhenLoginDeniedInBranch = 1
+-
```

<br>

## RemoveConfigSimulateStaticDocumentsFractor

Remove config.simulateStaticDocuments

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigSimulateStaticDocumentsFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigSimulateStaticDocumentsFractor.php)

```diff
-config.simulateStaticDocuments = 1
+-
```

<br>

## RemoveConfigSpamProtectEmailAddressesAsciiOptionFractor

Remove config.spamProtectEmailAddresses with option ascii

- class: [`a9f\Typo3Fractor\TYPO3v12\TypoScript\RemoveConfigSpamProtectEmailAddressesAsciiOptionFractor`](../rules/TYPO3v12/TypoScript/RemoveConfigSpamProtectEmailAddressesAsciiOptionFractor.php)

```diff
-config.spamProtectEmailAddresses = ascii
+-
```

<br>

## RemoveConfigSysLanguageIsocodeDefaultFractor

Remove config.sys_language_isocode_default

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigSysLanguageIsocodeDefaultFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigSysLanguageIsocodeDefaultFractor.php)

```diff
-config.sys_language_isocode_default = 1
+-
```

<br>

## RemoveConfigSysLanguageIsocodeFractor

Remove config.sys_language_isocode

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigSysLanguageIsocodeFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigSysLanguageIsocodeFractor.php)

```diff
-config.sys_language_isocode = 1
+-
```

<br>

## RemoveConfigSysLanguageModeFractor

Remove config.sys_language_mode

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigSysLanguageModeFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigSysLanguageModeFractor.php)

```diff
-config.sys_language_mode = 1
+-
```

<br>

## RemoveConfigSysLanguageOverlayFractor

Remove config.sys_language_overlay

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigSysLanguageOverlayFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigSysLanguageOverlayFractor.php)

```diff
-config.sys_language_overlay = 1
+-
```

<br>

## RemoveConfigSysLanguageUidFractor

Remove config.sys_language_uid

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigSysLanguageUidFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigSysLanguageUidFractor.php)

```diff
-config.sys_language_uid = 1
+-
```

<br>

## RemoveConfigTitleTagFunctionFractor

Remove config.titleTagFunction

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigTitleTagFunctionFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigTitleTagFunctionFractor.php)

```diff
-config.titleTagFunction = 1
+-
```

<br>

## RemoveConfigTxRealurlEnableFractor

Remove config.tx_realurl_enable

- class: [`a9f\Typo3Fractor\TYPO3v9\TypoScript\RemoveConfigTxRealurlEnableFractor`](../rules/TYPO3v9/TypoScript/RemoveConfigTxRealurlEnableFractor.php)

```diff
-config.tx_realurl_enable = 1
+-
```

<br>

## RemoveConfigTypolinkCheckRootlineFractor

Remove config.typolinkCheckRootline

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigTypolinkCheckRootlineFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigTypolinkCheckRootlineFractor.php)

```diff
-config.typolinkCheckRootline = 1
+-
```

<br>

## RemoveConfigTypolinkEnableLinksAcrossDomainsFractor

Remove config.typolinkEnableLinksAcrossDomains

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigTypolinkEnableLinksAcrossDomainsFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigTypolinkEnableLinksAcrossDomainsFractor.php)

```diff
-config.typolinkEnableLinksAcrossDomains = 1
+-
```

<br>

## RemoveConfigUniqueLinkVarsFractor

Remove config.uniqueLinkVars

- class: [`a9f\Typo3Fractor\TYPO3v7\TypoScript\RemoveConfigUniqueLinkVarsFractor`](../rules/TYPO3v7/TypoScript/RemoveConfigUniqueLinkVarsFractor.php)

```diff
-config.uniqueLinkVars = 1
+-
```

<br>

## RemoveConfigUsernameSubstTokenFractor

Remove config.USERNAME_substToken

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigUsernameSubstTokenFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigUsernameSubstTokenFractor.php)

```diff
-config.USERNAME_substToken = 1
+-
```

<br>

## RemoveConfigUseruidSubstTokenFractor

Remove config.USERUID_substToken

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveConfigUseruidSubstTokenFractor`](../rules/TYPO3v10/TypoScript/RemoveConfigUseruidSubstTokenFractor.php)

```diff
-config.USERUID_substToken = 1
+-
```

<br>

## RemoveDuplicateDoktypeRestrictionConfigurationFractor

Remove mod.web_list.noViewWithDokTypes

- class: [`a9f\Typo3Fractor\TYPO3v14\TypoScript\RemoveDuplicateDoktypeRestrictionConfigurationFractor`](../rules/TYPO3v14/TypoScript/RemoveDuplicateDoktypeRestrictionConfigurationFractor.php)

```diff
-mod.web_list.noViewWithDokTypes = 1
+-
```

<br>

## RemoveExposeNonexistentUserInForgotPasswordDialogSettingInFeLoginFractor

Remove plugin.tx_felogin_login.settings.exposeNonexistentUserInForgotPasswordDialog

- class: [`a9f\Typo3Fractor\TYPO3v14\TypoScript\RemoveExposeNonexistentUserInForgotPasswordDialogSettingInFeLoginFractor`](../rules/TYPO3v14/TypoScript/RemoveExposeNonexistentUserInForgotPasswordDialogSettingInFeLoginFractor.php)

```diff
-plugin.tx_felogin_login.settings.exposeNonexistentUserInForgotPasswordDialog = 1
+-
```

<br>

## RemoveExternalOptionFromTypoScriptFractor

Remove "external" option from TypoScript

- class: [`a9f\Typo3Fractor\TYPO3v14\TypoScript\RemoveExternalOptionFromTypoScriptFractor`](../rules/TYPO3v14/TypoScript/RemoveExternalOptionFromTypoScriptFractor.php)

```diff
 page = PAGE
 page.includeCSS {
     main = https://example.com/styles/main.css
-    main.external = 1
-    other = /styles/main.css
-    other.external = 1
+    other = URI:/styles/main.css
 }
```

<br>

## RemoveFrontendAssetConcatenationAndCompressionFractor

Remove Frontend Asset Concatenation and Compression

- class: [`a9f\Typo3Fractor\TYPO3v14\TypoScript\RemoveFrontendAssetConcatenationAndCompressionFractor`](../rules/TYPO3v14/TypoScript/RemoveFrontendAssetConcatenationAndCompressionFractor.php)

```diff
-config.compressCss
-config.compressJs
-config.concatenateCss
-config.concatenateJs
+-
+-
+-
+-
```

<br>

```diff
 page = PAGE
 page.includeCSS {
     main = EXT:site_package/Resources/Public/Css/main.css
-    main.disableCompression = 1
-    main.excludeFromConcatenation = 1
 }
```

<br>

## RemoveModNewPageWizOverrideWithExtensionFractor

Remove mod.web_list.newPageWiz.overrideWithExtension

- class: [`a9f\Typo3Fractor\TYPO3v8\TypoScript\RemoveModNewPageWizOverrideWithExtensionFractor`](../rules/TYPO3v8/TypoScript/RemoveModNewPageWizOverrideWithExtensionFractor.php)

```diff
-mod.web_list.newPageWiz.overrideWithExtension = 1
+-
```

<br>

## RemoveModWebLayoutDefLangBindingFractor

Remove mod.web_layout.defLangBinding

- class: [`a9f\Typo3Fractor\TYPO3v14\TypoScript\RemoveModWebLayoutDefLangBindingFractor`](../rules/TYPO3v14/TypoScript/RemoveModWebLayoutDefLangBindingFractor.php)

```diff
-mod.web_layout.defLangBinding = 1
+-
```

<br>

## RemoveNewContentElementWizardOptionsFractor

Remove TSConfig mod.web_layout.disableNewContentElementWizard and mod.newContentElementWizard.override

- class: [`a9f\Typo3Fractor\TYPO3v12\TypoScript\RemoveNewContentElementWizardOptionsFractor`](../rules/TYPO3v12/TypoScript/RemoveNewContentElementWizardOptionsFractor.php)

```diff
-mod.web_layout.disableNewContentElementWizard = 1
-mod.newContentElementWizard.override = 1
+-
```

<br>

## RemoveNoCacheHashAndUseCacheHashAttributeFluidFractor

Remove noCacheHash="1" and useCacheHash="1" attribute

- class: [`a9f\Typo3Fractor\TYPO3v10\Fluid\RemoveNoCacheHashAndUseCacheHashAttributeFluidFractor`](../rules/TYPO3v10/Fluid/RemoveNoCacheHashAndUseCacheHashAttributeFluidFractor.php)

```diff
-<f:link.page noCacheHash="1">Link</f:link.page>
-<f:link.typolink useCacheHash="1">Link</f:link.typolink>
+<f:link.page>Link</f:link.page>
+<f:link.typolink>Link</f:link.typolink>
```

<br>

## RemoveOptionAlternateBgColorsFractor

Remove mod.web_list.alternateBgColors

- class: [`a9f\Typo3Fractor\TYPO3v7\TypoScript\RemoveOptionAlternateBgColorsFractor`](../rules/TYPO3v7/TypoScript/RemoveOptionAlternateBgColorsFractor.php)

```diff
-mod.web_list.alternateBgColors = 1
+-
```

<br>

## RemovePageDoktypeRecyclerFromUserTsConfigFractor

Remove Page Doktype Recycler (255) from User Tsconfig

- class: [`a9f\Typo3Fractor\TYPO3v13\TypoScript\RemovePageDoktypeRecyclerFromUserTsConfigFractor`](../rules/TYPO3v13/TypoScript/RemovePageDoktypeRecyclerFromUserTsConfigFractor.php)

```diff
 options.pageTree {
-    doktypesToShowInNewPageDragArea = 1,6,4,7,3,254,255,199
+    doktypesToShowInNewPageDragArea = 1,6,4,7,3,254,199
 }
```

<br>

## RemoveTceFormsDomElementFlexFormFractor

Remove TCEForms key from all elements in data structure

- class: [`a9f\Typo3Fractor\TYPO3v12\FlexForm\RemoveTceFormsDomElementFlexFormFractor`](../rules/TYPO3v12/FlexForm/RemoveTceFormsDomElementFlexFormFractor.php)

```diff
 <T3DataStructure>
     <ROOT>
-        <TCEforms>
-            <sheetTitle>aTitle</sheetTitle>
-        </TCEforms>
+        <sheetTitle>aTitle</sheetTitle>
         <type>array</type>
         <el>
             <aFlexField>
-                <TCEforms>
-                    <label>aFlexFieldLabel</label>
-                    <config>
-                        <type>input</type>
-                    </config>
-                </TCEforms>
+                <label>aFlexFieldLabel</label>
+                <config>
+                    <type>input</type>
+                </config>
             </aFlexField>
         </el>
     </ROOT>
 </T3DataStructure>
```

<br>

## RemoveUploadsFromDefaultHtaccessFractor

Remove uploads/ from default .htaccess and add _assets/

- class: [`a9f\Typo3Fractor\TYPO3v14\Htaccess\RemoveUploadsFromDefaultHtaccessFractor`](../rules/TYPO3v14/Htaccess/RemoveUploadsFromDefaultHtaccessFractor.php)

```diff
-RewriteRule ^(?:fileadmin/|typo3conf/|typo3temp/|uploads/) - [L]
+RewriteRule ^(?:fileadmin/|typo3conf/|typo3temp/|_assets/) - [L]
```

<br>

## RemoveUseCacheHashFromTypolinkTypoScriptFractor

Remove useCacheHash TypoScript setting

- class: [`a9f\Typo3Fractor\TYPO3v10\TypoScript\RemoveUseCacheHashFromTypolinkTypoScriptFractor`](../rules/TYPO3v10/TypoScript/RemoveUseCacheHashFromTypolinkTypoScriptFractor.php)

```diff
 typolink {
     parameter = 3
-    useCacheHash = 1
 }
```

<br>

## RemoveUserTSConfigAuthBeRedirectToURLFractor

Remove auth.BE.redirectToURL

- class: [`a9f\Typo3Fractor\TYPO3v14\TypoScript\RemoveUserTSConfigAuthBeRedirectToURLFractor`](../rules/TYPO3v14/TypoScript/RemoveUserTSConfigAuthBeRedirectToURLFractor.php)

```diff
-auth.BE.redirectToURL = 1
+-
```

<br>

## RemoveWorkspaceModeOptionsFractor

Remove TSConfig options.workspaces.swapMode and options.workspaces.changeStageMode

- class: [`a9f\Typo3Fractor\TYPO3v12\TypoScript\RemoveWorkspaceModeOptionsFractor`](../rules/TYPO3v12/TypoScript/RemoveWorkspaceModeOptionsFractor.php)

```diff
-options.workspaces.swapMode = any
-options.workspaces.changeStageMode = any
+-
```

<br>

## RenameConfigXhtmlDoctypeToDoctypeFractor

Migrate typoscript xhtmlDoctype to doctype

- class: [`a9f\Typo3Fractor\TYPO3v12\TypoScript\RenameConfigXhtmlDoctypeToDoctypeFractor`](../rules/TYPO3v12/TypoScript/RenameConfigXhtmlDoctypeToDoctypeFractor.php)

```diff
-config.xhtmlDoctype = 1
+config.doctype = 1
```

<br>

## RenameFeLoginSettingShowForgotPasswordLinkFractor

Rename plugin.tx_felogin_login.settings.showForgotPasswordLink to plugin.tx_felogin_login.settings.showForgotPassword

- class: [`a9f\Typo3Fractor\TYPO3v11\TypoScript\RenameFeLoginSettingShowForgotPasswordLinkFractor`](../rules/TYPO3v11/TypoScript/RenameFeLoginSettingShowForgotPasswordLinkFractor.php)

```diff
-plugin.tx_felogin_login.settings.showForgotPasswordLink = 1
+plugin.tx_felogin_login.settings.showForgotPassword = 1

-styles.content.loginform.showForgotPasswordLink = 0
+styles.content.loginform.showForgotPassword = 0
```

<br>

## RenameTcemainLinkHandlerMailKeyFractor

Rename key mail to email for MailLinkHandler

- class: [`a9f\Typo3Fractor\TYPO3v12\TypoScript\RenameTcemainLinkHandlerMailKeyFractor`](../rules/TYPO3v12/TypoScript/RenameTcemainLinkHandlerMailKeyFractor.php)

```diff
 TCEMAIN.linkHandler {
-    mail {
+    email {
         handler = TYPO3\CMS\Recordlist\LinkHandler\MailLinkHandler
         label = LLL:EXT:recordlist/Resources/Private/Language/locallang_browse_links.xlf:email
         displayAfter = page,file,folder,url
         scanBefore = url
     }
 }
```

<br>

## ReplaceCaseDefaultWithDefaultCaseFluidFractor

Replace f:case with default attribute by f:defaultCase

- class: [`a9f\Typo3Fractor\TYPO3v8\Fluid\ReplaceCaseDefaultWithDefaultCaseFluidFractor`](../rules/TYPO3v8/Fluid/ReplaceCaseDefaultWithDefaultCaseFluidFractor.php)

```diff
 <f:switch expression="{someVariable}">
     <f:case value="foo">foo</f:case>
-    <f:case default="true">bar</f:case>
+    <f:defaultCase>bar</f:defaultCase>
 </f:switch>
```

<br>

## TranslationFileYamlFractor

Use key translationFiles instead of translationFile

- class: [`a9f\Typo3Fractor\TYPO3v10\Yaml\TranslationFileYamlFractor`](../rules/TYPO3v10/Yaml/TranslationFileYamlFractor.php)

```diff
 TYPO3:
   CMS:
     Form:
       prototypes:
         standard:
           formElementsDefinition:
             Form:
               renderingOptions:
                 translation:
-                  translationFile:
-                    10: 'EXT:form/Resources/Private/Language/locallang.xlf'
+                  translationFiles:
                     20: 'EXT:myextension/Resources/Private/Language/locallang.xlf'
```

<br>

## UseConfigArrayForTSFEPropertiesFractor

Use config array in TSFE instead of deprecated class properties

- class: [`a9f\Typo3Fractor\TYPO3v12\TypoScript\UseConfigArrayForTSFEPropertiesFractor`](../rules/TYPO3v12/TypoScript/UseConfigArrayForTSFEPropertiesFractor.php)

```diff
-page.10.data = TSFE:fileTarget
+page.10.data = TSFE:config|config|fileTarget
```

<br>

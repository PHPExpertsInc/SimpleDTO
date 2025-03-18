## v3.8.1

* **[2025-03-17 22:01:49 CDT]** Require minimal phpexperts/datatype-validator v1.7.0.

## v3.8.0

* **[2025-03-13 17:31:31 CDT]** Upgraded to PHPUnit v12 and added attributes for every phpunit @tag.
* **[2025-03-13 16:37:22 CDT]** Upgraded to PHP Experts, Inc's phar-distributed fork of povils/phpmnd.
* **[2025-03-13 16:36:45 CDT]** Upgraded to phpexperts/dockerize v12.1.0.
* **[2025-03-12 14:42:07 CDT]** Majorly rewrote NestedDTO::processDTOArray() and tests for simplicity, efficiency, and correct behavior.
* **[2025-03-12 14:32:48 CDT]** Upgraded to PHPUnit v10.5.32 and v11.
* **[2025-03-12 13:58:52 CDT]** Added support for PHP v8.4.
* **[2025-03-12 13:56:41 CDT]** Fixed PHP 8.4 deprecations.
* **[2024-07-30 21:12:12 CDT]** [m] Added some more unit test code coverage.

## v3.7.1

* **[2024-07-09 06:29:19 CDT]** Added support for Carbon 3.x and phpexperts/dockerize v10.x.
* **[2024-07-30 21:12:12 CDT]** [m] Added some more unit test code coverage.
* **[2024-07-30 21:14:36 CDT]** [m] Added a tombstone for potentially dead code.

## v3.7.0

* **[2024-06-26 23:23:03 CDT]** Added documentation for WriteOnce.
* **[2024-06-26 22:59:47 CDT]** Added a mechanism to support normal PHP protected properties via Attributes.
* **[2024-06-26 05:38:00 CDT]** Majorly refactored NestedDTO to remove the need for passing in an array of expected DTOs.
* **[2024-06-26 00:40:10 CDT]** Removed dead validation code in the NestedDTO.
* **[2024-04-29 15:23:04 CDT]** [m] Added tests for the README.
* **[2024-06-26 22:57:43 CDT]** [m] Added universal method return types to SimpleDTOTest.

## v3.6.0

* **[2024-04-26 16:40:51 CDT]** Boosted the requirements to PHP v7.4.
* **[2024-04-26 17:10:07 CDT]** Completely reimplemented serialization to support PHP 7.4's new __serialize() and for PHP 9.0 support.
* **[2024-04-26 17:02:39 CDT]** [m] PHPUnit will now display PHP deprecation notices.
* **[2024-04-26 17:06:54 CDT]** [m] Fixed the MyTypedPropertyTestDTO tests.
* **[2024-01-30 14:09:31 CDT]** Fixed nested DTOs where nested array property is an empty array. (#25)

## v3.5.0

* **[2023-07-22 22:40:34 CDT]** Added support for nullable concrete typed properties. HEAD -> v3.5, upstream/v3.5, origin/v3.5
* **[2023-07-22 22:23:23 CDT]** Added full tests for testing Permissive DTOs.
* **[2023-07-22 22:20:39 CDT]** ChatGPT-created unit tests.
* **[2023-07-21 04:58:57 CDT]** Fixed an edgecase where unserialized SimpleDTOs did not exactly match their original pre-serialized object.
* **[2023-07-21 04:58:04 CDT]** Fixed broken PHP 7.2 and 7.3 support.
* **[2023-07-21 04:56:30 CDT]** Throw a properly-detailed InvalidDataTypeException on malformed NestedDTO.
* **[2023-07-20 20:07:02 CDT]** Upgraded to PHPUnit v10 and created a system for supporting individual phpunit.xml for each PHPUnit version.
* **[2023-07-20 20:05:41 CDT]** Added a test runner for each version of PHP.

## v3.0.0

* **[2023-05-19 05:27:12 CST]** Added support for using PHP 7.4+ typed properties instead of docblocks.
* **[2023-02-18 04:05:30 CST]** Added a proper CHANGELOG.

## v2.6.0

* **[2022-01-28 20:53:26 CST]** Merge pull request #9 from andrew-demb/patch-1 HEAD -> master, tag: v2.6.0, upstream/master, upstream/HEAD, origin/master
* **[2022-01-28 20:49:47 CST]** Merge pull request #17 from open-source-contributions/test_enhancement
* **[2020-07-28 19:04:44 CDT]** 0d384ab N 2020-07-28 19:04:44 +0800 peter279k           Test enhancement
* **[2022-01-28 20:48:35 CST]** Merge pull request #16 from apolitano1/patch-1
* **[2020-05-04 07:25:49 CDT]** @CRLF in doc-block instead of strict @LF
* **[2022-01-28 20:45:12 CST]** Added more test code coverage.
* **[2022-01-28 20:27:40 CST]** Fixed phpstan errors up to level 6.
* **[2022-01-28 19:13:38 CST]** Use the latest phpexperts/dockerize.
* **[2022-01-28 19:12:22 CST]** Removed an extraneous dependency on the Laravel env polyfill.
* **[2022-01-28 18:52:46 CST]** Merge pull request #20 from ametad/fix-classes-with-_call-to-array
* **[2022-01-12 10:37:31 CST]** `toArray` also compatible with `__call` implementations
* **[2020-09-23 14:57:12 CDT]** [m] Migrated the project to Travis-CI.com.

## v2.5.0

* **[2020-09-22 11:16:05 CDT]** Upgraded to PHPUnit v9.0 and dropped support for PHP v7.1. tag: v2.5.0
* **[2020-04-08 12:56:45 CDT]** [m] PHP-CS-Fixer formatting changes.
* **[2020-04-08 12:46:35 CDT]** Shored up the code with some more testing.
* **[2020-04-08 12:13:09 CDT]** Fixed phpstan level 7 errors.
* **[2020-04-08 12:01:11 CDT]** Fixed phpstan level 6 errors.
* **[2020-04-08 11:44:55 CDT]** Fixed phpstan level 5 errors.
* **[2020-04-08 11:31:39 CDT]** Worked around the PHPCS PSR-12 changes.
* **[2020-04-08 11:10:28 CDT]** Fixed a PHP v7.1 bug.
* **[2020-04-08 11:02:55 CDT]** Removed the hack to get around a phpstan deficiency.
* **[2020-04-08 11:01:36 CDT]** Validate WriteOnce DTOs on toArray() and toSerialize().
* **[2020-04-08 11:00:09 CDT]** Fixed a composer Carbon dependency error.

## v2.4.5

* **[2019-09-24 08:46:48 CDT]** Merge pull request #15 from marcustrichel/marcus/FixIssue tag: v2.4.5
* **[2019-09-23 14:28:42 CDT]** Fixed issue #14

## v2.4.4

* **[2019-09-14 13:49:48 CDT]** (#14) Fixed a small bug. tag: v2.4.4

## v2.4.3

* **[2019-07-29 01:53:23 CDT]** Merge pull request #12 from hopeseekr/better_arrays tag: v2.4.3
* **[2019-07-29 01:49:00 CDT]** [m] Small code format fixes for PSR-12 compliance. origin/better_arrays, better_arrays
* **[2019-07-29 01:22:39 CDT]** Relaxed the CodeClimate restraint on # of returns.
* **[2019-07-29 01:00:35 CDT]** Fixed a whole range of recursive array bugs.
* **[2019-07-28 19:10:50 CDT]** Fully recursively convert objects to arrays via toArray().
* **[2019-07-28 18:23:38 CDT]** Refactored and fixed the handling of arrays of DTOs, passed as stdClasses.
* **[2019-07-28 14:22:14 CDT]** Added the missing `class_uses_recursive()` dependency.

## v2.4.2

* **[2019-07-08 22:19:52 CDT]** I don't know what's best anymore. :-( tag: v2.4.2
* **[2019-07-05 13:24:19 CDT]** typo in example
* **[2019-07-04 01:11:07 CDT]** Better validation report.

## v2.4.1

* **[2019-07-03 16:34:39 CDT]** Functionality to get the internal data.

## v2.4.0

* **[2019-07-03 10:42:55 CDT]** Now validates NestedDTOs, too. tag: v2.4.0
* **[2019-07-03 06:17:04 CDT]** Merge pull request #8 from hopeseekr/validation
* **[2019-07-03 05:17:19 CDT]** Added the ability to validate WriteOnce DTOs on output. origin/validation, validation

## v2.3.2

* **[2019-06-03 08:48:25 CDT]** Fixed a logic bug. tag: v2.3.2
* **[2019-06-02 23:52:56 CDT]** Better handling for WriteOnce DTOs.
* **[2019-06-02 23:52:56 CDT]** Better handling for WriteOnce DTOs.

## v2.3.1

* **[2019-06-01 11:41:44 CDT]** Updated the documentation. tag: v2.3.1
* **[2019-06-01 11:37:11 CDT]** Added a .gitattributes.
* **[2019-06-01 11:35:37 CDT]** Merge pull request #7 from hopeseekr/phpcs
* **[2019-06-01 11:29:58 CDT]** Got the tests to PSR1. origin/phpcs
* **[2019-06-01 11:26:27 CDT]** Got the code to the PSR12 standard.
* **[2019-06-01 10:11:37 CDT]** [m] Fixed the project link in the license headers.
* **[2019-06-01 10:10:43 CDT]** Added an example AgeDTO.
* **[2019-06-01 11:14:35 CDT]** Installed phpcs.
* **[2019-06-01 11:09:24 CDT]** Merge pull request #6 from hopeseekr/nested_dtos.v2
* **[2019-06-01 10:12:57 CDT]** [m] Stop PHPUnit on failures. origin/nested_dtos.v2
* **[2019-06-01 10:09:54 CDT]** Added even better support for Nested DTOs.
* **[2019-05-25 17:44:35 CDT]** Added better support for Nested DTOs.
* **[2019-05-25 13:21:24 CDT]** Added support for extra validation.

## v2.3.0

* **[2019-05-24 11:19:31 CDT]** Merge pull request #5 from hopeseekr/write-once-dto tag: v2.3.0
* **[2019-05-24 08:28:12 CDT]** Added support for Write-Once DTOs. origin/write-once-dto
* **[2019-05-20 14:02:22 CDT]** [m] Fixed a typo.

## v2.2.0

* **[2019-05-20 13:56:04 CDT]** Merge pull request #4 from hopeseekr/serialization tag: v2.2.0
* **[2019-05-20 13:50:30 CDT]** Added the ability to serialize NestedDTOs. origin/serialization, serialization
* **[2019-05-20 13:46:16 CDT]** Added the ability to serialize SimpleDTOs.

## v2.1.0

* **[2019-05-20 03:17:48 CDT]** Merge pull request #3 from hopeseekr/nested_dtos tag: v2.1.0
* **[2019-05-20 03:06:34 CDT]** Code review changes. origin/nested_dtos, nested_dtos
* **[2019-05-20 02:40:34 CDT]** Properly fleshed out Nested DTOs.
* **[2019-05-19 21:27:44 CDT]** Added the ability to nest DTOs.

## v2.0.1

* **[2019-05-17 08:29:06 CDT]** Merge pull request #2 from hopeseekr/better_nullables tag: v2.0.1
* **[2019-05-17 08:19:30 CDT]** Added better support for nullable properties. origin/better_nullables, better_nullables
* **[2019-05-17 07:20:53 CDT]** Fixed a regression where non-null properties weren't required.
* **[2019-05-13 08:42:01 CDT]** Fixed the README formatting.

## v2.0.0

* **[2019-05-12 21:50:24 CDT]** Merge pull request #1 from hopeseekr/version_2 tag: v2.0.0
* **[2019-05-12 21:39:37 CDT]** Fixed every phpstan issue.
* **[2019-05-12 18:46:47 CDT]** Code review changes. version_2
* **[2019-05-12 18:37:55 CDT]** Added instructions for Fuzzy types.
* **[2019-05-12 18:24:49 CDT]** Dramatically refactored the project to enforce property data types.

## v1.0.1

* **[2019-04-19 07:11:04 CDT]** Updated composer dependencies. tag: v1.0.1, upstream/v1.0, origin/v1.0

## v1.0.0

* **[2019-03-28 21:57:14 CDT]** Fixed the formatting of the README. tag: v1.0.0
* **[2019-03-28 11:09:45 CDT]** Removed the composer.lock for PHP v7.1 support.
* **[2019-03-28 10:35:33 CDT]** Fixed the README.
* **[2019-03-28 09:48:35 CDT]** Downgraded to PHPUnit 7.0 for PHP v7.1 support.

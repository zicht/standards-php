# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security
- Nothing so far

## 4.1.5 - 2021-03-02
### Fixed
- String offset access syntax with curly braces is deprecated in PHP 7.4

## 4.1.4 - 2021-02-26
### Changed
- Changed property comment sniff to allow dash in var type and allow mixed array key type
### Added
- Q&A through GitHub Actions

## 4.1.3 - 2020-09-21
### Fixed
- Fixed PropertyCommentSniff to detect PHP 7.4 property type definitions and do not make @var required
  when a type is defined.

## 4.1.2 - 2020-05-13
### Fixed
- Allow numbers in classnames to prevent errors on Symfony Migration Version20200506070809 classes

## 4.1.1 - 2019-07-15
### Fixed
- Fixes superflous description for constructor when class name would contain "New"
### Added
- Minor optimization: end by return when the description would only be "{@inheritDoc}"
- Better superflous description detection by splitting up the class/method names into separate words
- Merged v3.5.1: Loosen the Zicht.NamingConventions.Constants.InvalidName sniff

## 4.1.0 - 2019-06-17
### Changed in Sniffs
- Changed Zicht.Commenting.FunctionComment to only allow {@inheritDoc} instead
  of {@inheritdoc} (camelcase instead of lowercase)

## 4.0.1 - 2019-03-29
### Changed in Sniffs
- Generic.Files.LineLength configuration changes: soft limit is now 256 chars
  (warning), hard limit is now 512 charachters (error)

## 4.0.0 - 2019-02-14 💕
### Changed in Sniffs
- Generic.Files.LineLength is enforced
- Changed long lines in existing code
- Change warnings into errors now that warnings will be ignored
### Changed in package
- PHP files were moved into the src/ dir
- Created a phpcs.xml file that can be used with some configs and the
  reference to the old ruleset.xml
- Created a phpcs-dev.xml file to include PHPCompatibility
- Updated the README.md file Usage section
- Remove incorrect.php and correct.php

## 3.5.1 - 2019-07-15
### Changed
- Loosen the Zicht.NamingConventions.Constants.InvalidName sniff

## 3.5.0 - 2019-02-12
### Added
- Script to replace incorrect Zicht Online @copyright tags with correct ones
  (and corrected all in this package) (resolving #55)
- Enforce comma after last array item sniff (resolving #8)
- Assignments in control structures sniff (resolving #5)
- Class property comment sniff (resolving #22)
- Added this CHANGELOG.md

## < 3.5.0
No changelog was kept before version 3.5.0


# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security
Nothing so far 

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


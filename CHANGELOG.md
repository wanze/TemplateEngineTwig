# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2018-03-19

### Changed
* Update twig to latest version `1.35.2`.
* Define this repository as ProcessWire module in `composer.json` so it can be installed via composer ([more information](http://harikt.com/blog/2013/11/16/composer-support-for-processwire-modules/)).

### Fixed
* Fix autoloader deprecation message - by @lesaff.
* Make sure that some configuration settings are correctly passed as booleans to twig - by @nextgensparx

[1.1.0]: https://github.com/wanze/TemplateEngineTwig/releases/tag/v1.1.0
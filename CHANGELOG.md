# Changelog

## [Unreleased]

## [2.0.0] - 2018-02-13

* Initial release of the new major `2.x` version 🐣

### Added

* Add a setting to enable debug mode, independently from ProcessWire's debug mode.  
* The `\Twig_Extension_Debug` is automatically added if debug mode is active.
* Add unit tests 🎉

### Changed

* The Twig dependencies are no longer part of this repository. Installation is therefore only
possible with Composer, no longer via ProcessWire modules directory. 
* Update Twig to version `2.6.2`. Because of that, PHP > `7.x` is required.

## [1.1.0] - 2018-03-19

### Changed
* Update twig to latest version `1.35.2`.
* Define this repository as ProcessWire module in `composer.json` so it can be installed via composer
([more information](http://harikt.com/blog/2013/11/16/composer-support-for-processwire-modules/)).

### Fixed
* Fix autoloader deprecation message - by @lesaff.
* Make sure that some configuration settings are correctly passed as booleans to twig - by @nextgensparx

[2.0.0]: https://github.com/wanze/TemplateEngineTwig/releases/tag/v1.1.0
[1.1.0]: https://github.com/wanze/TemplateEngineTwig/releases/tag/v2.0.0
[Unreleased]: https://github.com/wanze/TemplateEngineTwig/compare/v2.0.0...HEAD

# Changelog

## [Unreleased]


## [3.0.0] - 2019-02-01

* Update Twig to version `3.x` - thanks @porl (see [#23](https://github.com/wanze/TemplateEngineTwig/pull/23))

Twig 3 uses namespaced classes and requires PHP `^7.2.5`. Make sure to test your installation after updating,
especially if you use hooks to customize Twig.

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

[3.0.0]: https://github.com/wanze/TemplateEngineTwig/releases/tag/v3.0.0
[2.0.0]: https://github.com/wanze/TemplateEngineTwig/releases/tag/v2.0.0
[1.1.0]: https://github.com/wanze/TemplateEngineTwig/releases/tag/v1.1.0
[Unreleased]: https://github.com/wanze/TemplateEngineTwig/compare/v3.0.0...HEAD

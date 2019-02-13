# TemplateEngineTwig

[![Build Status](https://travis-ci.org/wanze/TemplateEngineTwig.svg?branch=master)](https://travis-ci.org/wanze/TemplateEngineTwig)
[![StyleCI](https://github.styleci.io/repos/21304492/shield?branch=master)](https://github.styleci.io/repos/21304492)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![ProcessWire 3](https://img.shields.io/badge/ProcessWire-3.x-orange.svg)](https://github.com/processwire/processwire)

A ProcessWire module adding Twig to the [TemplateEngineFactory](https://github.com/wanze/TemplateEngineFactory).

## Requirements

* ProcessWire `3.0` or newer
* TemplateEngineFactory `2.0` or newer
* PHP `7.0` or newer
* Composer

> The `1.x` version of this module is available on the [1.x branch](https://github.com/wanze/TemplateEngineTwig/tree/1.x).
Use this version if you still use _TemplateEngineFactory_ `1.x`.  

## Installation

Execute the following command in the root directory of your ProcessWire installation:

```
composer require wanze/template-engine-twig:^2.0
```

This will install the _TemplateEngineTwig_ and _TemplateEngineFactory_ modules in one step. Afterwards, don't forget 
to enable Twig as engine in the _TemplateEngineFactory_ module's configuration.

> ℹ️ This module includes test dependencies. If you are installing on production with `composer install`, make sure to
pass the `--no-dev` flag to omit autoloading any unnecessary test dependencies!.

## Configuration

The module offers the following configuration:

* **`Template files suffix`** The suffix of the Twig template files, defaults to `twig.html`.
* **`Provide ProcessWire API variables in Twig templates`** API variables (`$pages`, `$input`, `$config`...)
are accessible in Twig,
e.g. `{{ config }}` for the config API variable.
* **`Debug`** If enabled, Twig outputs debug information. The module also registers the _Debug Extension_, offering
the `{{ dump() }}` function to inspect variables.  
* **`Auto reload templates (recompile)`** If enabled, templates are recompiled whenever the source code changes.
* **`Strict variables`** If set to `false`, Twig will silently ignore invalid variables (variables and
or attributes/methods that do not exist) and replace them with a `null` value. When set to `true`,
Twig throws an exception instead
* **`Auto escape variables`** If enabled, templates will auto-escape variables. If you are using ProcessWire
textformatters to escape field values, do not enable this feature.

## Extending Twig

It is possible to extend Twig after it has been initialized by the module. Hook the method `TemplateEngineTwig::initTwig`
to register custom functions, extensions, global variables, filters etc.

Here is an example how you can use the provided hook to attach a custom function.

```php
wire()->addHookAfter('TemplateEngineTwig::initTwig', function (HookEvent $event) {
    /** @var \Twig_Environment $twig */
    $twig = $event->arguments('twig');

    $twig->addFunction(new \Twig_Function('processwire', function () {
        return 'ProcessWire rocks!';
    }));
});

// ... and then use it anywhere in a Twig template:

{{ processwire() }}
```

> The above hook can be put in your `site/init.php` file. If you prefer to use modules, put it into the module's `init()`
method and make sure that the module is auto loaded.

### Use Twig Extensions

> The [Twig Extensions](https://twig-extensions.readthedocs.io/en/latest/) is a library that provides several useful
extensions for Twig.

These extensions are not included in the module by default, but you can add them to Twig using the same hook explained
above.

Fist, install the library with Composer in the ProcessWire root directory: `composer require twig/extensions`. Next,
use the same hook as above to register the desired extensions.

```php
wire()->addHookAfter('TemplateEngineTwig::initTwig', function (HookEvent $event) {
    /** @var \Twig_Environment $twig */
    $twig = $event->arguments('twig');
    
    // Register the extensions.
    $twig->addExtension(new Twig_Extensions_Extension_Text());
    $twig->addExtension(new Twig_Extensions_Extension_I18n());
    $twig->addExtension(new Twig_Extensions_Extension_Intl());
});
```

TemplateEngineTwig
==================

ProcessWire module adding Twig templates to the TemplateEngineFactory

## Installation
Install the module just like any other ProcessWire module. Check out the following guide: http://modules.processwire.com/install-uninstall/

This module requires TemplateEngineFactory: https://github.com/wanze/TemplateEngineFactory

After installing, don't forget to enable Twig as engine in the TemplateEngineFactory module's settings.

## Configuration
* **Path to templates** Path to folder where you want to store your Twig template files.
* **Template files suffix** The suffix of the template files, default is *html*.
* **Import ProcessWire API variables in Twig template** If checked, any API variable is accessible inside the Twig templates, for example *{{ page }}* refers to the current page.
* **Auto reload templates (recompile)** If enabled, templates are recompiled whenever the source code changes.
* **Auto escape variables** If enabled, templates will auto-escape variables. If you use ProcessWire's textformatter to escape variables, do not enable this feature.

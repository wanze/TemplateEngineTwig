<?php

namespace ProcessWire;

use TemplateEngineTwig\TemplateEngineTwig as TwigEngine;

/**
 * Adds Twig templates to the TemplateEngineFactory module.
 */
class TemplateEngineTwig extends WireData implements Module, ConfigurableModule
{
    /**
     * @var array
     */
    private static $defaultConfig = [
        'template_files_suffix' => 'html.twig',
        'api_vars_available' => 1,
        'auto_reload' => 1,
        'auto_escape' => 0,
        'strict_variables' => 0,
        'debug' => 'config',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->wire('classLoader')->addNamespace('TemplateEngineTwig', __DIR__ . '/src');
        $this->setDefaultConfig();
    }

    /**
     * @return array
     */
    public static function getModuleInfo()
    {
        return [
            'title' => 'Template Engine Twig',
            'summary' => 'Twig templates for the TemplateEngineFactory',
            'version' => 200,
            'author' => 'Stefan Wanzenried',
            'href' => 'https://processwire.com/talk/topic/6835-module-twig-for-the-templateenginefactory/',
            'singular' => true,
            'autoload' => true,
            'requires' => [
                'TemplateEngineFactory>=2.0.0',
                'PHP>=7.0',
                'ProcessWire>=3.0',
            ],
        ];
    }

    public function init()
    {
        /** @var \ProcessWire\TemplateEngineFactory $factory */
        $factory = $this->wire('modules')->get('TemplateEngineFactory');

        $factory->registerEngine('Twig', new TwigEngine($factory->getArray(), $this->getArray()));
    }

    private function setDefaultConfig()
    {
        foreach (self::$defaultConfig as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @param array $data
     *
     * @throws \ProcessWire\WireException
     * @throws \ProcessWire\WirePermissionException
     *
     * @return \ProcessWire\InputfieldWrapper
     */
    public static function getModuleConfigInputfields(array $data)
    {
        /** @var Modules $modules */
        $data = array_merge(self::$defaultConfig, $data);
        $wrapper = new InputfieldWrapper();
        $modules = wire('modules');

        /** @var \ProcessWire\InputfieldText $field */
        $field = $modules->get('InputfieldText');
        $field->label = __('Template files suffix');
        $field->name = 'template_files_suffix';
        $field->value = $data['template_files_suffix'];
        $field->required = 1;
        $wrapper->append($field);

        $field = $modules->get('InputfieldCheckbox');
        $field->label = __('Provide ProcessWire API variables in Twig templates');
        $field->description = __('API variables (`$pages`, `$input`, `$config`...) are accessible in Twig, e.g. `{{ config }}` for the config API variable.');
        $field->name = 'api_vars_available';
        $field->checked = (bool) $data['api_vars_available'];
        $wrapper->append($field);

        /** @var \ProcessWire\InputfieldSelect $field */
        $field = $modules->get('InputfieldSelect');
        $field->label = __('Debug');
        $field->name = 'debug';
        $field->addOptions([
            'config' => __('Inherit from ProcessWire'),
            0 => __('No'),
            1 => __('Yes'),
        ]);
        $field->value = $data['debug'];
        $wrapper->append($field);

        /** @var \ProcessWire\InputfieldCheckbox $field */
        $field = $modules->get('InputfieldCheckbox');
        $field->label = __('Auto reload templates (recompile)');
        $field->description = __('If enabled, templates are recompiled whenever the source code changes');
        $field->name = 'auto_reload';
        $field->checked = (bool) $data['auto_reload'];
        $wrapper->append($field);

        $field = $modules->get('InputfieldCheckbox');
        $field->label = __('Strict variables');
        $field->description = __('If set to `false`, Twig will silently ignore invalid variables (variables and or attributes/methods that do not exist) and replace them with a `null` value. When set to `true`, Twig throws an exception instead');
        $field->name = 'strict_variables';
        $field->checked = (bool) $data['strict_variables'];
        $wrapper->append($field);

        $field = $modules->get('InputfieldCheckbox');
        $field->label = __('Auto escape variables');
        $field->description = __('If enabled, templates will auto-escape variables. If you are using ProcessWire textformatters to escape field values, do not enable this feature.');
        $field->name = 'auto_escape';
        $field->checked = (bool) $data['auto_escape'];
        $wrapper->append($field);

        return $wrapper;
    }
}

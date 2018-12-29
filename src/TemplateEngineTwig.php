<?php

namespace TemplateEngineTwig;

use TemplateEngineFactory\TemplateEngineBase;

/**
 * Provides the Twig template engine.
 */
class TemplateEngineTwig extends TemplateEngineBase
{
    const COMPILE_DIR = 'TemplateEngineTwig_compile/';

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * {@inheritdoc}
     */
    public function render($template, $data = [])
    {
        $template = $this->normalizeTemplate($template);
        $data = $this->getData($data);

        return $this->getTwig()->render($template, $data);
    }

    /**
     * @throws \ProcessWire\WireException
     *
     * @return \Twig_Environment
     */
    protected function getTwig()
    {
        if ($this->twig === null) {
            return $this->buildTwig();
        }

        return $this->twig;
    }

    /**
     * @throws \ProcessWire\WireException
     *
     * @return \Twig_Environment
     */
    protected function buildTwig()
    {
        $loader = new \Twig_Loader_Filesystem($this->getTemplatesRootPath());

        $this->twig = new \Twig_Environment($loader, [
            'cache' => $this->wire('config')->paths->assets . 'cache/' . self::COMPILE_DIR,
            'debug' => $this->isDebug(),
            'auto_reload' => (bool) $this->moduleConfig['auto_reload'],
            'autoescape' => $this->moduleConfig['auto_escape'] ? 'name' : false,
            'strict_variables' => (bool) $this->moduleConfig['strict_variables'],
        ]);

        // Add the debug extension offering the "dump()" function for variables.
        if ($this->isDebug()) {
            $this->twig->addExtension(new \Twig_Extension_Debug());
        }

        $this->initTwig($this->twig);

        return $this->twig;
    }

    /**
     * Hookable method called after Twig has been initialized.
     *
     * Use this method to customize the passed $twig instance,
     * e.g. adding functions and filters.
     *
     * @param \Twig_Environment $twig
     */
    protected function ___initTwig(\Twig_Environment $twig)
    {
    }

    private function isDebug()
    {
        if ($this->moduleConfig['debug'] === 'config') {
            return $this->wire('config')->debug;
        }

        return (bool) $this->moduleConfig['debug'];
    }

    /**
     * @param array $data
     *
     * @throws \ProcessWire\WireException
     *
     * @return array
     */
    private function getData(array $data)
    {
        if (!$this->moduleConfig['api_vars_available']) {
            return $data;
        }

        foreach ($this->wire('all') as $name => $object) {
            $data[$name] = $object;
        }

        return $data;
    }

    /**
     * Normalize the given template by adding the template files suffix.
     *
     * @param string $template
     *
     * @return string
     */
    private function normalizeTemplate($template)
    {
        $suffix = $this->moduleConfig['template_files_suffix'];

        $normalizedTemplate = ltrim($template, DIRECTORY_SEPARATOR);

        if (!preg_match("/\.${suffix}$/", $template)) {
            return $normalizedTemplate . sprintf('.%s', $suffix);
        }

        return $normalizedTemplate;
    }
}

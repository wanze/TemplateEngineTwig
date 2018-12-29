<?php

namespace TemplateEngineTwig\Test;

use PHPUnit\Framework\TestCase;
use ProcessWire\HookEvent;
use ProcessWire\ProcessWire;
use TemplateEngineTwig\TemplateEngineTwig;
use Twig_Error_Loader;

class TemplateEngineTwigTest extends TestCase
{
    /**
     * @var \ProcessWire\ProcessWire
     */
    private $wire;

    protected function setUp()
    {
        $this->bootstrapProcessWire();
        $this->fakeSitePath();
    }

    protected function tearDown()
    {
        ProcessWire::removeInstance($this->wire);
    }

    /**
     * @dataProvider initializeTwigDataProvider
     */
    public function test_twig_initialized_correctly(array $moduleConfig, $autoReload, $strictVars, $debug, $wireDebug)
    {
        $this->wire->wire('config')->debug = $wireDebug;

        $this->wire->addHookAfter('TemplateEngineTwig::initTwig',
            function (HookEvent $event) use ($autoReload, $strictVars, $debug) {
                /** @var \Twig_Environment $twig */
                $twig = $event->arguments('twig');

                $this->assertEquals($debug, $twig->isDebug());
                $this->assertEquals($autoReload, $twig->isAutoReload());
                $this->assertEquals($strictVars, $twig->isStrictVariables());
            });

        $engine = $this->getTwigEngine($moduleConfig);

        $engine->render('dummy');
    }

    public function test_hook_initTwig()
    {
        $this->wire->addHookAfter('TemplateEngineTwig::initTwig',
            function (HookEvent $event) {
                /** @var \Twig_Environment $twig */
                $twig = $event->arguments('twig');

                $twig->addFunction(new \Twig_Function('processwire', function () {
                    return 'ProcessWire rocks!';
                }));
            });

        $engine = $this->getTwigEngine();

        $this->assertEquals("ProcessWire rocks!\n", $engine->render('test_custom_function'));
    }

    public function test_missing_template_throws_exception()
    {
        $engine = $this->getTwigEngine();

        $this->expectException(Twig_Error_Loader::class);

        $engine->render('this/template/does/not/exist');
    }

    public function test_rendering_with_or_without_templates_suffix()
    {
        $engine = $this->getTwigEngine();

        $this->assertEquals($engine->render('dummy'), $engine->render('dummy.html.twig'));
    }

    public function test_rendering_api_variables_available()
    {
        $engine = $this->getTwigEngine();

        $this->assertEquals("API variables available.", $engine->render('test_api_vars'));
    }

    public function test_rendering_template_in_subfolder()
    {
        $engine = $this->getTwigEngine();

        $this->assertEquals("Dummy in subfolder!\n", $engine->render('subfolder/dummy'));
    }

    public function test_rendering_custom_template_files_suffix()
    {
        $engine = $this->getTwigEngine(['template_files_suffix' => 'processwire.twig']);

        $this->assertEquals("ProcessWire!\n", $engine->render('test_custom_suffix'));
        $this->assertEquals("ProcessWire!\n", $engine->render('test_custom_suffix.processwire.twig'));
    }

    public function test_rendering_data()
    {
        $engine = $this->getTwigEngine();

        $data = [
            'Breaking Bad',
            'Sons of Anarchy',
            'Big Bang Theory',
        ];

        $this->assertEquals(implode(',', $data) . "\n", $engine->render('test_data', ['data' => $data]));
    }

    /**
     * @return array
     */
    public function initializeTwigDataProvider()
    {
        return [
            [
                [
                    'template_files_suffix' => 'html.twig',
                    'api_vars_available' => 1,
                    'auto_reload' => 1,
                    'auto_escape' => 0,
                    'strict_variables' => 0,
                    'debug' => 'config',
                ],
                true,   // auto reload
                false,  // strict variables
                false,  // debug
                false,  // $config->debug
            ],
            [
                [
                    'template_files_suffix' => 'html.twig',
                    'api_vars_available' => 1,
                    'auto_reload' => 1,
                    'auto_escape' => 0,
                    'strict_variables' => 0,
                    'debug' => 'config',
                ],
                true,   // auto reload
                false,  // strict variables
                true,   // debug
                true,   // $config->debug
            ],
            [
                [
                    'template_files_suffix' => 'html.twig',
                    'api_vars_available' => 1,
                    'auto_reload' => 0,
                    'auto_escape' => 0,
                    'strict_variables' => 1,
                    'debug' => 0,
                ],
                false,
                true,
                false,
                true,
            ],
            [
                [
                    'template_files_suffix' => 'html.twig',
                    'api_vars_available' => 1,
                    'auto_reload' => 0,
                    'auto_escape' => 0,
                    'strict_variables' => 1,
                    'debug' => 1,
                ],
                false,
                true,
                true,
                false,
            ]
        ];
    }

    /**
     * Let $config->paths->site point to this directory.
     */
    private function fakeSitePath()
    {
        $paths = $this->wire->wire('config')->paths;
        $paths->set('site', 'site/modules/TemplateEngineTwig/tests/');
    }

    /**
     * @param array $factoryConfig
     * @param array $moduleConfig
     *
     * @return \TemplateEngineTwig\TemplateEngineTwig
     */
    private function getTwigEngine(array $moduleConfig = [], array $factoryConfig = [])
    {
        $factoryConfig = array_merge($this->getFactoryConfig(), $factoryConfig);
        $moduleConfig = array_merge($this->getModuleConfig(), $moduleConfig);

        return new TemplateEngineTwig($factoryConfig, $moduleConfig);
    }

    private function getModuleConfig()
    {
        return [
            'template_files_suffix' => 'html.twig',
            'api_vars_available' => 1,
            'auto_reload' => 1,
            'auto_escape' => 0,
            'strict_variables' => 0,
            'debug' => 'config',
        ];
    }

    private function getFactoryConfig()
    {
        return $this->wire->wire('modules')->get('TemplateEngineFactory')->getArray();
    }

    private function bootstrapProcessWire()
    {
        $rootPath = __DIR__ . '../../../../../';
        $config = ProcessWire::buildConfig($rootPath);
        $this->wire = new ProcessWire($config);
    }
}

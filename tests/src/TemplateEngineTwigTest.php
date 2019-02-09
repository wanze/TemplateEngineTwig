<?php

namespace TemplateEngineTwig\Test;

use PHPUnit\Framework\TestCase;
use ProcessWire\HookEvent;
use ProcessWire\ProcessWire;
use TemplateEngineTwig\TemplateEngineTwig;
use Twig_Error_Loader;

/**
 * Tests for the TemplateEngineTwig class.
 *
 * @coversDefaultClass \TemplateEngineTwig\TemplateEngineTwig
 *
 * @group TemplateEngineTwig
 */
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
    public function testInitTwig_DifferentConfiguration_InitializedCorrectly(array $moduleConfig, $autoReload, $strictVars, $debug, $wireDebug)
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

        // Twig is only initialized when we actually render something.
        $engine->render('dummy');
    }

    public function testInitTwig_AddCustomFunctionToTwig_FunctionIsAvailableInTemplate()
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

    /**
     * @covers ::render
     */
    public function testRender_MissingTemplate_ThrowsException()
    {
        $engine = $this->getTwigEngine();

        $this->expectException(Twig_Error_Loader::class);

        $engine->render('this/template/does/not/exist');
    }

    /**
     * @covers ::render
     */
    public function testRender_TemplateWithOrWithoutSuffix_TemplatesFoundAndSameOutput()
    {
        $engine = $this->getTwigEngine();

        $this->assertEquals("Dummy\n", $engine->render('dummy'));
        $this->assertEquals("Dummy\n", $engine->render('dummy.html.twig'));
    }

    /**
     * @covers ::render
     */
    public function testRender_ApiVariablesEnabledOrDisabled_ApiVariablesAvailableInTemplate()
    {
        $engine = $this->getTwigEngine();
        $engine2 = $this->getTwigEngine(['api_vars_available' => false]);

        $this->assertEquals("API variables available.", $engine->render('test_api_vars'));
        $this->assertNotEquals("API variables available.", $engine2->render('test_api_vars'));
    }

    /**
     * @covers ::render
     */
    public function testRender_TemplateInSubfolder_TemplateFoundAndRenderedCorrectly()
    {
        $engine = $this->getTwigEngine();

        $this->assertEquals("Dummy in subfolder!\n", $engine->render('subfolder/dummy'));
    }

    /**
     * @covers ::render
     */
    public function testRender_CustomTemplateFilesSuffix_TemplateFoundAndRenderedCorrectly()
    {
        $engine = $this->getTwigEngine(['template_files_suffix' => 'processwire.twig']);

        $this->assertEquals("ProcessWire!\n", $engine->render('test_custom_suffix'));
        $this->assertEquals("ProcessWire!\n", $engine->render('test_custom_suffix.processwire.twig'));
    }

    /**
     * @covers ::render
     */
    public function testRender_PassingDataToTemplate_DataAvailableInTemplateAndRenderedCorrectly()
    {
        $engine = $this->getTwigEngine();

        $series = [
            'Breaking Bad',
            'Sons of Anarchy',
            'Big Bang Theory',
        ];

        // The series are rendered comma separated -> {{ series|join(',') }}.
        $expected = sprintf("%s\n", implode(',', $series));

        $this->assertEquals($expected, $engine->render('test_data', ['series' => $series]));
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
     *
     * This allows to render test twig templates under /templates/views.
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
        $rootPath = __DIR__ . '../../../../../../';
        $config = ProcessWire::buildConfig($rootPath);
        $this->wire = new ProcessWire($config);
    }
}

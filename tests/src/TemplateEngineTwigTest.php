<?php

namespace TemplateEngineTwig\Test;

use ProcessWire\HookEvent;
use TemplateEngineTwig\TemplateEngineTwig;
use Twig_Error_Loader;

/**
 * Tests for the TemplateEngineTwig class.
 *
 * @coversDefaultClass \TemplateEngineTwig\TemplateEngineTwig
 *
 * @group TemplateEngineTwig
 */
class TemplateEngineTwigTest extends ProcessWireTestCaseBase
{
    protected function setUp()
    {
        parent::setUp();

        $this->fakePath('site', 'site/modules/TemplateEngineTwig/tests/');
    }

    /**
     * @test
     * @dataProvider initializeTwigDataProvider
     */
    public function it_should_initialize_twig_correctly_depending_on_module_configuration(array $moduleConfig, $autoReload, $strictVars, $debug, $wireDebug)
    {
        $this->wire('config')->debug = $wireDebug;

        $this->addHookAfter('TemplateEngineTwig::initTwig',
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

    /**
     * @test
     */
    public function it_should_be_possible_to_extend_twig()
    {
        $this->addHookAfter('TemplateEngineTwig::initTwig',
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
     * @test
     * @covers ::render
     */
    public function it_should_throw_an_exception_if_a_template_does_not_exist()
    {
        $engine = $this->getTwigEngine();

        $this->expectException(Twig_Error_Loader::class);

        $engine->render('this/template/does/not/exist');
    }

    /**
     * @test
     * @covers ::render
     */
    public function it_should_find_templates_with_or_without_suffix_and_render_the_same_output()
    {
        $engine = $this->getTwigEngine();

        $this->assertEquals("Dummy\n", $engine->render('dummy'));
        $this->assertEquals("Dummy\n", $engine->render('dummy.html.twig'));
    }

    /**
     * @test
     * @covers ::render
     */
    public function it_should_only_provide_the_api_variables_if_enabled_in_config()
    {
        $engine = $this->getTwigEngine();
        $engine2 = $this->getTwigEngine(['api_vars_available' => false]);

        $this->assertEquals("API variables available.", $engine->render('test_api_vars'));
        $this->assertNotEquals("API variables available.", $engine2->render('test_api_vars'));
    }

    /**
     * @test
     * @covers ::render
     */
    public function it_should_find_and_render_a_template_in_a_subfolder()
    {
        $engine = $this->getTwigEngine();

        $this->assertEquals("Dummy in subfolder!\n", $engine->render('subfolder/dummy'));
    }

    /**
     * @test
     * @covers ::render
     */
    public function it_should_find_and_render_a_template_with_a_custom_suffix()
    {
        $engine = $this->getTwigEngine(['template_files_suffix' => 'processwire.twig']);

        $this->assertEquals("ProcessWire!\n", $engine->render('test_custom_suffix'));
        $this->assertEquals("ProcessWire!\n", $engine->render('test_custom_suffix.processwire.twig'));
    }

    /**
     * @test
     * @covers ::render
     */
    public function it_should_pass_data_to_the_template()
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
     * @param array $factoryConfig
     * @param array $moduleConfig
     *
     * @return \TemplateEngineTwig\TemplateEngineTwig
     */
    private function getTwigEngine(array $moduleConfig = [], array $factoryConfig = [])
    {
        $factoryConfig = array_merge($this->getFactoryConfig(), $factoryConfig);
        $moduleConfig = array_merge($this->getModuleConfig(), $moduleConfig);

        return $this->wire(new TemplateEngineTwig($factoryConfig, $moduleConfig));
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
        return $this->wire('modules')->get('TemplateEngineFactory')
            ->getArray();
    }
}

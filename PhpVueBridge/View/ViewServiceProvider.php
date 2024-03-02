<?php


namespace PhpVueBridge\View;

use PhpVueBridge\View\Engines\PhPEngine;
use PhpVueBridge\View\Engines\VueEngine;
use PhpVueBridge\Bedrock\ServiceProvider;

use PhpVueBridge\View\Compilers\VueCompiler;
use PhpVueBridge\View\Compilers\ViewCompiler;
use PhpVueBridge\View\Engines\CompilerEngine;
use PhpVueBridge\View\Engines\EngineResolver;
use PhpVueBridge\View\Compilers\CompilerFactory;

class ViewServiceProvider extends ServiceProvider
{

    public function register(): void
    {

        $this->app->bindShared('view', function ($app) {

            $resolver = $app->resolve('engine');

            $fileFinder = $app->resolve('view.finder');

            $factory = new ViewFactory(
                $app,
                $resolver,
                $fileFinder
            );

            $factory->share('_app', $app);

            return $factory;
        });

        $this->app->bindShared('compiler', function ($app) {
            $factory = new CompilerFactory(
                $app->getBasePath(),
                config('view.compiled_path'),
                config('view.compiled_extension')
            );

            foreach ([
                'vue.phtml' => 'vue',
                'phtml' => 'view',
            ] as $extension => $compiler) {
                $factory->registerCompiler(
                    $extension,
                    $compiler,
                    $this->{'register' . ucfirst($compiler) . 'Compiler'}()
                );
            }

            return $factory;
        });

        $this->app->bindShared('view.finder', function ($app) {
            return new ViewFinder(
                $app,
                config('view.paths', [])
            );
        });

        $this->app->bindShared('engine', function ($app) {

            $engineResolver = new EngineResolver();

            foreach (['vue', 'file', 'md', 'compiler'] as $engine) {
                $engineResolver->register(
                    $engine,
                    $this->{'get' . ucfirst($engine) . 'Engine'}(),
                );
            }

            return $engineResolver;
        });
    }


    protected function getVueEngine()
    {
        return fn () => new VueEngine($this->app->resolve('compiler'));
    }

    protected function getFileEngine()
    {
        return fn () => new PhPEngine;
    }

    protected function getMdEngine()
    {
        return fn () => new PhPEngine;
    }

    protected function getCompilerEngine()
    {
        return fn () => new CompilerEngine($this->app->resolve('compiler'));
    }

    protected function registerVueCompiler()
    {
        return fn ($factory) => new VueCompiler($factory);
    }

    protected function registerViewCompiler()
    {
        return fn ($factory) => new ViewCompiler($factory);
    }
}

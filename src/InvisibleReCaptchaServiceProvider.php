<?php

namespace AlbertCht\InvisibleReCaptcha;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class InvisibleReCaptchaServiceProvider extends ServiceProvider
{
    /**
     * Boot the services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootConfig();
        $this->app['validator']->extend('reCaptcha', function ($attribute, $value) {
            return $this->app['reCaptcha']->verifyResponse($value, $this->app['request']->getClientIp());
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('reCaptcha', function ($app) {
            return new InvisibleReCaptcha(
                $app['config']['reCaptcha.siteKey'],
                $app['config']['reCaptcha.secretKey'],
                $app['config']['reCaptcha.options']
            );
        });

        $this->app->afterResolving('blade.compiler', function () {
            $this->addBladeDirective($this->app['blade.compiler']);
        });
    }

    /**
     * Boot configure.
     *
     * @return void
     */
    protected function bootConfig()
    {
        $path = __DIR__.'/config/captcha.php';

        $this->mergeConfigFrom($path, 'reCaptcha');

        if (function_exists('config_path')) {
            $this->publishes([$path => config_path('captcha.php')]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['reCaptcha'];
    }

    /**
     * @param BladeCompiler $blade
     * @return void
     */
    public function addBladeDirective(BladeCompiler $blade)
    {
        $blade->directive('reCaptcha', function ($lang) {
            return "<?php echo app('reCaptcha')->render({$lang}); ?>";
        });
    }
}

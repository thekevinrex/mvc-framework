<?php


namespace app\core\bridge;


use app\Core\Support\ServiceProvider;

class BridgeServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bindShared('bridge', function ($app) {
            return new Bridge($app);
        });

        $this->app->bindShared('bridge_seo', function ($app) {
            return $app->resolve('bridge')->head();
        });

        $this->app->resolve('compiler')->directive('bridge', [$this, 'resolveBridgeContent']);
    }

    public function resolveBridgeContent($matches)
    {
        $app = trim($matches[2]) ?: 'app';

        $bridgeData = [
            'html' => 'htmlentities(json_encode($bridgeContent), ENT_QUOTES, \'UTF-8\')',
            'components' => 'htmlentities(json_encode($components), ENT_QUOTES, \'UTF-8\')',
            'events' => 'htmlentities(json_encode($events), ENT_QUOTES, \'UTF-8\')',
            'options' => 'htmlentities(json_encode($options), ENT_QUOTES, \'UTF-8\')',
        ];

        return implode(
            PHP_EOL,
            array(
                '<div id="' . $app . '" ' . $this->resolveBridgeData($bridgeData) . '>',
                "<?php echo \$bridgeContent ?>",
                "</div>",
            )
        );
    }

    protected function resolveBridgeData($data)
    {

        $data = array_map(function ($key) use ($data) {
            return 'data-' . $key . '="<?php echo ' . $data[$key] . ' ?>"';
        }, array_keys($data));

        return implode(' ', $data);
    }

}
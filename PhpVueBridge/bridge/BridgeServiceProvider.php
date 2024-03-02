<?php


namespace PhpVueBridge\Bridge;

use PhpVueBridge\Bedrock\ServiceProvider;


class BridgeServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bindShared('bridge', function ($app) {
            return new Bridge($app);
        });

        $this->app->bindShared('bridge_seo', function ($app) {
            return $app->resolve('bridge')->head();
        });

        $this->app->resolve('compiler')->directive('bridge', [$this, 'resolveBridgeContent']);
    }

    public function resolveBridgeContent(array $directive)
    {
        $app = trim($directive[2]) ?: 'app';

        $bridgeData = [
            'html' => 'e(json_encode($bridgeContent), ENT_QUOTES, \'UTF-8\')',
            'components' => 'e(json_encode($components), ENT_QUOTES, \'UTF-8\')',
            'events' => 'e(json_encode($events), ENT_QUOTES, \'UTF-8\')',
            'options' => 'e(json_encode($options), ENT_QUOTES, \'UTF-8\')',
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

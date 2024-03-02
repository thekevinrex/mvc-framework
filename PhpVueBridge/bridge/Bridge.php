<?php


namespace PhpVueBridge\Bridge;

use PhpVueBridge\Bridge\Utils\Vite;
use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Bridge\Utils\AssetLoader;
use PhpVueBridge\Bridge\Contracts\BridgeContract;
use PhpVueBridge\View\Compilers\Managers\compileEchos;

class Bridge implements BridgeContract {

    public const BRIDGE_HEADER = 'X_BRIDGE';

    protected Vite $server;

    protected AssetLoader $assets;

    protected BridgeHandler $handler;

    protected BridgeSeo $head;

    protected array $components = [];

    protected array $events = [];

    public function __construct(
        protected Application $app
    ) {
        $this->server = new Vite(
            $this->app->getBasePath().'/bootstrap/Cache/server.json'
        );

        $this->assets = new AssetLoader($this->server);
    }

    public function loadAssets(array $inputs) {
        return $this->assets?->loadAssets($inputs);
    }

    public function asset($asset) {
        return $this->assets?->loadFile($asset);
    }

    public function handler() {
        return $this->handler ??= new BridgeHandler();
    }

    public function registerVueComponent($componentData, $view): void {


        [$content, $engine] = $view;

        $vueData = [
            'template' => "<template>{$content}</template>",
            'data' => [],
            'name' => ucfirst($componentData['name']),
            'props' => $componentData['attributes']->toVueProps(),
        ];

        $properties = $componentData['component']->getPropertiesValues();
        $props = $componentData['attributes']->getPropsAsData();
        $refs = $engine->getRefs();

        $vueData['data'] = array_merge(
            $properties,
            $props,
            $refs
        );

        $exportData = true;

        foreach($engine->getVueScripts() as $script) {
            [$type, $scriptContent] = $script;


            if(str_contains($type, 'setup')) {

                //     $scriptContent = preg_replace_callback('/@bridgeData/x', function ($matches) use ($vueData, &$exportData) {

                //         $refs = array();

                //         foreach($vueData['data'] as $key => $value) {
                //             $refs[] = "const {$key} = ref({$value})";
                //         }

                //         $exportData = false;

                //         return implode(PHP_EOL, $refs);

                //     }, $scriptContent);

                //     if(count(array_keys($vueData['data'])) > 0) {
                //         $scriptContent = PHP_EOL.'import { ref } from "vue";'.PHP_EOL.$scriptContent;
                //     }

                //     $scriptContent = preg_replace_callback('/@bridgeProps/x', function ($matches) use ($componentData) {

                //         $props = array();

                //         foreach($componentData['attributes']->toVuePropsWithValue() as $key => $value) {
                //             $refs[] = "const {$key} = '{$value}'";
                //         }

                //         return implode(PHP_EOL, $refs);

                //     }, $scriptContent);

            }

            $vueData['template'] .= "<script {$type}>{$scriptContent}</script>";
        }

        $stringData = json_encode($vueData['data']);
        $stringProps = json_encode($vueData['props']);

        // $vueData['template'] .= implode(PHP_EOL, [
        //     "<script>export default {",
        //     "props : {$stringProps},",
        //     ($exportData ? "data: () => { return {$stringData} }" : ''),
        //     "}</script>",
        // ]);

        $this->components[] = $vueData;
    }

    public function getComponents() {
        return $this->components;
    }


    public function head(): BridgeSeo {
        return $this->head ??= new BridgeSeo(config('seo'));
    }
}
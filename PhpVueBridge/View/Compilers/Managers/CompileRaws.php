<?php

namespace app\core\view\Compilers\Managers;

use app\Core\Support\Util;

trait compileRaws
{

    protected function compileRawPhp($content)
    {
        return preg_replace_callback('/@php(.*?)@endphp/is', function ($matches) {
            return "<?php {$matches[1]} ?>";
        }, $content);
    }

    protected function compileScript($content)
    {
        $content = preg_replace_callback('/<script\s*(.*?)>/x', function ($matches) {
            return "<?php \$_engine->startScript('{$matches[1]}') ?>";
        }, $content);

        return preg_replace(
            '/<\/script>/is',
            '<?php \$_engine->endScript() ?>',
            $content
        );
    }

}
?>
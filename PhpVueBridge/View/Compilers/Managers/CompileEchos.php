<?php

namespace app\core\view\Compilers\Managers;

use app\Core\Support\Util;

trait compileEchos
{

    protected function compileEchos($content)
    {
        return preg_replace_callback('/(@)?{{\s*(.+?)\s*}}(\r?\n)?/s', function ($matches) {
            return ($matches[1] === '@') ?
                substr($matches[0], 1) :
                "<?php echo e({$matches[2]}) ?>";
        }, $content);
    }

}
?>
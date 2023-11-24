<?php

namespace app\core\view\Compilers\Managers;

use app\Core\Support\Util;

trait compileFiles
{

    protected function compileFiles($content)
    {
        return preg_replace_callback('/@?(include|extend)\s*\((.*)\)/i', function ($matches) {

            $type = $matches[1];
            $arguments = explode(',', trim($matches[2]), 2);

            [$name, $data] = [
                Util::StripString($arguments[0]),
                $arguments[1] ?? '[]'
            ];

            if ($type == 'extend') {
                $this->footer[] = "<?php echo \$_engine->render('{$name}') ?>";
            } else {
                return "<?php echo \$_engine->render('{$name}',{$data}) ?>";
            }

        }, $content);
    }

    protected function compileBlocks($content)
    {
        $content = preg_replace_callback('/@section\s*\(([^)]*)\)/is', function ($matches) {

            $name = Util::StripString($matches[1]);

            return "<?php \$_engine->startSection ('{$name}') ?>";
        }, $content);

        return preg_replace('/@endsection/is', '<?php \$_engine->endSection (); ?>', $content);
    }

    public function yield ($matches)
    {
        $yield = Util::StripString($matches[2]);
        return "<?php echo \$_engine->yieldContent ('{$yield}') ?>";
    }


    public function assets($matches)
    {
        $inputs = $matches[2];

        if (str_starts_with($matches[2], '[') && str_ends_with($matches[2], ']')) {
            $inputs = substr($matches[2], 1, -1);
        }

        $inputs = explode(',', $inputs);

        return \app\core\facades\Bridge::loadAssets($inputs);
    }


}
?>
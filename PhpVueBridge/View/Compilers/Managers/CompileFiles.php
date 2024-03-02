<?php

namespace PhpVueBridge\View\Compilers\Managers;

use PhpVueBridge\Support\Util;
use PhpVueBridge\Support\Facades\Bridge;

trait compileFiles
{
    protected function compileFiles($content)
    {
        return preg_replace_callback('/@?(include|extends)\s*\((.*)\)/i', function ($matches) {

            $type = $matches[1];
            $arguments = explode(',', trim($matches[2]), 2);

            [$name, $data] = [
                Util::StripString($arguments[0]),
                $arguments[1] ?? '[]'
            ];

            if ($type == 'extends') {
                $this->footer[] = "<?php echo \$_factory->make('{$name}')->render() ?>";
            } else {
                return "<?php echo \$_factory->make('{$name}',{$data})->render() ?>";
            }
        }, $content);
    }

    protected function compileBlocks($content)
    {
        $content = preg_replace_callback('/@section\s*\(([^)]*)\)/is', function ($matches) {

            $name = Util::StripString($matches[1]);

            return "<?php \$_factory->startSection ('{$name}') ?>";
        }, $content);

        return preg_replace('/@endsection/is', '<?php \$_factory->endSection (); ?>', $content);
    }

    public function yield(array $directive)
    {
        $yield = Util::StripString($directive[2]);

        return "<?php echo \$_factory->yieldContent ('{$yield}') ?>";
    }


    public function assets($directive)
    {
        $inputs = $directive[2];

        if (str_starts_with($directive[2], '[') && str_ends_with($directive[2], ']')) {
            $inputs = substr($directive[2], 1, -1);
        }

        $inputs = explode(',', $inputs);

        return Bridge::loadAssets($inputs);
    }
}

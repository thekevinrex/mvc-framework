<?php

namespace PhpVueBridge\View\Engines;

class PhPEngine extends Engine
{

    public function get(string $file, array $data)
    {

        extract($data, EXTR_SKIP);

        $obLevel = ob_get_level();

        ob_start();

        try {
            require $file;
        } catch (\Throwable $e) {

            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }

            throw $e;
        }

        return ltrim(ob_get_clean());
    }
}
?>
<?php


namespace app\App\View\Components;

use app\core\bridge\Components\BridgeComponent;



class PruebaComponent extends BridgeComponent
{

    public string $msg = 'hola probando public properties';

    public $hola = 'sdasdasdas';

    public function click()
    {
        $hola = 'probando evento';
    }
}
?>
<?php


namespace App\View\Components;

use PhpVueBridge\View\View;
use PhpVueBridge\View\Component;

class PruebaComponent extends Component
{

    public string $msg = 'hola probando public properties';

    public $hola;

    public function click()
    {
        $hola = 'probando evento';
    }

    public function render(): string|View
    {
        return 'components.prueba';
    }
}

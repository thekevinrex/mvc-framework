<?php


namespace PhpVueBridge\View\Contracts;

use PhpVueBridge\Bedrock\Contracts\Contracts;

interface ViewContract extends Contracts
{


    public function getShared(): array;

    public function startRendering(string $path): void;

    public function endRendering(): string;
}

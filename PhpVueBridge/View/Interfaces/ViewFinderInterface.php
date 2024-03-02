<?php

namespace PhpVueBridge\View\Interfaces;

interface ViewFinderInterface
{

    public function syncExtensions(array $extensions): void;

    public function getExtension(string $path): string;

    public function find(string $view): string;
}

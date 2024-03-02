<?php

namespace PhpVueBridge\View\Managers\Concerns;

trait ManageSections
{

    protected array $buildingSection = [];

    protected array $sections = [];

    public function startSection(string $section): void
    {
        if (ob_start()) {
            $this->buildingSection[] = $section;
        }
    }

    public function endSection(): void
    {
        $lastSection = array_pop($this->buildingSection);

        $this->sections[$lastSection] = ob_get_clean();
    }

    public function yieldContent(string $section): string
    {
        return $this->sections[$section] ?? '';
    }
}

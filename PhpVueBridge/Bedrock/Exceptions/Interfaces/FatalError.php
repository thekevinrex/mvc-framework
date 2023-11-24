<?php

namespace PhpVueBridge\Bedrock\Exceptions\Interfaces;

class FatalError extends \Error
{

    private array $error;

    public function __construct(string $message, int $code, array $error)
    {

        parent::__construct($message, $code);

        $this->error = $error;

        foreach (['file' => $error['file'], 'line' => $error['line'], 'trace' => null,] as $property => $value) {
            if (null !== $value) {
                $refl = new \ReflectionProperty(\Error::class, $property);
                $refl->setValue($this, $value);
            }
        }
    }

    public function getError()
    {
        return $this->error;
    }
}
?>
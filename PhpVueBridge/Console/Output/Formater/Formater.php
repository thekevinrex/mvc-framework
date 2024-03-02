<?php


namespace PhpVueBridge\Console\Output\Formater;

use PhpVueBridge\Console\Output\Color\Color;


class Formater
{
    const ESC = "\033";

    const ANSI = self::ESC . "[%sm";

    const FONT_BOLD = 1;

    const FONT_ITALIC = 3;

    const FONT_UNDERLINE = 4;

    const FONT_STRIKETHROUGH = 9;

    const CLOSE = 0;

    protected array $defaultStyles = [

        'comment' => [
            'fg' => 'white',
            'bg' => 'black',
        ],

    ];

    public function __construct(
        protected string $message,
        protected ?string $style = null
    ) {

    }

    public function toString()
    {
        if (is_null($this->style)) {
            return $this->message;
        }

        return $this->format();
    }

    protected function format(): string
    {

        // Find Blocks

        $line = $this->message;
        $isBlock = false;

        $line = preg_replace_callback('/\[(.*?)\]/', function ($matches) use (&$isBlock) {

            $isBlock = true;

            return $this->applyStyle(' ' . $matches[1] . ' ');
        }, $line, 1);

        // Aply styles

        return (!$isBlock) ? $this->applyStyle($line) : $line;
    }

    protected function applyStyle($line)
    {

        $styles = $this->getStyles();

        $finishStyle = self::ESC;

        foreach ($styles as $type => $value) {

            if (in_array($type, ['fg', 'bg'])) {
                $finishStyle .= (new Color($value))->{'get' . ucfirst($type)}();

            } else if (in_array($type, ['format'])) {
                if (in_array($value, [self::FONT_BOLD, self::FONT_ITALIC, self::FONT_STRIKETHROUGH, self::FONT_UNDERLINE])) {
                    $finishStyle .= sprintf(self::ANSI, $value);
                }
            }
        }

        $finishStyle .= $line;
        $finishStyle .= sprintf(self::ANSI, self::CLOSE);

        return $finishStyle;
    }

    protected function getStyles(): array
    {

        $rawStyles = explode(';', $this->style);

        $styles = [];

        foreach ($rawStyles as $style) {
            if (str_contains($style, '=')) {
                [$type, $value] = explode('=', $style, 2);

                $styles[$type] = $value;
            } else {
                if (in_array($style, array_keys($this->defaultStyles))) {
                    foreach ($this->defaultStyles[$style] as $key => $value) {
                        $styles[$key] = $value;
                    }
                }
            }
        }

        return $styles;
    }

}
?>
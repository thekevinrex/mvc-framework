<?php


namespace PhpVueBridge\Console\Output\Line;

use PhpVueBridge\Console\Output\Color\Color;
use PhpVueBridge\Console\Output\Formater\Formater;
use PhpVueBridge\Console\Interfaces\OutputLineInterface;

class OutputLine implements OutputLineInterface
{
    protected ?string $fg = null;

    protected ?string $bg = null;

    protected ?int $format = null;

    public function __construct(
        protected string $message,
        protected ?string $style = null,
    ) {

    }

    public function bold()
    {
        $this->format = Formater::FONT_BOLD;

        return $this;
    }

    public function italic()
    {
        $this->format = Formater::FONT_ITALIC;

        return $this;
    }

    public function underline()
    {
        $this->format = Formater::FONT_UNDERLINE;

        return $this;
    }

    public function background(string $background)
    {
        if (!in_array($background, array_keys(Color::COLORS))) {
            throw new \InvalidArgumentException('The background color is not supported');
        }

        $this->bg = $background;

        return $this;
    }

    public function foreground(string $foreground)
    {
        if (!in_array($foreground, array_keys(Color::COLORS))) {
            throw new \InvalidArgumentException('The foreground color is not supported');
        }

        $this->fg = $foreground;

        return $this;
    }

    public function blue()
    {
        $this->foreground('blue');

        return $this;
    }

    /**
     * Set the value of message
     *
     * @return  self
     */
    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    public function style(string $style)
    {
        $this->style = $style;

        return $this;
    }

    protected function getStyle()
    {
        if (!is_null($this->style)) {
            return $this->style;
        }

        $style = '';

        if (!is_null($this->fg)) {
            $style .= sprintf('fg=%s;', $this->fg);
        }

        if (!is_null($this->bg)) {
            $style .= sprintf('bg=%s;', $this->bg);
        }

        if (!is_null($this->format)) {
            $style .= sprintf('format=%s;', $this->format);
        }

        return $style;
    }

    public function __toString(): string
    {
        return (
            new Formater(
                $this->message,
                $this->getStyle()
            )
        )->toString();
    }

    public function prepend(string $message)
    {
        $this->message = $message . $this->message;

        return $this;
    }
}
?>
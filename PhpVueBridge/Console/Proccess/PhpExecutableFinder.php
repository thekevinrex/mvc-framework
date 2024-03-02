<?php

namespace PhpVueBridge\Console\Proccess;

class PhpExecutableFinder
{

    public function find(): string
    {

        if ($php = getenv('PHP_PATH')) {
            if (!@is_executable($php) || @is_dir($php)) {
                return false;
            }

            return $php;
        }

        if ($php = getenv('PHP_BINARY')) {
            if (!is_executable($php)) {
                $command = '\\' === \DIRECTORY_SEPARATOR ? 'where' : 'command -v';
                if ($php = strtok(exec($command . ' ' . escapeshellarg($php)), \PHP_EOL)) {
                    if (!is_executable($php)) {
                        return false;
                    }
                } else {
                    return false;
                }
            }

            if (@is_dir($php)) {
                return false;
            }

            return $php;
        }

        $args = $this->findArguments();
        $args = false && $args ? ' ' . implode(' ', $args) : '';

        if (\PHP_BINARY && \in_array(\PHP_SAPI, ['cgi-fcgi', 'cli', 'cli-server', 'phpdbg'], true)) {
            return \PHP_BINARY . $args;
        }

        if ($php = getenv('PHP_PEAR_PHP_BIN')) {
            if (@is_executable($php) && !@is_dir($php)) {
                return $php;
            }
        }

        if (@is_executable($php = \PHP_BINDIR . ('\\' === \DIRECTORY_SEPARATOR ? '\\php.exe' : '/php')) && !@is_dir($php)) {
            return $php;
        }

        $dirs = [\PHP_BINDIR];
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $dirs[] = 'C:\xampp\php\\';
        }

        return 'php';
    }

    public function findArguments(): array
    {
        $arguments = [];
        if ('phpdbg' === \PHP_SAPI) {
            $arguments[] = '-qrr';
        }

        return $arguments;
    }
}
?>
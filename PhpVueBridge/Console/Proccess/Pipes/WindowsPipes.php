<?php

namespace PhpVueBridge\Console\Proccess\Pipes;

use PhpVueBridge\Console\Proccess\Proccess;

class WindowsPipes extends AbstractPipes
{
    protected array $files = [];

    protected array $fileHandles = [];

    protected array $lockFiles = [];

    protected array $readBytes = [
        Proccess::STDOUT => 0,
        Proccess::STDERR => 0,
    ];

    public function __construct()
    {

        $tmpDir = sys_get_temp_dir();

        $pipes = [
            Proccess::STDOUT => Proccess::OUT,
            Proccess::STDERR => Proccess::ERR,
        ];

        for ($i = 0; ; ++$i) {
            foreach ($pipes as $pipe => $name) {

                $filePath = sprintf('%s//sf_proc_%02X.%s', $tmpDir, $i, $name);

                // Si no se puede abrir el lock file
                if (!$file = fopen($filePath . '.lock', 'w')) {
                    // if the file exists means that this file is locked already by another process
                    if (file_exists($filePath . '.lock')) {
                        continue 2;
                    }

                    // if the file does not exist then there is a problem opening the files so we throw an exception
                    throw new \RuntimeException('Unable to open the lock file at ' . $filePath);
                }

                // if the lock file exists now we try to lock the file and if it is not possible we pass to a new iteration
                if (!flock($file, LOCK_EX | LOCK_NB)) {
                    continue 2;
                }

                // If the pipe is already has been locked to a file then we unlock that file and close the stream
                if (isset($this->lockFiles[$pipe])) {
                    flock($this->lockFiles[$pipe], LOCK_UN);
                    fclose($this->lockFiles[$pipe]);
                }

                // We save the file lock stream to know if the pipe is already open
                $this->lockFiles[$pipe] = $file;

                // if the file cant be opened the we unlock the lock file and close the pipe stream
                if (!$file = fopen($filePath, 'r')) {
                    flock($this->lockFiles[$pipe], \LOCK_UN);
                    fclose($this->lockFiles[$pipe]);
                    unset($this->lockFiles[$pipe]);
                    continue 2;
                }

                $this->fileHandles[$pipe] = $file;
                $this->files[$pipe] = $filePath;
            }

            break;
        }
    }

    public function getDescriptors(): array
    {
        return [
            Proccess::STDIN => ['pipe', 'r'],
            Proccess::STDOUT => ['file', 'NULL', 'w'],
            Proccess::STDERR => ['file', 'NULL', 'w'],
        ];

        // return [
        //     0 => array('pipe', 'r'),
        //     1 => array('pipe', 'w'),
        //     2 => array('pipe', 'w'),
        // ];
    }

    public function readPipes(): array
    {
        $result = [];

        $w = $this->write();
        $r = $e = [];
        $this->unBlock();

        foreach ($this->fileHandles as $pipe => $file) {

            $data = @stream_get_contents($file, -1, $this->readBytes[$pipe]);

            if ($data !== false) {
                $result[$pipe] = $data;
                $this->readBytes[$pipe] += \strlen($data);
            }

        }

        return $result;
    }

    public function close()
    {
        parent::close();

        $this->readBytes = [
            Proccess::STDOUT => 0,
            Proccess::STDERR => 0,
        ];

        foreach ($this->fileHandles as &$file) {
            ftruncate($file, 0);
            flock($file, LOCK_UN);
            fclose($file);
        }

        $this->lockFiles = $this->files = $this->fileHandles = [];
    }
}

?>
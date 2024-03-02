<?php

namespace PhpVueBridge\Console\Proccess\Pipes;

use PhpVueBridge\Console\Proccess\Proccess;

class WindowsPipes extends AbstractPipes
{
    /**
     * The files for the pipes
     */
    protected array $files = [];

    /**
     * The files stream handler for the pipes
     */
    protected array $fileHandles = [];

    /**
     * The locked files for the pipes
     */
    protected array $lockFiles = [];

    /**
     * The current reading bytes for each pipe
     */
    protected array $readBytes = [
        Proccess::STDOUT => 0,
        Proccess::STDERR => 0,
    ];

    public function __construct()
    {

        // Get the system temporary directory to know where the pipes generate the pipes files
        $tmpDir = sys_get_temp_dir();

        $pipes = [
            Proccess::STDOUT => Proccess::OUT,
            Proccess::STDERR => Proccess::ERR,
        ];

        // An infinite loop to iterate until we can get and lock the files for each pipe
        for ($i = 0; ; ++$i) {
            foreach ($pipes as $pipe => $name) {

                // The file path for the pipe
                $filePath = sprintf('%s//sf_proc_%02X.%s', $tmpDir, $i, $name);

                // If the lock file cant be opened then we check if the file exists
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
                // Because this pipe in a previous iteration succeded in locking a file pipe but there was a pipe that could not
                if (isset($this->lockFiles[$pipe])) {
                    flock($this->lockFiles[$pipe], LOCK_UN);
                    fclose($this->lockFiles[$pipe]);
                }

                // We save the file lock stream to know if the pipe is already open
                $this->lockFiles[$pipe] = $file;

                // if the file cant be opened the we unlock the lock file and close the pipe stream
                if (!($file = fopen($filePath, 'w')) || !fclose($file) || !$file = fopen($filePath, 'r')) {
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
        // We get the descriptors por the process 
        // We set the stdout and stderr for files becouse the pipes hangs out in windows 
        // So using stdout and stderr files is a way around this problem
        return [
            ['pipe', 'r'],
            ['file', $this->files[Proccess::STDOUT], 'w'],
            ['file', $this->files[Proccess::STDERR], 'w'],
        ];
    }

    public function readPipes(): array
    {
        $result = [];

        $w = $this->write();
        $r = $e = [];
        $this->unBlock();

        // We iterate through each file handle to get the output 
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
        // We close the pipes first
        parent::close();

        // Set the reading bytes to 0
        $this->readBytes = [
            Proccess::STDOUT => 0,
            Proccess::STDERR => 0,
        ];

        // For each file handle we truncate the file, unlock and close the file stream
        foreach ($this->fileHandles as &$file) {
            ftruncate($file, 0);
            flock($file, LOCK_UN);
            fclose($file);
        }

        // Then we set the lock, files and handles to be empty
        $this->lockFiles = $this->files = $this->fileHandles = [];
    }
}

?>
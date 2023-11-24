<?php


namespace PhpVueBridge\Console\Proccess;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Console\Proccess\Pipes\AbstractPipes;
use PhpVueBridge\Console\Proccess\Pipes\WindowsPipes;

class Proccess
{

    const STDIN = 0;

    const STDOUT = 1;

    const STDERR = 2;

    const OUT = 'out';

    const ERR = 'err';

    protected array $env = array();

    /**
     * The process pipes
     */
    protected AbstractPipes $processPipes;

    /**
     * The process stream
     */
    protected $process;

    /**
     * The callback for the process output
     */
    protected ?\Closure $callback;

    /**
     * The current process status
     */
    protected array $status;

    public function __construct(
        protected string $command,
        protected ?string $path = null,
        array $env = []
    ) {

        if (!\function_exists('proc_open')) {
            throw new \LogicException('The Process class relies on proc_open, which is not available on your PHP installation.');
        }

        if (is_null($this->path)) {
            $this->path = getcwd();
        }

        if (!empty($env)) {
            $this->setEnv($env);
        }
    }

    public function start(
        \Closure $callback
    ) {

        // We get the descriptor from the proc_open
        $descriptor = $this->getDescriptors();
        $options = [];

        // If is present the callback it we be build for its use on the process output
        $this->callback = $this->buildCallback($callback);

        $path = $this->getPath();

        if (!is_dir($path)) {
            throw new \RuntimeException('You must specify a directory');
        }

        $this->process = @proc_open(
            $this->prepareCommand(),
            $descriptor,
            $this->processPipes->pipes,
            $path,
            $this->getEnvs(),
            $options
        );

        if (!is_resource($this->process)) {
            throw new \RuntimeException('You must specify a process');
        }

        $this->updateStatus();
    }

    public function getDescriptors()
    {
        $this->processPipes = $pipes = new WindowsPipes();

        return $pipes->getDescriptors();
    }

    protected function prepareCommand()
    {
        return $command = $this->command;
    }

    protected function getEnvs()
    {
        $envs = [];

        $env = getenv();
        $env = ('\\' === \DIRECTORY_SEPARATOR ? array_intersect_ukey($env, $_SERVER, 'strcasecmp') : array_intersect_key($env, $_SERVER)) ?: $env;

        $env = $_ENV + ('\\' === \DIRECTORY_SEPARATOR ? array_diff_ukey($env, $this->env, 'strcasecmp') : $env);

        foreach ($env as $key => $value) {
            if (!in_array($key, ['argc', 'argv', 'ARGC', 'ARGV'], true)) {
                $envs[$key] = $value;
            }
        }

        return $envs;
    }

    public function readPipes(): void
    {
        $result = $this->processPipes->readPipes();

        foreach ($result as $type => $value) {
            call_user_func($this->getCallback(), $type, $value);
        }
    }

    public function setEnv(array $env)
    {
        $this->env = array_merge($this->env, $env);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function updateStatus()
    {
        $this->status = proc_get_status($this->process);

        $this->readPipes();

        if (!$this->status['running']) {
            $this->close();
        }
    }

    public function isRunning(): bool
    {
        $status = proc_get_status($this->process);

        $this->updateStatus();

        return $status['running'];
    }

    public function close()
    {

        // First we close the process pipes
        $this->processPipes->close();

        // IF the process is still a stream the we closit
        if (\is_resource($this->process)) {
            proc_close($this->process);
        }

        $exitCode = $this->status['exitcode'];

        $this->callback = null;

        return $exitCode;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function buildCallback(\Closure $callback)
    {
        return function ($type, $value) use ($callback) {
            return !is_null($callback) && $callback($type, $value);
        };
    }
}

?>
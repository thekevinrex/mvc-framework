<?php

namespace PhpVueBridge\View\Compilers\Parser;

use PhpVueBridge\View\Compilers\Parser\Nodes\Node;
use PhpVueBridge\View\Compilers\Parser\Nodes\ModuleNode;
use PhpVueBridge\View\Compilers\Parser\Nodes\RootNode;
use PhpVueBridge\View\Compilers\Parser\Nodes\TemplateNode;
use PhpVueBridge\View\Compilers\Parser\TokenTree;

class TreeNavegator
{

    protected ModuleNode $currentModule;

    protected ?Node $next;

    protected ?Node $prev;

    protected ?Node $current;

    protected \Closure $callback;

    protected bool|string $filterBy = false;

    protected array $filters = [];

    protected bool $stop = false;

    public function __construct(
        protected RootNode $root
    ) {
    }

    public function withRoot(RootNode $root): self
    {
        $this->root = $root;

        return $this;
    }

    public function filterByTokens(array $tokens): self
    {
        $this->filterBy = 'tokens';
        $this->filters = $tokens;

        return $this;
    }

    public function filterByNodes(array $nodes): self
    {
        $this->filterBy = 'nodes';
        $this->filters = $nodes;

        return $this;
    }

    protected function passedFilters(): bool
    {
        if (!$this->filterBy) {
            return true;
        }

        if ($this->filterBy === 'tokens') {
            if (in_array($this->current->getTokenType(), $this->filters)) {
                return true;
            }
        }

        if ($this->filterBy === 'nodes') {
            foreach ($this->filters as $filter) {
                if ($this->current instanceof $filter) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getPrev(): Node
    {
        return $this->prev;
    }

    public function getCurrent(): Node
    {
        return $this->current;
    }

    public function getNext(): Node
    {
        return $this->next;
    }

    public function start(\Closure $callback)
    {
        $this->callback = $this->buildCallback($callback);

        $this->reset();

        $this->navigate();

        return $this;
    }

    public function reset()
    {
        $this->prev = null;
        $this->current = null;
        $this->next = null;

        $this->currentModule = $this->root;

        $this->filterBy = false;
        $this->filters = [];

        $this->stop = false;
    }

    protected function navigate()
    {
        while (!$this->currentModule->isLastNode() && !$this->stop) {

            $this->current = $this->currentModule->getCurrentNode();

            $this->next = $this->currentModule->getNextNode();

            if ($this->passedFilters()) {
                $this->callCompiler();
            }

            if ($this->stop) {
                break;
            }

            if ($this->current instanceof ModuleNode) {
                $this->toNextModule($this->current);
            }

            if (
                $this->current instanceof TemplateNode
                && ($nextModule = $this->current->getNode()) instanceof ModuleNode
            ) {
                $this->toNextModule($nextModule);
            }

            $this->prev = $this->current;
            $this->currentModule->nextNode();
        }
    }

    protected function buildCallback(\Closure $callback)
    {
        return function (Node $node) use ($callback) {
            return $callback($node, $this);
        };
    }

    protected function toNextModule(ModuleNode $module)
    {
        $actual = $this->currentModule;

        $this->currentModule = $module;

        $this->navigate();

        $this->currentModule = $actual;
    }

    protected function callCompiler()
    {
        call_user_func($this->callback, $this->current);
    }

    public function stop()
    {
        $this->stop = true;
    }
}

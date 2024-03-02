<?php

namespace PhpVueBridge\Bedrock;

use PhpVueBridge\Collection\Collection;
use PhpVueBridge\Bedrock\Contracts\ConfigContract;

class Config extends Collection implements ConfigContract
{

    protected $app;

    public function __construct(Application $app, $items = [])
    {
        $this->app = $app;

        parent::__construct($items);
    }


    public function setEncoding($encoding): void
    {
        mb_internal_encoding($encoding);
    }

    public function setDefaultTimeZone($timezone)
    {
        date_default_timezone_set($timezone);
    }

}
?>
<?php

namespace app\App\Listeners;

class TestingListeners
{

    public function handle($server)
    {
        var_dump($server->server);
    }
}
?>
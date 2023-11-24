<?php

namespace app\Core\Websockets\Payload;

use app\Core\Websockets\ConnectionManager;
use app\Core\Websockets\Exceptions\PayloadException;
use app\Core\Websockets\Protocols\Protocol;

class PayloadHandler
{

    protected $callback;

    protected Payload $payload;

    public function __construct(
        protected Protocol $protocol,
        $callback
    ) {
        $this->callback = $callback;
    }

    public function handle(string $data)
    {
        while ($data) {
            // Each iteration pulls off a single payload chunk
            $remaining = $this->getPayload()->getRemainingData();

            // If we don't yet know how much data is remaining, read data into
            // the payload in two byte chunks (the size of a WebSocket frame
            // header to get the initial length)
            //
            // Then re-loop. For extended lengths, this will happen once or four
            // times extra, as the extended length is read in.
            if ($remaining === null) {
                $chunkSize = 2;
            } elseif ($remaining > 0) {
                $chunkSize = $remaining;
            } elseif ($remaining === 0) {
                $chunkSize = 0;
            }

            $chunkSize = min(strlen($data), $chunkSize);
            $chunk = substr($data, 0, $chunkSize);
            $data = substr($data, $chunkSize);

            $this->getPayload()->receiveData($chunk);

            if ($remaining !== 0 && !$this->getPayload()->isComplete()) {
                continue;
            }

            if ($this->getPayload()->isComplete()) {
                $this->emit($this->getPayload());
                $this->payload = $this->protocol->getPayload();
            } else {
                throw new PayloadException('Payload will not complete');
            }
        }
    }

    /**
     * Emits a complete payload to the callback
     *
     * @param Payload $payload
     */
    protected function emit(Payload $payload)
    {
        call_user_func($this->callback, $payload);
    }

    public function getPayload(): Payload
    {
        return $this->payload ??= $this->protocol->getPayload();
    }
}
?>
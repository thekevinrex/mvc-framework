<?php


namespace PhpVueBridge\Http\Response;

class JsonResponse extends Response implements \JsonSerializable
{

    protected array $data;

    public function __construct(array $data = array())
    {
        $this->data = $data;

        parent::__construct(json_encode($data));
    }

    public function jsonSerialize(): mixed
    {
        return json_encode($this->data);
    }
}
?>
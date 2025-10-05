<?php
class ErrorBase
{
    public mixed $type;
    public mixed $data;

    public function __construct(mixed $type, mixed $data)
    {
        $this->type = $type;
        $this->data = $data;
    }
}
?>
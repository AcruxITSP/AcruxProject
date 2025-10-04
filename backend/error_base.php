<?php
abstract class ErrorBase
{
    public mixed $type;
    public mixed $data;

    protected function __construct(mixed $type, mixed $data)
    {
        $this->type = $type;
        $this->data = $data;
    }
}
?>
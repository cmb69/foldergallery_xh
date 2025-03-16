<?php

namespace Foldergallery\Infra;

class FakeRequest extends Request
{
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    protected function query(): string
    {
        return $this->options["query"] ?? "";
    }
}

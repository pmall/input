<?php

declare(strict_types=1);

namespace Quanta\Validation;

final class NestedError implements ErrorInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var \Quanta\Validation\ErrorInterface
     */
    private $error;

    /**
     * @param string                            $key
     * @param \Quanta\Validation\ErrorInterface $error
     */
    public function __construct(string $key, ErrorInterface $error)
    {
        $this->key = $key;
        $this->error = $error;
    }

    /**
     * @inheritdoc
     */
    public function label(): string
    {
        return $this->error->label();
    }

    /**
     * @inheritdoc
     */
    public function name(): string
    {
        return '[' . $this->key . ']' . $this->error->name();
    }

    /**
     * @inheritdoc
     */
    public function params(): array
    {
        return $this->error->params();
    }

    /**
     * @inheritdoc
     */
    public function message(): string
    {
        return $this->error->message();
    }
}

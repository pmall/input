<?php

declare(strict_types=1);

namespace Quanta;

final class WrappedCallable implements InputInterface
{
    /**
     * @var string[]
     */
    private $keys;

    /**
     * @var callable
     */
    private $f;

    /**
     * @param callable  $f
     * @param string    ...$keys
     */
    public function __construct(callable $f, string ...$keys)
    {
        $this->keys = $keys;
        $this->f = $f;
    }

    /**
     * @param string ...$keys
     * @return \Quanta\WrappedCallable
     */
    public function nested(string ...$keys): self
    {
        return count($keys) == 0 ? $this : new self($this->f, ...$keys, ...$this->keys);
    }

    /**
     * @param mixed $x
     * @return \Quanta\WrappedCallable
     */
    public function curryed($x): self
    {
        return new self(fn (...$xs) => ($this->f)($x, ...$xs), ...$this->keys);
    }

    /**
     * @param \Quanta\InputInterface ...$inputs
     * @return \Quanta\InputInterface
     */
    public function __invoke(InputInterface ...$inputs): InputInterface
    {
        return array_reduce($inputs, fn ($f, $input) => $input->apply($f), $this);
    }

    /**
     * @param \Quanta\InputInterface ...$inputs
     * @return \Quanta\InputInterface
     */
    public function flatinvoke(InputInterface ...$inputs): InputInterface
    {
        return $this(...$inputs)->validate(fn ($input) => $input);
    }

    /**
     * @inheritdoc
     */
    public function apply(InputInterface $input): InputInterface
    {
        switch (true) {
            case $input instanceof Failure:
                return $input;
            case $input instanceof WrappedCallable:
                return $input->curryed(($this->f)());
        }

        throw new \InvalidArgumentException(
            sprintf('The given argument must be an instance of Quanta\WrappedCallable|Quanta\Failure, %s given', gettype($input))
        );
    }

    /**
     * @inheritdoc
     */
    public function bind(callable $f): InputInterface
    {
        $input = $f(($this->f)());

        switch (true) {
            case $input instanceof Success:
            case $input instanceof Failure:
            case $input instanceof WrappedCallable:
                return $input->nested(...$this->keys);
        }

        throw new \InvalidArgumentException(
            sprintf('The given callable must return an instance of Quanta\Success|Quanta\WrappedCallable|Quanta\Failure, %s returned', gettype($input))
        );
    }

    /**
     * @inheritdoc
     */
    public function validate(callable ...$fs): InputInterface
    {
        if (count($fs) == 0) {
            return $this;
        }

        /** @var callable */
        $f = array_shift($fs);

        return $this->bind($f)->validate(...$fs);
    }

    /**
     * @inheritdoc
     */
    public function unpack(callable ...$fs): array
    {
        $value = ($this->f)();

        if (is_array($value)) {
            return array_map(function ($key, $value) use ($fs) {
                return (new Success($value, ...[...$this->keys, (string) $key]))->validate(...$fs);
            }, array_keys($value), $value);
        }

        throw new \LogicException(sprintf('Cannot unpack %s', gettype($value)));
    }

    /**
     * @inheritdoc
     */
    public function extract(callable $success, callable $failure)
    {
        return $success(($this->f)());
    }
}

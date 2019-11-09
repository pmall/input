<?php

declare(strict_types=1);

namespace Quanta\Validation;

interface InputInterface
{
    /**
     * Call the given wrapped callable with the wrapped value.
     *
     * @param \Quanta\Validation\InputInterface $f
     * @return \Quanta\Validation\InputInterface
     */
    public function apply(InputInterface $f): InputInterface;

    /**
     * Call the given wrapping callable with the wrapped value.
     *
     * @param callable(mixed $value): \Quanta\Validation\InputInterface ...$fs
     * @return \Quanta\Validation\InputInterface
     */
    public function bind(callable ...$fs): InputInterface;

    /**
     * Apply the given success callable on successful value or the failure callable on errors.
     *
     * @param callable(mixed $a): mixed                     $success
     * @param callable(ErrorInterface ...$errors): mixed    $failure
     * @return mixed
     */
    public function extract(callable $success, callable $failure);
}

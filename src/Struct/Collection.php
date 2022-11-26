<?php
declare(strict_types=1);

namespace Danger\Struct;

/**
 * @template T
 *
 * @implements \IteratorAggregate<array-key, T>
 */
abstract class Collection implements \IteratorAggregate, \Countable
{
    /**
     * @var T[]
     */
    protected array $elements = [];

    /**
     * @param iterable<string|int, T> $elements
     */
    final public function __construct(iterable $elements = [])
    {
        foreach ($elements as $key => $element) {
            $this->set($key, $element);
        }
    }

    /**
     * @param T $element
     */
    public function add(mixed $element): void
    {
        $this->elements[] = $element;
    }

    /**
     * @param T $element
     */
    public function set(string|int $key, mixed $element): void
    {
        $this->elements[$key] = $element;
    }

    /**
     * @return T|null
     */
    public function get(string|int $key)
    {
        if ($this->has($key)) {
            return $this->elements[$key];
        }

        return null;
    }

    public function clear(): void
    {
        $this->elements = [];
    }

    public function count(): int
    {
        return \count($this->elements);
    }

    /**
     * @return string[]|int[]
     */
    public function getKeys(): array
    {
        return array_keys($this->elements);
    }

    public function has(string|int $key): bool
    {
        return \array_key_exists($key, $this->elements);
    }

    /**
     * @return mixed[]
     */
    public function map(\Closure $closure): array
    {
        return array_map($closure, $this->elements);
    }

    public function reduce(\Closure $closure, mixed $initial = null): mixed
    {
        return array_reduce($this->elements, $closure, $initial);
    }

    /**
     * @return array<mixed>
     */
    public function fmap(\Closure $closure): array
    {
        return array_filter($this->map($closure));
    }

    public function sort(\Closure $closure): void
    {
        uasort($this->elements, $closure);
    }

    /**
     * @return static(Collection<T>)
     */
    public function filter(\Closure $closure): static
    {
        return $this->createNew(array_filter($this->elements, $closure));
    }

    /**
     * @return static(Collection<T>)
     */
    public function slice(int $offset, ?int $length = null): static
    {
        return $this->createNew(\array_slice($this->elements, $offset, $length, true));
    }

    /**
     * @return T[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @return T|null
     */
    public function first()
    {
        return array_values($this->elements)[0] ?? null;
    }

    /**
     * @return T|null
     */
    public function last()
    {
        return array_values($this->elements)[\count($this->elements) - 1] ?? null;
    }

    public function remove(string $key): void
    {
        unset($this->elements[$key]);
    }

    /**
     * @return \Generator<T>
     */
    public function getIterator(): \Generator
    {
        yield from $this->elements;
    }

    /**
     * @param T[] $elements
     *
     * @return static(Collection<T>)
     */
    protected function createNew(iterable $elements = []): static
    {
        return new static($elements);
    }
}

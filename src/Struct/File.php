<?php
declare(strict_types=1);

namespace Danger\Struct;

abstract class File
{
    public const STATUS_ADDED = 'added';
    public const STATUS_REMOVED = 'removed';
    public const STATUS_MODIFIED = 'modified';

    public string $name;

    public string $status;

    public int $additions;

    public int $deletions;

    public int $changes;

    public string $patch = '';

    abstract public function getContent(): string;
}

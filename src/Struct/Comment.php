<?php
declare(strict_types=1);

namespace Danger\Struct;

use DateTimeInterface;

class Comment
{
    public string $author;

    public string $body;

    public DateTimeInterface $createdAt;

    public DateTimeInterface $updatedAt;
}

<?php
declare(strict_types=1);

namespace Danger\Struct;

use DateTimeInterface;

class Commit
{
    public string $sha;

    public string $message;

    public string $author;

    public string $authorEmail;

    public DateTimeInterface $createdAt;

    public bool $verified = false;
}

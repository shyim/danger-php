<?php
declare(strict_types=1);

namespace Danger\Component\Struct;

class Commit
{
    public string $sha;

    public string $message;

    public string $author;

    public string $authorEmail;

    public \DateTime $createdAt;

    public bool $verified = false;
}

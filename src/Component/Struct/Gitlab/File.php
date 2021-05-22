<?php
declare(strict_types=1);

namespace Danger\Component\Struct\Gitlab;

use Gitlab\Client;

class File extends \Danger\Component\Struct\File
{
    public function __construct(private Client $client, private string $projectIdentifier, private string $path, private string $sha)
    {
    }

    public function getContent(): string
    {
        $file = $this->client->repositoryFiles()->getFile($this->projectIdentifier, $this->path, $this->sha);

        if (isset($file['content'])) {
            return base64_decode($file['content'], true);
        }

        throw new \RuntimeException(sprintf('Invalid file %s at sha %s', $this->path, $this->sha));
    }
}

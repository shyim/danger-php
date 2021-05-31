<?php
declare(strict_types=1);

namespace Danger\Struct\Gitlab;

use Gitlab\Client;

class File extends \Danger\Struct\File
{
    private ?string $content = null;

    public function __construct(private Client $client, private string $projectIdentifier, private string $path, private string $sha)
    {
    }

    public function getContent(): string
    {
        if ($this->content !== null) {
            return $this->content;
        }

        $file = $this->client->repositoryFiles()->getFile($this->projectIdentifier, $this->path, $this->sha);

        if (isset($file['content'])) {
            return $this->content = (string) base64_decode($file['content'], true);
        }

        throw new \RuntimeException(sprintf('Invalid file %s at sha %s', $this->path, $this->sha));
    }
}

<?php
declare(strict_types=1);

namespace Danger\Struct\Github;

use Github\Client;

class File extends \Danger\Struct\File
{
    private ?string $content = null;

    public function __construct(private Client $client, private string $owner, private string $repo, private string $fileName, private string $headSha)
    {
    }

    public function getContent(): string
    {
        if ($this->content !== null) {
            return $this->content;
        }

        $rawDownload = $this->client->repo()->contents()->rawDownload($this->owner, $this->repo, $this->fileName, $this->headSha);
        \assert(is_string($rawDownload));
        $this->content = $rawDownload;

        return $this->content;
    }
}

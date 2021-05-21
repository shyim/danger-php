<?php
declare(strict_types=1);

namespace Danger;

class Config
{
    public const UPDATE_COMMENT_MODE_REPLACE = 'replace';
    public const UPDATE_COMMENT_MODE_UPDATE = 'update';

    /**
     * @var callable[]
     */
    private array $rules = [];

    private string $updateCommentMode = self::UPDATE_COMMENT_MODE_UPDATE;

    private ?string $githubCommentProxyUrl = null;

    public function useRule(callable $closure): static
    {
        $this->rules[] = $closure;

        return $this;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function useCommentMode(string $mode): static
    {
        $this->updateCommentMode = $mode;

        return $this;
    }

    public function getUpdateCommentMode(): string
    {
        return $this->updateCommentMode;
    }

    public function useGithubCommentProxy(string $proxyUrl): static
    {
        $this->githubCommentProxyUrl = $proxyUrl;

        return $this;
    }

    public function getGithubCommentProxy(): ?string
    {
        return $this->githubCommentProxyUrl;
    }
}

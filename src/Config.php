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

    /**
     * @var callable[]
     */
    private array $afterHooks = [];

    private string $updateCommentMode = self::UPDATE_COMMENT_MODE_UPDATE;

    private ?string $githubCommentProxyUrl = null;

    private bool $useThread = false;

    public function useRule(callable $closure): static
    {
        $this->rules[] = $closure;

        return $this;
    }

    public function after(callable $closure): static
    {
        $this->afterHooks[] = $closure;

        return $this;
    }

    /**
     * @return callable[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @return callable[]
     */
    public function getAfterHooks(): array
    {
        return $this->afterHooks;
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

    public function useThreadOnFails(bool $enable = true): static
    {
        $this->useThread = $enable;

        return $this;
    }

    public function isThreadEnabled(): bool
    {
        return $this->useThread;
    }
}

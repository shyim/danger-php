<?php
declare(strict_types=1);

namespace Danger;

class Config
{
    public const UPDATE_COMMENT_MODE_REPLACE = 'replace';
    public const UPDATE_COMMENT_MODE_UPDATE = 'update';

    public const REPORT_LEVEL_FAILURE = 32;
    public const REPORT_LEVEL_WARNING = 16;
    public const REPORT_LEVEL_NOTICE = 8;
    public const REPORT_LEVEL_NONE = 0;

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

    private int $useThreadOn = 0;

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

    /**
     * @deprecated will be removed - use useThreadOn instead
     */
    public function useThreadOnFails(bool $enable = true): static
    {
        $this->useThreadOn = $enable ? self::REPORT_LEVEL_FAILURE : self::REPORT_LEVEL_NONE;

        return $this;
    }

    public function useThreadOn(int $useTreadOn): static
    {
        $this->useThreadOn = $useTreadOn;

        return $this;
    }

    public function isThreadEnabled(): bool
    {
        return $this->useThreadOn > 0;
    }

    public function getUseThreadOn(): int
    {
        return $this->useThreadOn;
    }

    /**
     * Get the highest report level of the given context
     */
    public function getReportLevel(Context $context): int
    {
        if (!$context->hasReports()) {
            return self::REPORT_LEVEL_NONE;
        }

        if ($context->hasFailures()) {
            return self::REPORT_LEVEL_FAILURE;
        }

        if ($context->hasWarnings()) {
            return self::REPORT_LEVEL_WARNING;
        }

        if ($context->hasNotices()) {
            return self::REPORT_LEVEL_NOTICE;
        }

        return self::REPORT_LEVEL_NONE;
    }
}

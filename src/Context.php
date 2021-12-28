<?php
declare(strict_types=1);

namespace Danger;

use function count;
use Danger\Platform\AbstractPlatform;

class Context
{
    /**
     * @var string[]
     */
    private array $failures = [];

    /**
     * @var string[]
     */
    private array $warnings = [];

    /**
     * @var string[]
     */
    private array $notices = [];

    public function __construct(public AbstractPlatform $platform)
    {
    }

    public function failure(string $text): void
    {
        $this->failures[] = $text;
    }

    public function warning(string $text): void
    {
        $this->warnings[] = $text;
    }

    public function notice(string $text): void
    {
        $this->notices[] = $text;
    }

    public function hasReports(): bool
    {
        return $this->hasFailures() || $this->hasNotices() || $this->hasWarnings();
    }

    /**
     * @return string[]
     */
    public function getFailures(): array
    {
        return $this->failures;
    }

    public function hasFailures(): bool
    {
        return count($this->failures) > 0;
    }

    /**
     * @return string[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function hasWarnings(): bool
    {
        return count($this->warnings) > 0;
    }

    /**
     * @return string[]
     */
    public function getNotices(): array
    {
        return $this->notices;
    }

    public function hasNotices(): bool
    {
        return count($this->notices) > 0;
    }
}

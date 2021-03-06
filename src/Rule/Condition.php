<?php
declare(strict_types=1);

namespace Danger\Rule;

use Danger\Context;

class Condition
{
    /**
     * @var callable
     */
    private $condition;

    /**
     * @param callable[] $rules
     */
    public function __construct(callable $condition, private array $rules)
    {
        $this->condition = $condition;
    }

    public function __invoke(Context $context): void
    {
        $cond = $this->condition;

        if (!$cond($context)) {
            return;
        }

        foreach ($this->rules as $rule) {
            $rule($context);
        }
    }
}

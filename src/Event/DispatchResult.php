<?php

declare(strict_types=1);

namespace AcMailer\Event;

use SplQueue;

final class DispatchResult extends SplQueue
{
    /**
     * @param mixed $value
     */
    public function contains($value): bool
    {
        foreach ($this as $response) {
            if ($response === $value) {
                return true;
            }
        }

        return false;
    }
}

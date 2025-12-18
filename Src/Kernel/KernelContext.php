<?php

declare(strict_types=1);

namespace Design\Kernel;

/**
 * Defines what kind of script is running.
 *
 * We use this to adjust what the Kernel creates:
 * - Front / Controller usually need sessions
 * - CLI / Jobs usually don't
 * - Health can be configured either way
 */
enum KernelContext: string
{
    case Front = 'front';
    case Controller = 'controller';
    case Health = 'health';
    case Cli = 'cli';
    case Job = 'job';
}

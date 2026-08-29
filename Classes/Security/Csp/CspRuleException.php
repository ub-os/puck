<?php

declare(strict_types=1);

namespace UBOS\Puck\Security\Csp;

/**
 * Thrown when the "Content-Security-Policy rules" site configuration field
 * contains a rule that cannot be parsed.
 */
final class CspRuleException extends \RuntimeException
{
}

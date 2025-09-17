<?php /** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Plan2net\Routi\Routing\Aspect;

use InvalidArgumentException;
use Override;
use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;

/**
 * Class StaticIntegerRangeMapper
 *
 * @package Plan2net\Routi\Routing\Aspect
 * @author Wolfgang Klinger <wk@plan2.net>
 */
class StaticIntegerRangeMapper implements StaticMappableAspectInterface, \Countable
{
    protected int $start;

    protected int $end;

    /**
     * @param array $settings
     * @throws InvalidArgumentException
     */
    public function __construct(array $settings)
    {
        if (!is_numeric($settings['start'])) {
            throw new \InvalidArgumentException('start must be a number', 1577297576);
        }
        if (!is_numeric($settings['end'])) {
            throw new \InvalidArgumentException('end must be a number', 1577297577);
        }
        $this->start = (int)$settings['start'];
        $this->end = (int)$settings['end'];
    }

    #[Override]
    public function count(): int
    {
        return $this->end - $this->start;
    }

    #[Override]
    public function generate(string $value): ?string
    {
        return (int) $value >= $this->start && (int) $value <= $this->end ? $value : null;
    }

    #[Override]
    public function resolve(string $value): ?string
    {
        return (int) $value >= $this->start && (int) $value <= $this->end ? $value : null;
    }
}

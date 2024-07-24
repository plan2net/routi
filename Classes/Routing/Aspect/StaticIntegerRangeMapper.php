<?php /** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Plan2net\Routi\Routing\Aspect;

use InvalidArgumentException;
use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;

final class StaticIntegerRangeMapper implements StaticMappableAspectInterface, \Countable
{
    protected readonly int $start;

    protected readonly int $end;

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

        if ($this->start < 0) {
            throw new \InvalidArgumentException('start must be larger than zero', 1577297579);
        }

        if ($this->end <= $this->start) {
            throw new \InvalidArgumentException('end must be larger than start', 1577297578);
        }

        if (($this->end - $this->start) > 1000) {
            throw new \LengthException(
                'Range is larger than 1000 items',
                1537696771
            );
        }
    }

    public function count(): int
    {
        return $this->end - $this->start;
    }

    public function generate(string $value): ?string
    {
        return $this->respondWhenInRange($value);
    }

    public function resolve(string $value): ?string
    {
        return $this->respondWhenInRange($value);
    }

    private function respondWhenInRange(string $value): ?string
    {
        return ((int) $value >= $this->start && (int) $value <= $this->end) ? $value : null;
    }
}
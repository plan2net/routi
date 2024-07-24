<?php /** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Plan2net\Routi\Routing\Aspect;

use InvalidArgumentException;
use TYPO3\CMS\Core\Routing\Aspect\StaticRangeMapper;

final class StaticPaddedRangeMapper extends StaticRangeMapper
{
    private string $padString = '';

    private int $padLength = 0;

    private int $padType = STR_PAD_LEFT;

    /**
     * @param array $settings
     * @throws InvalidArgumentException
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(array $settings)
    {
        $start = $settings['start'] ?? null;
        $end = $settings['end'] ?? null;

        $padString = $settings['padString'] ?? null;
        $padLength = $settings['padLength'] ?? null;
        $padType = $settings['padType'] ?? null;

        if (!is_string($start)) {
            throw new InvalidArgumentException('start must be string', 1537277163);
        }
        if (!is_string($end)) {
            throw new InvalidArgumentException('end must be string', 1537277164);
        }
        if (!is_string($padString)) {
            throw new InvalidArgumentException('padString must be string', 1538277165);
        }
        if (!is_int($padLength)) {
            throw new InvalidArgumentException('padLength must be string', 1538277166);
        }
        if (!is_int($padType)) {
            throw new InvalidArgumentException('padType must be integer', 1538277167);
        }
        if (!in_array($padType, [STR_PAD_LEFT, STR_PAD_RIGHT, STR_PAD_BOTH], true)) {
            throw new InvalidArgumentException('padType must be valid (0, 1, 2)', 1538277168);
        }

        $this->settings = $settings;
        $this->start = $start;
        $this->end = $end;

        $this->padString = $padString;
        $this->padLength = $padLength;
        $this->padType = $padType;

        $this->range = $this->buildRange();
    }

    /**
     * @return array
     */
    protected function buildRange(): array
    {
        $padString = $this->padString;
        $padLength = $this->padLength;
        $padType = $this->padType;

        $range = array_map(static function ($item) use ($padString, $padLength, $padType) {
            return str_pad((string)$item, $padLength, $padString, $padType);
        }, range($this->start, $this->end));

        if (count($range) > 1000) {
            throw new \LengthException(
                'Range is larger than 1000 items',
                1537696771
            );
        }

        return $range;
    }
}

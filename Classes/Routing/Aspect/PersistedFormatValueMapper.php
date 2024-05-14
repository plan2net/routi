<?php

declare(strict_types=1);

namespace Plan2net\Routi\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;
use TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;

class PersistedFormatValueMapper extends PersistedAliasMapper
{
    // This is required, because the AspectFactory checks for this trait
    // with 'class_uses' and this does not work for inherited classes
    // using a trait
    use SiteLanguageAwareTrait;

    /**
     * @var string|null
     */
    protected $valuePrefix;

    /**
     * @var string|null
     */
    protected $valuePostfix;

    public function __construct(array $settings)
    {
        parent::__construct($settings);

        $this->valuePrefix = $settings['valuePrefix'] ?? null;
        $this->valuePostfix = $settings['valuePostfix'] ?? null;
    }

    public function generate(string $value): ?string
    {
        // Apply prefix and postfix for value for query
        $value = $this->valuePrefix . $value . $this->valuePostfix;
        $result = parent::generate($value);
        if (null === $result) {
            return null;
        }

        // Remove prefix and postfix again for URL slug
        return $this->purgeRouteValuePrefix($this->stripPrefixPostfix($result));
    }

    public function resolve(string $value): ?string
    {
        $value = $this->routeValuePrefix . $this->purgeRouteValuePrefix($value);
        // Apply prefix and postfix for value for query
        $result = parent::resolve($this->valuePrefix . $value . $this->valuePostfix);
        if (null === $result) {
            return null;
        }

        // Remove prefix and postfix again for value
        return $this->stripPrefixPostfix($result);
    }

    protected function stripPrefixPostfix(string $value): string
    {
        if (null !== $this->valuePrefix || null !== $this->valuePostfix) {
            $pattern = '/'
                . preg_quote((string) $this->valuePrefix, '/')
                . '(.*?)'
                . preg_quote((string) $this->valuePostfix, '/') . '/';
            $value = preg_replace($pattern, '$1', $value);
        }

        return $value;
    }
}

<?php

declare(strict_types=1);

namespace Plan2net\Routi\Routing;

use TYPO3\CMS\Core\Routing\Enhancer\EnhancerInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Explode 'limitToPages' if it is a string to allow a configuration like
 *  limitToPages: '%env(TYPO3_SOME_PID_LIST)%'
 * with a list of pages defined in a .env file.
 */
final class PageRouter extends \TYPO3\CMS\Core\Routing\PageRouter
{
    /**
     * Fetch possible enhancers + aspects based on the current page configuration and
     * the site configuration put into "routeEnhancers".
     *
     * @return EnhancerInterface[]
     */
    protected function getEnhancersForPage(int $pageId, SiteLanguage $language): array
    {
        $enhancers = [];
        $routeEnhancers = $this->site->getConfiguration()['routeEnhancers'] ?? [];
        foreach ($routeEnhancers as $enhancerConfiguration) {
            $limitToPages = $this->parseLimitToPages($enhancerConfiguration['limitToPages'] ?? []);
            if ($this->isPageIdRestricted($pageId, $limitToPages)) {
                continue;
            }
            $enhancers[] = $this->createEnhancer($enhancerConfiguration, $language);
        }

        return $enhancers;
    }

    private function parseLimitToPages(mixed $limitToPages): array
    {
        if (is_string($limitToPages)) {
            return GeneralUtility::intExplode(',', $limitToPages);
        }

        return is_array($limitToPages) ? $limitToPages : [];
    }

    private function isPageIdRestricted(int $pageId, array $limitToPages): bool
    {
        return !empty($limitToPages) && !in_array($pageId, $limitToPages, true);
    }

    private function createEnhancer(array $enhancerConfiguration, SiteLanguage $language): EnhancerInterface
    {
        $enhancerType = $enhancerConfiguration['type'] ?? '';
        $enhancer = $this->enhancerFactory->create($enhancerType, $enhancerConfiguration);
        if (!empty($enhancerConfiguration['aspects'])) {
            $aspects = $this->aspectFactory->createAspects(
                $enhancerConfiguration['aspects'],
                $language,
                $this->site
            );
            $enhancer->setAspects($aspects);
        }

        return $enhancer;
    }
}

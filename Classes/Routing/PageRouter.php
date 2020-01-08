<?php
declare(strict_types=1);

namespace Plan2net\Routi\Routing;

use TYPO3\CMS\Core\Routing\Enhancer\EnhancerInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PageRouter
 *
 * Explode 'limitToPages' if it is a string to allow a configuration like
 *  limitToPages: '%env(TYPO3_SOME_PID_LIST)%'
 * with a list of pages defined in a .env file.
 *
 * @package Plan2net\Routi\Routing
 * @author Wolfgang Klinger <wk@plan2.net>
 */
class PageRouter extends \TYPO3\CMS\Core\Routing\PageRouter
{
    /**
     * Fetch possible enhancers + aspects based on the current page configuration and
     * the site configuration put into "routeEnhancers".
     *
     * @param int $pageId
     * @param SiteLanguage $language
     * @return EnhancerInterface[]
     */
    protected function getEnhancersForPage(int $pageId, SiteLanguage $language): array
    {
        $enhancers = [];
        foreach ($this->site->getConfiguration()['routeEnhancers'] ?? [] as $enhancerConfiguration) {
            $limitToPages = $enhancerConfiguration['limitToPages'] ?? [];
            if (!is_array($limitToPages)) {
                $limitToPages = GeneralUtility::intExplode(',', (string)$limitToPages);
            }
            // Check if there is a restriction to page Ids.
            if (!in_array($pageId, $limitToPages, false)) {
                continue;
            }
            $enhancerType = $enhancerConfiguration['type'] ?? '';
            $enhancer = $this->enhancerFactory->create($enhancerType, $enhancerConfiguration);
            if (!empty($enhancerConfiguration['aspects'] ?? null)) {
                $aspects = $this->aspectFactory->createAspects(
                    $enhancerConfiguration['aspects'],
                    $language
                );
                $enhancer->setAspects($aspects);
            }
            $enhancers[] = $enhancer;
        }

        return $enhancers;
    }
}
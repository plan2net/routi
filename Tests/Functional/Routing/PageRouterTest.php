<?php

declare(strict_types=1);

namespace Plan2net\Routi\Tests\Functional\Routing;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Plan2net\Routi\Routing\PageRouter;

class PageRouterTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/routi',
    ];

    protected array $coreExtensionsToLoad = [
        'core',
        'frontend',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->setUpSiteConfiguration();
    }

    protected function setUpSiteConfiguration(): void
    {
        $configuration = [
            'rootPageId' => 1,
            'base' => 'https://example.com/',
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'English',
                    'navigationTitle' => 'English',
                    'base' => '/',
                    'locale' => 'en_US.UTF-8',
                    'iso-639-1' => 'en',
                    'hreflang' => 'en-US',
                    'direction' => 'ltr',
                    'typo3Language' => 'default',
                    'flag' => 'us',
                    'enabled' => true,
                ],
            ],
            'errorHandling' => [],
            'routes' => [],
        ];

        try {
            /** @var SiteWriter $siteWriter */
            $siteWriter = GeneralUtility::makeInstance(SiteWriter::class);
            if (method_exists($siteWriter, 'createNewBasicSite')) {
                $siteWriter->createNewBasicSite('testing', 1, 'https://example.com/');
            }
        } catch (\Throwable $e) {
        }

        $configDir = Environment::getConfigPath() . '/sites/testing';
        if (!is_dir($configDir)) {
            GeneralUtility::mkdir_deep($configDir);
        }
        $configPath = $configDir . '/config.yaml';
        file_put_contents($configPath, Yaml::dump($configuration));
    }

    #[Test]
    public function pageRouterHandlesStringLimitToPagesFromEnvironmentVariable(): void
    {
        putenv('TYPO3_TEST_PAGE_LIST=10,20,30');
        $_ENV['TYPO3_TEST_PAGE_LIST'] = '10,20,30';

        $configPath = Environment::getConfigPath() . '/sites/testing/config.yaml';
        $configuration = Yaml::parseFile($configPath);
        $configuration['routeEnhancers'] = [
            'TestEnhancer' => [
                'type' => 'Simple',
                'limitToPages' => '%env(TYPO3_TEST_PAGE_LIST)%',
                'routePath' => '/test/{test_param}',
                '_arguments' => [
                    'test_param' => 'test_param',
                ],
            ],
        ];
        file_put_contents($configPath, Yaml::dump($configuration));

        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier('testing');
        $pageRouter = $this->getPageRouterInstance($site);

        // Page 20 is in the limit list -> parameter mapped into path
        $uriAllowed = $pageRouter->generateUri(20, ['test_param' => 'foo']);
        $this->assertSame('/page-20/test/foo', $uriAllowed->getPath());
        $this->assertStringNotContainsString('test_param=', $uriAllowed->getQuery());

        // Page 40 is NOT in the limit list -> parameter stays as query param
        $uriDenied = $pageRouter->generateUri(40, ['test_param' => 'foo']);
        $this->assertSame('/page-40', $uriDenied->getPath());
        $this->assertStringContainsString('test_param=foo', $uriDenied->getQuery());

        putenv('TYPO3_TEST_PAGE_LIST');
        unset($_ENV['TYPO3_TEST_PAGE_LIST']);
    }

    #[Test]
    public function pageRouterHandlesArrayLimitToPages(): void
    {
        $siteConfig = [
            'rootPageId' => 1,
            'base' => 'https://example.com/',
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'English',
                    'base' => '/',
                    'locale' => 'en_US.UTF-8',
                ],
            ],
            'routeEnhancers' => [
                'TestEnhancer' => [
                    'type' => 'Simple',
                    'limitToPages' => [5, 10, 15],
                    'routePath' => '/test/{test_param}',
                ],
            ],
        ];

        $site = new Site('testing', 1, $siteConfig);
        $pageRouter = $this->getPageRouterInstance($site);

        // 10 allowed -> mapped into path
        $uriAllowed = $pageRouter->generateUri(10, ['test_param' => 'foo']);
        $this->assertSame('/page-10/test/foo', $uriAllowed->getPath());
        $this->assertStringNotContainsString('test_param=', $uriAllowed->getQuery());

        // 20 not allowed -> parameter as query
        $uriDenied = $pageRouter->generateUri(20, ['test_param' => 'foo']);
        $this->assertSame('/page-20', $uriDenied->getPath());
        $this->assertStringContainsString('test_param=foo', $uriDenied->getQuery());
    }

    #[Test]
    public function pageRouterWorksWithoutLimitToPages(): void
    {
        $siteConfig = [
            'rootPageId' => 1,
            'base' => 'https://example.com/',
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'English',
                    'base' => '/',
                    'locale' => 'en_US.UTF-8',
                ],
            ],
            'routeEnhancers' => [
                'TestEnhancer' => [
                    'type' => 'Simple',
                    'routePath' => '/test/{test_param}',
                ],
            ],
        ];

        $site = new Site('testing', 1, $siteConfig);
        $pageRouter = $this->getPageRouterInstance($site);

        $uri = $pageRouter->generateUri(10, ['test_param' => 'foo']);
        $this->assertSame('/page-10/test/foo', $uri->getPath());
        $this->assertStringNotContainsString('test_param=', $uri->getQuery());
    }

    #[Test]
    #[DataProvider('provideInvalidLimitToPagesConfigurations')]
    public function pageRouterHandlesInvalidStringGracefully(mixed $limitToPages): void
    {
        $siteConfig = [
            'rootPageId' => 1,
            'base' => 'https://example.com/',
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'English',
                    'base' => '/',
                    'locale' => 'en_US.UTF-8',
                ],
            ],
            'routeEnhancers' => [
                'TestEnhancer' => [
                    'type' => 'Simple',
                    'limitToPages' => $limitToPages,
                    'routePath' => '/test/{test_param}',
                ],
            ],
        ];

        $site = new Site('testing', 1, $siteConfig);
        $pageRouter = $this->getPageRouterInstance($site);

        // Should not throw and must return a valid URI regardless of config quirks
        $uri = $pageRouter->generateUri(10, ['test_param' => 'foo']);
        $this->assertNotEmpty($uri->getPath());
    }

    public static function provideInvalidLimitToPagesConfigurations(): array
    {
        return [
            'empty string' => [''],
            'string with spaces' => ['10, 20, 30'],
            'string with invalid characters' => ['10,abc,30'],
            'mixed valid and invalid' => ['10,20,xyz'],
        ];
    }

    protected function getPageRouterInstance(Site $site): PageRouter
    {
        $request = new ServerRequest(
            new Uri('https://example.com/'),
            'GET'
        );

        return GeneralUtility::makeInstance(
            PageRouter::class,
            $site
        );
    }
}

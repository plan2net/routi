<?php

declare(strict_types=1);

namespace Plan2net\Routi\Tests\Functional\Routing\Aspect;

use PHPUnit\Framework\Attributes\Test;
use Plan2net\Routi\Routing\Aspect\PersistedJoinAliasMapper;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class PersistedJoinAliasMapperTest extends FunctionalTestCase
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
        // minimal content for DB tables used by the mapper
        $this->importCSVDataSet(__DIR__ . '/Fixtures/sys_file.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/sys_file_metadata.csv');
    }

    protected function buildMapper(): PersistedJoinAliasMapper
    {
        $settings = [
            'tableName' => 'sys_file',
            'routeFieldName' => 'title',
            'routeValuePrefix' => '',
            'joinTableName' => 'sys_file_metadata',
            'joinCondition' => 'sys_file.uid = sys_file_metadata.file',
        ];

        $mapper = new PersistedJoinAliasMapper($settings);
        // Provide a default SiteLanguage context
        $site = new Site('testing', 1, [
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
        ]);
        $mapper->setSiteLanguage($site->getDefaultLanguage());
        return $mapper;
    }

    #[Test]
    public function generateReturnsJoinedTitleForGivenIdentifier(): void
    {
        $mapper = $this->buildMapper();
        $this->assertSame('Cool Image', $mapper->generate('100'));
    }

    #[Test]
    public function resolveReturnsIdentifierForGivenJoinedTitle(): void
    {
        $mapper = $this->buildMapper();
        $this->assertSame('100', $mapper->resolve('Cool Image'));
    }

    #[Test]
    public function resolveReturnsNullForUnknownTitle(): void
    {
        $mapper = $this->buildMapper();
        $this->assertNull($mapper->resolve('Does Not Exist'));
    }
}


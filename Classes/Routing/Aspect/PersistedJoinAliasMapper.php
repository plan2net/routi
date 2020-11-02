<?php /** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection TraitsPropertiesConflictsInspection */

declare(strict_types=1);

namespace Plan2net\Routi\Routing\Aspect;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;
use TYPO3\CMS\Core\Routing\Aspect\PersistenceDelegate;
use TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PersistedJoinAliasMapper
 * @package Plan2net\Routi\Routing\Aspect
 * @author Wolfgang Klinger <wk@plan2.net>
 *
 * Example:
 *   routeEnhancers:
 *     EventsPlugin:
 *       type: Extbase
 *       extension: Events2
 *       plugin: Pi1
 *       routes:
 *         - { routePath: '/clip/{clip}', _controller: 'Controller::detail', _arguments: {'clip': 'file_id'} }
 *       defaultController: 'Controller::list'
 *       aspects:
 *         clip:
 *           type: PersistedJoinAliasMapper
 *           tableName: sys_file
 *           joinTableName: sys_file_metadata
 *           joinCondition: sys_file.uid = sys_file_metadata.file
 *           # uses the field name of the join table, no table prefix!
 *           routeFieldName: title
 */
class PersistedJoinAliasMapper extends PersistedAliasMapper
{
    // This is required, because the AspectFactory checks for this trait
    // with 'class_uses' and this does not work for inherited classes
    // using a trait
    use SiteLanguageAwareTrait;

    /**
     * @var string
     */
    protected $joinTableName;

    /**
     * @var string
     */
    protected $joinCondition;

    /**
     * PersistedJoinAliasMapper constructor.
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        parent::__construct($settings);

        $joinTableName = $settings['joinTableName'] ?? null;
        $joinCondition = $settings['joinCondition'] ?? null;

        if (!is_string($joinTableName)) {
            throw new \InvalidArgumentException(
                'joinTableName must be string',
                1537277141
            );
        }
        if (!is_string($joinCondition)) {
            throw new \InvalidArgumentException(
                'joinCondition must be string',
                1537277142
            );
        }

        $this->joinTableName = $joinTableName;
        $this->joinCondition = $joinCondition;
    }

    /**
     * @return string[]
     */
    protected function buildPersistenceFieldNames(): array
    {
        return array_filter([
            $this->tableName . '.uid',
            $this->tableName . '.pid',
            $this->joinTableName . '.' . $this->routeFieldName,
            $GLOBALS['TCA'][$this->tableName]['ctrl']['languageField'] ? $this->tableName . '.' . $GLOBALS['TCA'][$this->tableName]['ctrl']['languageField'] : null,
            $GLOBALS['TCA'][$this->tableName]['ctrl']['transOrigPointerField'] ? $this->tableName . '.' . $GLOBALS['TCA'][$this->tableName]['ctrl']['transOrigPointerField'] : null,
        ]);
    }

    protected function findByIdentifier(string $value): ?array
    {
        $queryBuilder = $this->createQueryBuilder();
        $result = $queryBuilder
            ->select(...$this->persistenceFieldNames)
            ->join(
                $this->tableName,
                $this->joinTableName,
                $this->joinTableName,
                $this->joinCondition
            )
            ->where($queryBuilder->expr()->eq(
                $this->tableName . '.uid',
                $queryBuilder->createNamedParameter($value, \PDO::PARAM_INT)
            ))
            ->execute()
            ->fetchAssociative();

        return $result !== false ? $result : null;
    }

    protected function findByRouteFieldValue(string $value): ?array
    {
        $queryBuilder = $this->createQueryBuilder();
        $result = $queryBuilder
            ->select(...$this->persistenceFieldNames)
            ->join(
                $this->tableName,
                $this->joinTableName,
                $this->joinTableName,
                $this->joinCondition
            )
            ->where($queryBuilder->expr()->eq(
                $this->joinTableName . '.' . $this->routeFieldName,
                $queryBuilder->createNamedParameter($value, \PDO::PARAM_STR)
            ))
            ->execute()
            ->fetchAssociative();

        return $result !== false ? $result : null;
    }
}

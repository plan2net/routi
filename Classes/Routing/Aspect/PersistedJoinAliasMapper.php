<?php /** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection TraitsPropertiesConflictsInspection */

declare(strict_types=1);

namespace Plan2net\Routi\Routing\Aspect;

use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;
use TYPO3\CMS\Core\Routing\Aspect\PersistenceDelegate;
use TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;

final class PersistedJoinAliasMapper extends PersistedAliasMapper
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
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        parent::__construct($settings);

        $this->joinTableName = $settings['joinTableName'] ?? null;
        $this->joinCondition = $settings['joinCondition'] ?? null;

        if (!is_string($this->joinTableName) || !is_string($this->joinCondition)) {
            throw new \InvalidArgumentException('joinTableName and joinCondition must be strings.');
        }
    }

    /**
     * @return string[]
     */
    protected function buildPersistenceFieldNames(): array
    {
        $fields = [
            $this->tableName . 'uid',
            $this->tableName . 'pid',
            "$this->joinTableName.$this->routeFieldName",
        ];

        $ctrl = $GLOBALS['TCA'][$this->tableName]['ctrl'] ?? [];
        if (isset($ctrl['languageField'])) {
            $fields[] = "$this->tableName.{$ctrl['languageField']}";
        }

        if (isset($ctrl['transOrigPointerField'])) {
            $fields[] = "$this->tableName.{$ctrl['transOrigPointerField']}";
        }

        return $fields;
    }

    /**
     * @throws \Exception
     */
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
                $this->tableName . 'uid',
                $queryBuilder->createNamedParameter($value, \PDO::PARAM_INT)
            ))
            ->executeQuery()
            ->fetchAssociative();

        return $result !== false ? $result : null;
    }

    /**
     * @throws \Exception
     */
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
                "$this->joinTableName.$this->routeFieldName",
                $queryBuilder->createNamedParameter($value, \PDO::PARAM_STR)
            ))
            ->executeQuery()
            ->fetchAssociative();

        return $result !== false ? $result : null;
    }
}

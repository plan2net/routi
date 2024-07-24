# Routi

The little TYPO3 CMS routing helpers.

# Page Router

The custom `PageRouter` explodes `limitToPages` by a comma, if the value is a string to allow a configuration like
```yaml
limitToPages: '%env(TYPO3_SOME_PID_LIST)%'
```
with a list of pages defined in a `.env` file.

Example site `config.yaml`:

```yaml
routeEnhancers:
  Events:
    type: Extbase
    limitToPages: '%env(TYPO3_EVENTS_DETAIL_PID_LIST)%'
    extension: News
    plugin: Pi1
    …
```

where the `.env` file contains
```dotenv
TYPO3_EVENTS_DETAIL_PID_LIST=123,456,789
```

# Routing Aspects

## PersistedJoinAliasMapper

This mapper is used to map a field of a join table to a route parameter. The join table must be defined in the `joinTableName` and the join condition in the `joinCondition`. The `routeFieldName` is the field name of the join table your are interested in.

Example site `config.yaml`:
```yaml
routeEnhancers:
  Tv:
    type: Extbase
    …
    routes:
      - { routePath: '/clip/{clip}', _controller: 'TvController::detail', _arguments: { 'clip': 'file_id' } }
    aspects:
      clip:
        type: PersistedJoinAliasMapper
        tableName: sys_file
        joinTableName: sys_file_metadata
        joinCondition: sys_file.uid = sys_file_metadata.file
        # Uses the field name of the join table, no table prefix!
        routeFieldName: title
```

## StaticIntegerRangeMapper

@todo

## StaticPaddedRangeMapper

Example:

```yaml
routeEnhancers:
  …
  aspects:
    field:
      type: StaticPaddedRangeMapper
      start: '1'
      end: '12'
      padString: '0'
      padLength: 2
      # STR_PAD_LEFT = 0
      padType: 0
```

`padType` can be one of the following _PHP_ constants:
- `STR_PAD_LEFT` = 0
- `STR_PAD_RIGHT` = 1
- `STR_PAD_BOTH` = 2

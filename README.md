# Routi

[![Tests](https://github.com/plan2net/routi/actions/workflows/tests.yml/badge.svg)](https://github.com/plan2net/routi/actions/workflows/tests.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2%20|%208.3%20|%208.4-blue.svg)](https://www.php.net/)
[![TYPO3](https://img.shields.io/badge/TYPO3-12.4%20|%2013.4-orange.svg)](https://typo3.org/)
[![License](https://img.shields.io/badge/License-GPL%202.0%2B-green.svg)](LICENSE)

The little TYPO3 CMS routing helpers.

Supported: PHP 8.2–8.4, TYPO3 12.4 / 13.4.

# Installation

Install via Composer:

```bash
composer require plan2net/routi
```

Then configure the aspects in your site configuration as needed (examples below).

# Page Router

Enhances TYPO3’s PageRouter to accept `limitToPages` as either an array of page IDs or a comma‑separated string (including values provided via `%env(...)%`). This lets you scope route enhancers to specific pages while keeping lists in environment variables.

Example (site config):

```yaml
routeEnhancers:
  MyEnhancer:
    type: Simple
    routePath: "/test/{param}"
    limitToPages: "%env(TYPO3_PAGE_LIST)%"  # e.g. "10,20,30"
    _arguments:
      param: param
```

Notes:
- `limitToPages` may be an array (`[5,10,15]`) or a string ("5,10,15").
- Non‑numeric tokens are ignored gracefully; routing still works.
- If omitted, the enhancer applies to all pages.

# Routing Aspects

## Why These Aspects

These helpers close small but practical gaps in TYPO3’s core aspects:
- Core `PersistedAliasMapper` looks up the URL value on the same table as the record. Many real cases (e.g., file titles in `sys_file_metadata`) store the human value in a related table. `PersistedJoinAliasMapper` adds an explicit SQL join so you can keep data normalized without duplicating fields or writing custom slugs, while still benefiting from core’s language awareness.
- Core `StaticRangeMapper` builds a concrete list of values. For simple numeric constraints, `StaticIntegerRangeMapper` avoids generating the full range and just validates bounds — a lightweight fit for day, page, or year segments.
- Core range mappers don’t support fixed‑width (zero‑padded or custom‑padded) segments. `StaticPaddedRangeMapper` pads values to a target length with configurable pad string and direction, enabling patterns like `01..12` for months or other fixed‑width tokens.

## PersistedJoinAliasMapper

Builds speaking URLs from a related table by joining another table when resolving/generating route field values. Useful when the human‑readable value lives in a different table (e.g., `sys_file_metadata.title` for `sys_file`).

Config keys:
- `tableName`: base table
- `joinTableName`: table to join
- `joinCondition`: SQL join condition
- `routeFieldName`: field (from the join table) used in the URL
- Inherits other options from TYPO3’s `PersistedAliasMapper` (e.g., `routeValuePrefix`).

Example:

```yaml
routeEnhancers:
  Assets:
    type: Extbase
    extension: Vendor
    plugin: File
    routes:
      - { routePath: "/file/{file}", _controller: "File::show", _arguments: { file: uid } }
    defaultController: "File::list"
    aspects:
      file:
        type: PersistedJoinAliasMapper
        tableName: sys_file
        joinTableName: sys_file_metadata
        joinCondition: sys_file.uid = sys_file_metadata.file
        routeFieldName: title
```

## StaticIntegerRangeMapper

Constrains a parameter to an integer range. Values outside the range are rejected (no match).

Example:

```yaml
aspects:
  day:
    type: StaticIntegerRangeMapper
    start: "1"
    end: "31"
```

## StaticPaddedRangeMapper

Like TYPO3’s `StaticRangeMapper`, but returns values padded to a fixed length using a custom pad string and type. Ideal for zero‑padded segments such as months `01..12`.

Config keys:
- `start`, `end`: string numbers delimiting the range
- `padString`: pad fill (e.g., `"0"` or `"XY"`)
- `padLength`: total length after padding
- `padType`: one of `STR_PAD_LEFT` (0), `STR_PAD_RIGHT` (1), `STR_PAD_BOTH` (2)

Example (months):

```yaml
aspects:
  month:
    type: StaticPaddedRangeMapper
    start: "1"
    end: "12"
    padString: "0"
    padLength: 2
    padType: 0   # STR_PAD_LEFT
```

# Activation

Installing the extension auto‑registers the custom PageRouter and the three aspect types. Configure them in your site `config.yaml` as shown above.

<?php

use Plan2net\Routi\Routing\Aspect\PersistedJoinAliasMapper;
use Plan2net\Routi\Routing\Aspect\StaticIntegerRangeMapper;
use Plan2net\Routi\Routing\Aspect\StaticPaddedRangeMapper;
use TYPO3\CMS\Core\Routing\PageRouter;

(static function () {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][PageRouter::class] = [
        'className' => \Plan2net\Routi\Routing\PageRouter::class
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['PersistedJoinAliasMapper'] =
        PersistedJoinAliasMapper::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['StaticIntegerRangeMapper'] =
        StaticIntegerRangeMapper::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['StaticPaddedRangeMapper'] =
        StaticPaddedRangeMapper::class;
})();

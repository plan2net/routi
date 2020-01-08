<?php

defined('TYPO3_MODE') or die();

(static function () {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Routing\PageRouter::class] = array(
        'className' => \Plan2net\Routi\Routing\PageRouter::class
    );
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['PersistedJoinAliasMapper'] =
        \Plan2net\Routi\Routing\Aspect\PersistedJoinAliasMapper::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['StaticIntegerRangeMapper'] =
        \Plan2net\Routi\Routing\Aspect\StaticIntegerRangeMapper::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['StaticPaddedRangeMapper'] =
        \Plan2net\Routi\Routing\Aspect\StaticPaddedRangeMapper::class;
})();

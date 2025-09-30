<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['BE_MOD']['system']['entityImport'] = [
    'tables' => ['tl_entity_import_source', 'tl_entity_import_config'],
    'import' => [HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'import'],
    'dryRun' => [HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'dryRun'],
];

$GLOBALS['BE_MOD']['system']['entityImportQuick'] = [
    'tables' => ['tl_entity_import_quick_config'],
    'import' => [HeimrichHannot\EntityImportBundle\DataContainer\EntityImportQuickConfigContainer::class, 'import'],
    'dryRun' => [HeimrichHannot\EntityImportBundle\DataContainer\EntityImportQuickConfigContainer::class, 'dryRun'],
];

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_entity_import_source'] = 'HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel';
$GLOBALS['TL_MODELS']['tl_entity_import_config'] = 'HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel';
$GLOBALS['TL_MODELS']['tl_entity_import_quick_config'] = 'HeimrichHannot\EntityImportBundle\Model\EntityImportQuickConfigModel';
$GLOBALS['TL_MODELS']['tl_entity_import_cache'] = 'HeimrichHannot\EntityImportBundle\Model\EntityImportCacheModel';

/*
 * Hooks
 */
$GLOBALS['TL_HOOKS']['sqlGetFromDca']['huhEntityImportBundle'] = [\HeimrichHannot\EntityImportBundle\EventListener\Contao\SqlGetFromDcaEventListener::class, '__invoke'];

/*
 * Backend widgets
 */
$GLOBALS['BE_FFL']['entityImportProgress'] = 'HeimrichHannot\EntityImportBundle\Widget\ImportProgress';

/*
 * Crons
 */
$GLOBALS['TL_CRON']['minutely'][] = [HeimrichHannot\EntityImportBundle\Controller\PoorManCronController::class, 'runMinutely'];
$GLOBALS['TL_CRON']['hourly'][] = [HeimrichHannot\EntityImportBundle\Controller\PoorManCronController::class, 'runHourly'];
$GLOBALS['TL_CRON']['daily'][] = [HeimrichHannot\EntityImportBundle\Controller\PoorManCronController::class, 'runDaily'];
$GLOBALS['TL_CRON']['weekly'][] = [HeimrichHannot\EntityImportBundle\Controller\PoorManCronController::class, 'runWeekly'];
$GLOBALS['TL_CRON']['monthly'][] = [HeimrichHannot\EntityImportBundle\Controller\PoorManCronController::class, 'runMonthly'];

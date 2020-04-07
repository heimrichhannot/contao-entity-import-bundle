<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['entityImport'] = [
    'tables' => [HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel::getTable(), HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel::getTable()],
    'icon'   => '',
    'import' => [HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'import'],
    'dryRun' => [HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'dryRun'],
];

/**
 * Models
 */
$GLOBALS['TL_MODELS'][HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel::getTable()] = 'HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel';
$GLOBALS['TL_MODELS'][HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel::getTable()] = 'HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel';

/**
 * Crons
 */
$GLOBALS['TL_CRON']['minutely'][] = [HeimrichHannot\EntityImportBundle\Controller\PoorManCronController::class, 'runMinutely'];
$GLOBALS['TL_CRON']['hourly'][]     = [HeimrichHannot\EntityImportBundle\Controller\PoorManCronController::class, 'runHourly'];
$GLOBALS['TL_CRON']['daily'][]       = [HeimrichHannot\EntityImportBundle\Controller\PoorManCronController::class, 'runDaily'];
$GLOBALS['TL_CRON']['weekly'][]     = [HeimrichHannot\EntityImportBundle\Controller\PoorManCronController::class, 'runWeekly'];
$GLOBALS['TL_CRON']['monthly'][]   = [HeimrichHannot\EntityImportBundle\Controller\PoorManCronController::class, 'runMonthly'];
<?php

use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportConfigContainer;
use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportQuickConfigContainer;
use HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel;
use HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel;
use HeimrichHannot\EntityImportBundle\Model\EntityImportQuickConfigModel;
use HeimrichHannot\EntityImportBundle\Model\EntityImportCacheModel;
use HeimrichHannot\EntityImportBundle\EventListener\Contao\SqlGetFromDcaEventListener;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['BE_MOD']['system']['entityImport'] = [
    'tables' => ['tl_entity_import_source', 'tl_entity_import_config'],
    'import' => [EntityImportConfigContainer::class, 'import'],
    'dryRun' => [EntityImportConfigContainer::class, 'dryRun'],
];

$GLOBALS['BE_MOD']['system']['entityImportQuick'] = [
    'tables' => ['tl_entity_import_quick_config'],
    'import' => [EntityImportQuickConfigContainer::class, 'import'],
    'dryRun' => [EntityImportQuickConfigContainer::class, 'dryRun'],
];

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_entity_import_source'] = EntityImportSourceModel::class;
$GLOBALS['TL_MODELS']['tl_entity_import_config'] = EntityImportConfigModel::class;
$GLOBALS['TL_MODELS']['tl_entity_import_quick_config'] = EntityImportQuickConfigModel::class;
$GLOBALS['TL_MODELS']['tl_entity_import_cache'] = EntityImportCacheModel::class;

/*
 * Backend widgets
 */
$GLOBALS['BE_FFL']['entityImportProgress'] = 'HeimrichHannot\EntityImportBundle\Widget\ImportProgress';

if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
    $GLOBALS['TL_CSS']['be_entityimportbundle'] = 'bundles/heimrichhannotcontaoentityimport/assets/contao-entity-import-bundle-be.css|static';
    $GLOBALS['TL_JAVASCRIPT']['be_entityimportbundle'] = 'bundles/heimrichhannotcontaoentityimport/assets/contao-entity-import-bundle-be.js|static';
}

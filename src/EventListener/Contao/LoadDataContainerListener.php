<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\EventListener\Contao;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;

class LoadDataContainerListener
{
    protected Utils $utils;
    protected ModelUtil $modelUtil;
    protected ContaoFramework $framework;

    private static $run = false;

    public function __construct(ContaoFramework $framework, Utils $utils, ModelUtil $modelUtil)
    {
        $this->framework = $framework;
        $this->utils = $utils;
        $this->modelUtil = $modelUtil;
    }

    public function __invoke($table)
    {
        if ('tl_entity_import_cache' !== $table || !$this->utils->container()->isBackend()) {
            return;
        }

        // only run once
        if (static::$run) {
            return;
        }

        static::$run = true;

        /** @var Database $db */
        $db = $this->framework->getAdapter(Database::class)->getInstance();

        $dca = &$GLOBALS['TL_DCA']['tl_entity_import_cache'];

        if (!$db->fieldExists('useCacheForQuickImporters', 'tl_entity_import_config')) {
            return;
        }

        // add cache fields to tl_entity_import_cache
        if (null === ($importers = $this->modelUtil->findModelInstancesBy('tl_entity_import_config', [
                'tl_entity_import_config.useCacheForQuickImporters=?',
            ], [
                true,
            ]))) {
            return;
        }

        $cacheFieldsBeforeInsertion = array_keys($dca['fields']);

        foreach ($importers->fetchEach('targetTable') as $targetTable) {
            $fields = $db->listFields($targetTable);

            foreach ($fields as $field) {
                if (\in_array($field['name'], $cacheFieldsBeforeInsertion)) {
                    continue;
                }

                $dca['fields'][$field['name']] = [
                    'sql' => $this->transformSqlArrayToString($field),
                ];
            }
        }
    }

    private function transformSqlArrayToString(array $fieldSqlData)
    {
        unset($fieldSqlData['name']);

        if ('index' != $fieldSqlData['type']) {
            unset($fieldSqlData['index'], $fieldSqlData['origtype']);

            // Field type
            if ($fieldSqlData['length']) {
                $fieldSqlData['type'] .= '('.$fieldSqlData['length'].($fieldSqlData['precision'] ? ','.$fieldSqlData['precision'] : '').')';

                unset($fieldSqlData['length'], $fieldSqlData['precision']);
            }

            // Default values
            if (null === $fieldSqlData['default'] || false !== stripos($fieldSqlData['extra'], 'auto_increment') || 'null' == strtolower($fieldSqlData['null']) || \in_array(strtolower($fieldSqlData['type']), ['text', 'tinytext', 'mediumtext', 'longtext', 'blob', 'tinyblob', 'mediumblob', 'longblob'])) {
                unset($fieldSqlData['default']);
            } // Date/time constants (see #5089)
            elseif (\in_array(strtolower($fieldSqlData['default']), ['current_date', 'current_time', 'current_timestamp'])) {
                $fieldSqlData['default'] = 'default '.$fieldSqlData['default'];
            } // Everything else
            else {
                $fieldSqlData['default'] = "default '".$fieldSqlData['default']."'";
            }

            return trim(implode(' ', $fieldSqlData));
        }

        return null;
    }
}

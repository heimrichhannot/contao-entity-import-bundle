<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\EventListener\Contao;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use HeimrichHannot\UtilsBundle\Util\Utils;

class SqlGetFromDcaEventListener
{
    protected Utils $utils;
    protected ContaoFramework $framework;

    public function __construct(ContaoFramework $framework, Utils $utils)
    {
        $this->framework = $framework;
        $this->utils = $utils;
    }

    public function __invoke(array $sqlDcaData)
    {
        if (!$this->utils->container()->isBackend()) {
            return $sqlDcaData;
        }

        /** @var Database $db */
        $db = $this->framework->getAdapter(Database::class)->getInstance();

        if (!$db->tableExists('tl_entity_import_config')) {
            return $sqlDcaData;
        }

        if (!$db->fieldExists('useCacheForQuickImporters', 'tl_entity_import_config')) {
            return $sqlDcaData;
        }

        // add cache fields to tl_entity_import_cache
        if (null === ($importers = $this->utils->model()->findModelInstancesBy('tl_entity_import_config', [
                'tl_entity_import_config.useCacheForQuickImporters=?',
            ], [
                true,
            ]))) {
            return $sqlDcaData;
        }

        $skipFields = [
            'id',
            'cache_ptable',
            'cache_pid',
        ];

        foreach ($importers->fetchEach('targetTable') as $targetTable) {
            $fields = $db->listFields($targetTable);

            foreach ($fields as $field) {
                if (\in_array($field['name'], $skipFields) || 'index' === $field['type']) {
                    continue;
                }

                $sqlDcaData['tl_entity_import_cache']['TABLE_FIELDS'][$field['name']] = '`'.$field['name'].'` '.$this->transformSqlArrayToString($field);
            }
        }

        return $sqlDcaData;
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

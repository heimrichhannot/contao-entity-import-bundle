<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\Database;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DatabaseSource extends AbstractSource
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var StringUtil
     */
    private $stringUtil;
    /**
     * @var DcaUtil
     */
    private $dcaUtil;

    /**
     * AbstractFileSource constructor.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, StringUtil $stringUtil, DcaUtil $dcaUtil)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->stringUtil = $stringUtil;
        $this->dcaUtil = $dcaUtil;

        parent::__construct();
    }

    public function getMappedData(array $options = []): array
    {
        $sourceModel = $this->sourceModel;
        $mapping = \Contao\StringUtil::deserialize($this->sourceModel->fieldMapping, true);

        $mapping = $this->adjustMappingForDcMultilingual($mapping);
        $mapping = $this->adjustMappingForChangeLanguage($mapping);

        // retrieve the source records
        $db = Database::getInstance($sourceModel->row());
        $where = $sourceModel->dbSourceTableWhere ?: '1=1';

        $records = $db->prepare("SELECT * FROM $sourceModel->dbSourceTable WHERE $where")->execute();

        $data = [];

        while ($records->next()) {
            $data[] = $this->getMappedItemData($records->row(), $mapping);
        }

        return $data;
    }

    protected function adjustMappingForDcMultilingual(array $mapping)
    {
        // DC_Multilingual
        if (!class_exists('\Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle') || !$this->sourceModel->addDcMultilingualSupport) {
            return $mapping;
        }

        $table = $this->sourceModel->dbSourceTable;

        // id is needed mandatory for fixing the foreign key (langPid) with the new ids
        $mapping[] = [
            'name' => '__id',
            'valueType' => 'source_value',
            'sourceValue' => 'id',
            'skip' => true,
        ];

        $this->dcaUtil->loadDc($table);

        $dca = $GLOBALS['TL_DCA'][$table];

        $langPidField = $dca['config']['langPid'] ?? 'langPid';
        $languageField = $dca['config']['langColumnName'] ?? 'language';

        $mapping[] = [
            'name' => 'langPid',
            'valueType' => 'source_value',
            'sourceValue' => $langPidField,
        ];

        $mapping[] = [
            'name' => 'language',
            'valueType' => 'source_value',
            'sourceValue' => $languageField,
        ];

        if (class_exists('HeimrichHannot\DcMultilingualUtilsBundle\ContaoDcMultilingualUtilsBundle') && isset($dca['config']['langPublished'])) {
            $publishedField = $dca['config']['langPublished'] ?? 'langPublished';

            $mapping[] = [
                'name' => 'langPublished',
                'valueType' => 'source_value',
                'sourceValue' => $publishedField,
            ];

            if ($dca['config']['langStart']) {
                $publishedStartField = $dca['config']['langStart'] ?? 'langStart';
                $publishedStopField = $dca['config']['langStop'] ?? 'langStop';

                $mapping[] = [
                    'name' => 'langStart',
                    'valueType' => 'source_value',
                    'sourceValue' => $publishedStartField,
                ];

                $mapping[] = [
                    'name' => 'langStop',
                    'valueType' => 'source_value',
                    'sourceValue' => $publishedStopField,
                ];
            }
        }

        return $mapping;
    }

    protected function adjustMappingForChangeLanguage(array $mapping)
    {
        // DC_Multilingual
        if (!class_exists('\Terminal42\ChangeLanguage\Language') || !$this->sourceModel->addChangeLanguageSupport) {
            return $mapping;
        }

        // id is needed mandatory for fixing the foreign key (languageMain) with the new ids
        $mapping[] = [
            'name' => '__id',
            'valueType' => 'source_value',
            'sourceValue' => 'id',
            'skip' => true,
        ];

        $mapping[] = [
            'name' => 'languageMain',
            'valueType' => 'source_value',
            'sourceValue' => 'languageMain',
        ];

        return $mapping;
    }
}

<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\Config;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Date;
use Contao\StringUtil;
use HeimrichHannot\EntityImportBundle\Importer\ImporterFactory;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;

class EntityImportConfigContainer
{
    const SORTING_MODE_TARGET_FIELDS = 'target_fields';

    const SORTING_MODES = [
        self::SORTING_MODE_TARGET_FIELDS,
    ];

    const DELETION_MODE_MIRROR = 'mirror';
    const DELETION_MODE_TARGET_FIELDS = 'target_fields';

    const DELETION_MODES = [
        self::DELETION_MODE_MIRROR,
        self::DELETION_MODE_TARGET_FIELDS,
    ];

    /**
     * @var Request
     */
    private $request;

    /**
     * @var UrlUtil
     */
    private $urlUtil;

    /**
     * @var ModelUtil
     */
    private $modelUtil;

    /**
     * @var ImporterFactory
     */
    private $importerFactory;
    /**
     * @var DatabaseUtil
     */
    private $databaseUtil;
    /**
     * @var ArrayUtil
     */
    private $arrayUtil;

    /**
     * EntityImportConfigContainer constructor.
     */
    public function __construct(Request $request, ImporterFactory $importerFactory, UrlUtil $urlUtil, ModelUtil $modelUtil, DatabaseUtil $databaseUtil, ArrayUtil $arrayUtil)
    {
        $this->request = $request;
        $this->urlUtil = $urlUtil;
        $this->modelUtil = $modelUtil;
        $this->importerFactory = $importerFactory;
        $this->databaseUtil = $databaseUtil;
        $this->arrayUtil = $arrayUtil;
    }

    public function initPalette(?DataContainer $dc)
    {
        $dca = &$GLOBALS['TL_DCA'][$dc->table];

        if (null === ($configModel = $this->modelUtil->findModelInstanceByPk($dc->table, $dc->id)) || !$configModel->targetTable) {
            $dca['palettes']['default'] = '{general_legend},title,targetTable;';

            return;
        }
    }

    public function getAllTargetTables(?DataContainer $dc): array
    {
        return array_values(Database::getInstance()->listTables(null, true));
    }

    public function getSourceFields(?DataContainer $dc): array
    {
        $options = [];

        if (null === ($configModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $dc->id))) {
            return $options;
        }

        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $configModel->pid))) {
            return $options;
        }

        $mapping = StringUtil::deserialize($sourceModel->fieldMapping, true);

        if (!\is_array($mapping) || empty($mapping)) {
            return $options;
        }

        foreach ($mapping as $data) {
            if (null === $data['sourceValue']) {
                $options[$data['name']] = $data['name'];
            } else {
                $options[$data['name']] = $data['name'].' ['.$data['sourceValue'].']';
            }
        }

        return $options;
    }

    public function getTargetFields(?DataContainer $dc): array
    {
        $options = [];

        if (null === ($configModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $dc->id)) || !$configModel->targetTable) {
            return $options;
        }

        $fields = Database::getInstance()->listFields($configModel->targetTable);

        if (!\is_array($fields) || empty($fields)) {
            return $options;
        }

        foreach ($fields as $field) {
            if (\in_array('index', $field, true)) {
                continue;
            }

            $options[$field['name']] = $field['name'].' ['.$field['origtype'].']';
        }

        return $options;
    }

    public function import()
    {
        $this->runImport(false);
    }

    public function dryRun()
    {
        $this->runImport(true);
    }

    public function listItems(array $row): string
    {
        return '<div class="tl_content_left">'.$row['title'].' <span style="color:#999;padding-left:3px">['.Date::parse(Config::get('datimFormat'), $row['dateAdded']).']</span></div>';
    }

    private function runImport(bool $dry = false)
    {
        $config = $this->request->getGet('id');

        if (null === ($configModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $config))) {
            throw new \Exception(sprintf('Entity config model of ID %s not found', $config));
        }

        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $configModel->pid))) {
            throw new \Exception(sprintf('Entity source model of ID %s not found', $configModel->pid));
        }

        $importer = $this->importerFactory->createInstance($configModel->id);
        $importer->setDryRun($dry);
        $importer->run();

        throw new RedirectResponseException($this->urlUtil->addQueryString('id='.$sourceModel->id, $this->urlUtil->removeQueryString(['key', 'id'])));
    }
}

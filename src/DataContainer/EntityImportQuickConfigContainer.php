<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\EntityImportBundle\Importer\ImporterFactory;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EntityImportQuickConfigContainer
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ImporterFactory
     */
    private $importerFactory;
    /**
     * @var UrlUtil
     */
    private $urlUtil;
    /**
     * @var DcaUtil
     */
    private $dcaUtil;

    public function __construct(
        ModelUtil $modelUtil,
        EventDispatcherInterface $eventDispatcher,
        Request $request,
        ImporterFactory $importerFactory,
        UrlUtil $urlUtil,
        DcaUtil $dcaUtil
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->modelUtil = $modelUtil;
        $this->request = $request;
        $this->importerFactory = $importerFactory;
        $this->urlUtil = $urlUtil;
        $this->dcaUtil = $dcaUtil;
    }

    public function getImporterConfigs()
    {
        $options = [];

        if (null === ($configs = $this->modelUtil->findAllModelInstances('tl_entity_import_config', [
                'order' => 'tl_entity_import_config.title ASC',
            ]))) {
            return [];
        }

        while ($configs->next()) {
            $options[$configs->id] = $configs->title;
        }

        return $options;
    }

    public function modifyDca(DataContainer $dc)
    {
        if (null === ($quickImporter = $this->modelUtil->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) || !$quickImporter->importerConfig) {
            return;
        }

        if (null === ($importer = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return;
        }

        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $importer->pid))) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_entity_import_quick_config'];

        $this->dcaUtil->loadDc($importer->targetTable);

        $targetDca = &$GLOBALS['TL_DCA'][$importer->targetTable];

        if (EntityImportSourceContainer::RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM === $sourceModel->retrievalType) {
            switch ($sourceModel->fileType) {
                case EntityImportSourceContainer::FILETYPE_CSV:
                    $dca['palettes']['default'] = str_replace('importerConfig', 'importerConfig,fileSRC,fileContent,csvPreviewList', $dca['palettes']['default']);

                    if (isset($targetDca['config']['ptable']) && $targetDca['config']['ptable'] && Database::getInstance()->fieldExists('pid', $importer->targetTable)) {
                        $dca['palettes']['default'] = str_replace('importerConfig', 'fileContent,parentEntity', $dca['palettes']['default']);
                    }

                    break;
            }
        }
    }

    public function getParentEntitiesAsOptions(\Contao\DataContainer $dc)
    {
        if (null === ($quickImporter = $this->modelUtil->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) || !$quickImporter->importerConfig) {
            return [];
        }

        if (null === ($importer = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return [];
        }

        $dca = &$GLOBALS['TL_DCA'][$importer->targetTable];

        if (!isset($dca['config']['ptable'])) {
            return [];
        }

        return System::getContainer()->get('huh.utils.choice.model_instance')->getCachedChoices([
            'dataContainer' => $dca['config']['ptable'],
        ]);
    }

    public function import()
    {
        $this->runImport(false);
    }

    public function dryRun()
    {
        $this->runImport(true);
    }

    public function getHeaderFieldsForPreview($config, $widget, \DataContainer $dc)
    {
        if (null === ($quickImporter = $this->modelUtil->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) || !$quickImporter->importerConfig) {
            return [];
        }

        if (null === ($importer = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return [];
        }

        $fields = [];

        foreach (StringUtil::deserialize($importer->fieldMapping, true) as $mapping) {
            $fields[$mapping['columnName']] = $this->dcaUtil->getLocalizedFieldName($mapping['columnName'], $importer->targetTable);
        }

        if (Database::getInstance()->fieldExists('pid', $importer->targetTable) && $quickImporter->parentEntity) {
            $this->dcaUtil->loadDc($importer->targetTable);

            $dca = &$GLOBALS['TL_DCA'][$importer->targetTable];

            if (isset($dca['config']['ptable'])) {
                $fields = array_merge(['pid' => $this->dcaUtil->getLocalizedFieldName('pid', $importer->targetTable)], $fields);
            }
        }

        return $fields;
    }

    public function getItemsForPreview($config, $widget, $dc)
    {
        if (null === ($quickImporter = $this->modelUtil->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) || !$quickImporter->importerConfig) {
            return [];
        }

        if (null === ($importer = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return [];
        }

        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $importer->pid))) {
            return [];
        }

        $this->addParentEntityToFieldMapping($quickImporter, $importer);
        $importer->fileSRC = $quickImporter->fileSRC;

        $importer = $this->importerFactory->createInstance($importer);
        $result = $importer->getMappedItems();

        return $result;
    }

    protected function addParentEntityToFieldMapping($quickImporter, $importer)
    {
        if (!Database::getInstance()->fieldExists('pid', $importer->targetTable) || !$quickImporter->parentEntity) {
            return;
        }

        $this->dcaUtil->loadDc($importer->targetTable);

        $dca = &$GLOBALS['TL_DCA'][$importer->targetTable];

        if (!isset($dca['config']['ptable'])) {
            return;
        }

        $mapping = array_merge([[
            'columnName' => 'pid',
            'valueType' => 'static_value',
            'staticValue' => $quickImporter->parentEntity,
        ]], StringUtil::deserialize($importer->fieldMapping, true));

        $importer->fieldMapping = serialize($mapping);
    }

    private function runImport(bool $dry = false)
    {
        $config = $this->request->getGet('id');

        if (null === ($quickImporter = $this->modelUtil->findModelInstanceByPk('tl_entity_import_quick_config', $config)) || !$quickImporter->importerConfig) {
            return;
        }

        if (null === ($importer = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return;
        }

        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $importer->pid))) {
            return;
        }

        $this->addParentEntityToFieldMapping($quickImporter, $importer);
        $importer->fileSRC = $quickImporter->fileSRC;

        $importer = $this->importerFactory->createInstance($importer);
        $importer->setDryRun($dry);
        $importer->run();

        throw new RedirectResponseException($this->urlUtil->removeQueryString(['key', 'id']));
    }
}

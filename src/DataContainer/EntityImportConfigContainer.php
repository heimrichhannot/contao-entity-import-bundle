<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Date;
use Contao\StringUtil;
use HeimrichHannot\EntityImportBundle\Event\AddConfigFieldMappingPresetsEvent;
use HeimrichHannot\EntityImportBundle\Importer\ImporterFactory;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

    const STATES = [
        self::STATE_READY_FOR_IMPORT,
        self::STATE_SUCCESS,
        self::STATE_FAILED,
    ];

    const STATE_READY_FOR_IMPORT = 'ready_for_import';
    const STATE_SUCCESS = 'success';
    const STATE_FAILED = 'failed';

    protected Request $request;
    protected UrlUtil $urlUtil;
    protected ModelUtil $modelUtil;
    protected ImporterFactory $importerFactory;
    protected DatabaseUtil $databaseUtil;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        Request $request,
        ImporterFactory $importerFactory,
        UrlUtil $urlUtil,
        ModelUtil $modelUtil,
        DatabaseUtil $databaseUtil,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->request = $request;
        $this->urlUtil = $urlUtil;
        $this->modelUtil = $modelUtil;
        $this->importerFactory = $importerFactory;
        $this->databaseUtil = $databaseUtil;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getDryRunOperation($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['useCronInWebContext']) {
            return '';
        }

        return '<a href="'.Controller::addToUrl($href.'&amp;id='.$row['id']).'&rt='.\RequestToken::get().'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
    }

    public function setPreset(?DataContainer $dc)
    {
        if (!($preset = $dc->activeRecord->fieldMappingPresets)) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_entity_import_config'];

        $this->databaseUtil->update('tl_entity_import_config', [
            'fieldMappingPresets' => '',
            'fieldMapping' => serialize($dca['fields']['fieldMappingPresets']['eval']['presets'][$preset]),
        ], 'tl_entity_import_config.id='.$dc->id);
    }

    public function initPalette(?DataContainer $dc)
    {
        $dca = &$GLOBALS['TL_DCA'][$dc->table];

        if (null === ($configModel = $this->modelUtil->findModelInstanceByPk($dc->table, $dc->id)) || !$configModel->targetTable) {
            $dca['palettes']['default'] = '{general_legend},title,targetTable;';

            return;
        }

        if ($configModel->state === static::STATE_READY_FOR_IMPORT) {
            $dca['palettes']['default'] = '{general_legend},importProgress;';

            return;
        }

        // field mapping presets
        $event = $this->eventDispatcher->dispatch(new AddConfigFieldMappingPresetsEvent([], $configModel), AddConfigFieldMappingPresetsEvent::NAME);

        $presets = $event->getPresets();

        if (empty($presets)) {
            unset($dca['fields']['fieldMappingPresets']);
        } else {
            $options = array_keys($presets);

            asort($presets);

            $dca['fields']['fieldMappingPresets']['options'] = $options;
            $dca['fields']['fieldMappingPresets']['eval']['presets'] = $presets;
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
        $this->runImport();
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

        if ($configModel->useCronInWebContext) {
            $configModel->importStarted = $configModel->importProgressCurrent = $configModel->importProgressTotal = $configModel->importProgressSkipped = 0;
            $configModel->state = static::STATE_READY_FOR_IMPORT;
            $configModel->importProgressResult = '';
            $configModel->save();

            throw new RedirectResponseException($this->urlUtil->addQueryString('act=edit', $this->urlUtil->removeQueryString(['key'])));
        }
        $importer = $this->importerFactory->createInstance($configModel->id);
        $importer->setDryRun($dry);
        $result = $importer->run();
        $importer->outputFinalResultMessage($result);

        throw new RedirectResponseException($this->urlUtil->addQueryString('id='.$sourceModel->id, $this->urlUtil->removeQueryString(['key', 'id'])));
    }
}

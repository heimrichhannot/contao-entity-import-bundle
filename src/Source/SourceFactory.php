<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class SourceFactory
{
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var FileUtil
     */
    private $fileUtil;

    public function __construct(ModelUtil $modelUtil, FileUtil $fileUtil)
    {
        $this->modelUtil = $modelUtil;
        $this->fileUtil = $fileUtil;
    }

    public function createInstance(int $sourceModel): ?SourceInterface
    {
        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $sourceModel))) {
            return null;
        }

        $source = null;

        // TODO -> change to config.yml
        switch ($sourceModel->sourceType) {
            case EntityImportSourceContainer::SOURCE_TYPE_CONTAO_FILE_SYSTEM:
                switch ($sourceModel->fileType) {
                    case EntityImportSourceContainer::FILETYPE_JSON:
                        $source = new JSONFileSource($this->fileUtil, $this->modelUtil);
                        break;
                    case EntityImportSourceContainer::FILETYPE_CSV:
                        $source = new CSVFileSource($this->fileUtil, $this->modelUtil);
                        break;
                    default:
                        // TODO: add Event for creating new FileSourceClasses
                        break;
                }
                break;
            case EntityImportSourceContainer::SOURCE_TYPE_ABSOLUTE_PATH:
                break;
            case EntityImportSourceContainer::SOURCE_TYPE_HTTP:
                break;
            default:
                // TODO: add Event for other SourceTypes
                break;
        }

        $source->setFieldMapping(unserialize($sourceModel->fieldMapping));
        $source->setFilePath($sourceModel->id);
        $source->setSourceModel($sourceModel);

        return $source;
    }
}

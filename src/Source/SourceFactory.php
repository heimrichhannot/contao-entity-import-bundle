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
            case EntityImportSourceContainer::TYPE_DATABASE:
                break;
            case EntityImportSourceContainer::TYPE_FILE:
                switch ($sourceModel->fileType) {
                    case EntityImportSourceContainer::FILETYPE_JSON:
                        $source = new JSONFileSource($this->fileUtil);
                        break;
                    case EntityImportSourceContainer::TYPE_FILE:

                        break;
                }
                break;
            default:
                break;
        }

        $source->setFieldMapping($sourceModel->fieldMapping);

        return $source;
    }
}

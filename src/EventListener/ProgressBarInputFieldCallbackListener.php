<?php

namespace HeimrichHannot\EntityImportBundle\EventListener;


use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\EntityImportBundle\Widget\ImportProgressWidget;
use HeimrichHannot\UtilsBundle\Util\Utils;

class ProgressBarInputFieldCallbackListener
{
    public function __construct(protected readonly Utils $utils)
    {
    }

    #[AsCallback(table: 'tl_entity_import_config', target: 'fields.importProgress.input_field')]
    #[AsCallback(table: 'tl_entity_import_quick_config', target: 'fields.importProgress.input_field')]
    public function generateProgressField(DataContainer $dc): string
    {
        $widget = new ImportProgressWidget($this->utils, [
            'id' => 'ctrl_importProgress',
            'name' => 'importProgress',
            'value' => $dc->id,
            'strTable' => 'tl_entity_import_config',
        ]);

        return $widget->generate();
    }

}
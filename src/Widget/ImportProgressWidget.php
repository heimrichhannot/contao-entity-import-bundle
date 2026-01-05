<?php

namespace HeimrichHannot\EntityImportBundle\Widget;

use Contao\Widget;
use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportConfigContainer;
use HeimrichHannot\UtilsBundle\Util\Utils;

class ImportProgressWidget extends Widget
{
    protected $strTemplate = 'be_widget';

    public function __construct(
        private readonly Utils $utils,
        array $arrAttributes = []
    ) {
        parent::__construct($arrAttributes);
    }

    public function generate(): string
    {
        if (!$this->varValue) {
            return '';
        }

        $importConfig = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $this->varValue);

        if (!$importConfig || EntityImportConfigContainer::STATE_READY_FOR_IMPORT !== $importConfig->state) {
            return '';
        }

        $progress = $importConfig->importProgressTotal > 0
            ? round(($importConfig->importProgressCurrent / $importConfig->importProgressTotal) * 100)
            : 0;

        $stateClass = match ($importConfig->state) {
            EntityImportConfigContainer::STATE_SUCCESS => 'success',
            EntityImportConfigContainer::STATE_FAILED => 'error',
            default => 'progress',
        };

        return sprintf(
            '<div class="entity-import-progress" data-config-id="%d">
                <div class="progress-info">
                    <span>%s: <strong id="progress-current-%d">%d</strong> / <strong id="progress-total-%d">%d</strong></span>
                    <span>%s: <strong id="progress-skipped-%d">%d</strong></span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar %s" id="progress-bar-%d" style="width: %d%%"></div>
                </div>
            </div>
            <script>
                (function() {
                    const configId = %d;
                    const table = "%s";
                    const polling = setInterval(function() {
                        fetch("/contao?do=" + table + "&table=" + table + "&id=" + configId + "&ajax=1&action=getProgress&rt=" + Contao.request_token)
                            .then(r => r.json())
                            .then(data => {
                                const bar = document.getElementById("progress-bar-" + configId);
                                const current = document.getElementById("progress-current-" + configId);
                                const total = document.getElementById("progress-total-" + configId);
                                const skipped = document.getElementById("progress-skipped-" + configId);

                                if (bar) bar.style.width = data.progress + "%%";
                                if (current) current.textContent = data.current;
                                if (total) total.textContent = data.total;
                                if (skipped) skipped.textContent = data.skipped;

                                if (data.state !== "%s") {
                                    clearInterval(polling);
                                    setTimeout(() => location.reload(), 1000);
                                }
                            })
                            .catch(() => clearInterval(polling));
                    }, 2000);
                })();
            </script>',
            $importConfig->id,
            $GLOBALS['TL_LANG']['MSC']['entityImport']['progress'] ?? 'Fortschritt',
            $importConfig->id,
            $importConfig->importProgressCurrent,
            $importConfig->id,
            $importConfig->importProgressTotal,
            $GLOBALS['TL_LANG']['MSC']['entityImport']['skipped'] ?? 'Übersprungen',
            $importConfig->id,
            $importConfig->importProgressSkipped,
            $stateClass,
            $importConfig->id,
            $progress,
            $importConfig->id,
            $this->strTable,
            EntityImportConfigContainer::STATE_READY_FOR_IMPORT
        );
    }
}

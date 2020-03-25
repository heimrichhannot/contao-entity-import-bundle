<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

use Contao\Database;
use Contao\Message;
use Contao\Model;
use HeimrichHannot\EntityImportBundle\Event\AfterImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeImportEvent;
use HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Importer implements ImporterInterface
{
    /**
     * @var SourceInterface
     */
    protected $source;

    /**
     * @var EntityImportConfigModel
     */
    protected $configModel;

    /**
     * @var bool
     */
    protected $dryRun;

    /**
     * @var bool
     */
    protected $mergeTable;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var string
     */
    protected $targetTable;

    /**
     * @var DatabaseUtil
     */
    protected $databaseUtil;

    /**
     * @var bool
     */
    protected $isInitialized;

    /**
     * Importer constructor.
     *
     * @param $databaseUtil    DatabaseUtil
     * @param $eventDispatcher EventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher, DatabaseUtil $databaseUtil)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->databaseUtil = $databaseUtil;
    }

    public function init(SourceInterface $source, Model $configModel)
    {
        $this->database = Database::getInstance();
        $this->source = $source;
        $this->configModel = $configModel;
        $this->mergeTable = ($configModel->mergeTable ? $configModel->mergeTable : false);

        $this->isInitialized = true;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): bool
    {
        if (!$this->isInitialized) {
            throw new \Exception('Not initialized, yet.');
        }

        //System::getContainer()->get('huh.utils.model')->callModelMethod('tl', 'findBySpecialSomething', 1, 2, 3, 4)

        $items = $this->getDataFromSource();

        $this->eventDispatcher->dispatch(BeforeImportEvent::NAME, new BeforeImportEvent());

        $this->executeImport($items);

        $this->eventDispatcher->dispatch(AfterImportEvent::NAME, new AfterImportEvent());

        return true;
    }

    public function getDataFromSource(): array
    {
        return $this->source->getMappedData();
    }

    protected function executeImport($items)
    {
        if ($this->database->tableExists($this->targetTable)) {
            try {
                $count = 0;
                $targetTableColumns = $this->database->getFieldNames($this->targetTable);

                foreach ($items as $item) {
                    // check if all columns exist
                    $columnsNotExisting = array_diff(array_keys($item), $targetTableColumns);
                    if (!empty($columnsNotExisting)) {
                        throw new Exception('Fields of target and source differ');
                    }

                    ++$count;

                    if (!$this->dryRun) {
                        if ($this->mergeTable) {
                            $mergeIdentifier = $this->configModel->mergeIdentifierFields;
                            if (empty($mergeIdentifier)) {
                                throw new Exception('No unique identifier fields set.');
                            }
                            $this->databaseUtil->update($this->targetTable, $item, $mergeIdentifier['target'].'=?', [$mergeIdentifier['source']]);
                        } else {
                            $this->databaseUtil->insert($this->targetTable, $item);
                        }
                    }
                }

                if ($count > 0) {
                    Message::addConfirmation(sprintf('Successfully inserted %s records', $count));
                } else {
                    Message::addInfo(sprintf('Nothing to import'));
                }
            } catch (\Exception $e) {
                Message::addError(sprintf('Error inserted %s records. Fehler: %s', $count, $e->getMessage()));
            }
        }
    }
}

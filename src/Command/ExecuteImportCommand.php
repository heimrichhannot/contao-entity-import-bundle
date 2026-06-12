<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Command;

use Contao\CoreBundle\Framework\ContaoFramework;
use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportConfigContainer;
use HeimrichHannot\EntityImportBundle\Importer\ImporterFactory;
use HeimrichHannot\EntityImportBundle\Importer\ImporterInterface;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\LockFactory;

#[AsCommand(
    name: 'huh:entity-import:execute',
    description: 'Runs a given importer config on the command line.'
)]
class ExecuteImportCommand extends Command
{
    protected InputInterface $input;
    protected SymfonyStyle $io;

    public function __construct(
        private readonly Utils $utils,
        private readonly ImporterFactory $importerFactory,
        private readonly ContaoFramework $framework,
        private readonly LockFactory $lockFactory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('config-ids', InputArgument::REQUIRED, 'The importer config ids as a comma separated list');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Run importer without making changes to the database.');
        $this->addOption('web-cron-mode', null, InputOption::VALUE_NONE, 'Companion cron for the website import function. See README for details.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lock = $this->lockFactory->createLock('huh-entity-import-execute');

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');
            return Command::FAILURE;
        }

        try {
            $this->input = $input;
            $this->io = new SymfonyStyle($input, $output);
            $this->framework->initialize();

            $this->import();

            return Command::SUCCESS;
        } finally {
            $lock->release();
        }
        return Command::SUCCESS;
    }

    private function import(): void
    {
        $configIds = explode(',', (string) $this->input->getArgument('config-ids'));
        $dryRun = (bool) $this->input->getOption('dry-run');
        $webCronMode = (bool) $this->input->getOption('web-cron-mode');

        foreach ($configIds as $configId) {
            $configModel = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $configId);

            if (null === $configModel) {
                $this->io->error("Importer config with ID $configId not found.");
                continue;
            }

            if (!$configModel->useCron) {
                $this->io->warning("Importer with config ID $configId hasn't set useCron=1. Skipped.");
                continue;
            }

            if ($webCronMode && $configModel->useCronInWebContext) {
                if (EntityImportConfigContainer::STATE_READY_FOR_IMPORT !== $configModel->state) {
                    continue;
                }

                if ($configModel->importStarted) {
                    continue;
                }

                $configModel->importStarted = time();
                $configModel->importProgressCurrent = 0;
                $configModel->save();
            }

            $language = null;
            if ($configModel->cronLanguage) {
                $language = $GLOBALS['TL_LANGUAGE'];
                $GLOBALS['TL_LANGUAGE'] = $configModel->cronLanguage;
            }

            /** @var ImporterInterface $importer */
            $importer = $this->importerFactory->createInstance($configModel->id);
            $importer->setDryRun($dryRun);
            $importer->setWebCronMode($webCronMode);
            $importer->setInputOutput($this->io);
            $result = $importer->run();

            if ($webCronMode && $configModel->useCronInWebContext && EntityImportConfigContainer::STATE_READY_FOR_IMPORT === $configModel->state) {
                $configModel->refresh();
                $configModel->importFinished = time();
                $configModel->state = 'success' === $result['state']
                    ? EntityImportConfigContainer::STATE_SUCCESS
                    : EntityImportConfigContainer::STATE_FAILED;
                $configModel->save();
            }

            $importer->outputFinalResultMessage($result);

            if (null !== $language) {
                $GLOBALS['TL_LANGUAGE'] = $language;
            }
        }
    }
}

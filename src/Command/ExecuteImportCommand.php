<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Command;

use Contao\CoreBundle\Command\AbstractLockedCommand;
use HeimrichHannot\EntityImportBundle\Importer\ImporterFactory;
use HeimrichHannot\EntityImportBundle\Importer\ImporterInterface;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExecuteImportCommand extends AbstractLockedCommand
{
    /**
     * @var string
     */
    public static $defaultName = 'huh:entity-import:execute';

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var ModelUtil
     */
    private $modelUtil;

    /**
     * @var ImporterFactory
     */
    private $importerFactory;

    /**
     * ExecuteImportCommand constructor.
     */
    public function __construct(ModelUtil $modelUtil, ImporterFactory $importerFactory)
    {
        $this->modelUtil = $modelUtil;
        $this->importerFactory = $importerFactory;
        parent::__construct(null);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('huh:entity-import:execute');
        $this->setDescription('Runs a given importer config on the command line.');
        $this->addArgument('config-id', InputArgument::REQUIRED, 'The importer source id');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Run importer without making changes to the database.');
    }

    /**
     * {@inheritdoc}
     */
    protected function executeLocked(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->io = new SymfonyStyle($input, $output);
        $this->rootDir = $this->getContainer()->getParameter('kernel.project_dir');
        $this->framework = $this->getContainer()->get('contao.framework');
        $this->framework->initialize();

        if ($this->import()) {
            $this->io->success('Import finished');
        } else {
            $this->io->error('Import failed');
        }

        return 0;
    }

    private function import(): bool
    {
        $importerConfigId = $this->input->getArgument('config-id');
        $importerDryRun = $this->input->getOption('dry-run') ?: false;

        if (null === ($configModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $importerConfigId))) {
            $this->io->error('Exporter config with id '.$importerConfigId.' not found.');

            return false;
        }

        if ($configModel->language) {
            $language = $GLOBALS['TL_LANGUAGE'];

            $GLOBALS['TL_LANGUAGE'] = $configModel->language;
        }

        /** @var ImporterInterface $importer */
        $importer = $this->importerFactory->createInstance($configModel->id);
        $importer->setDryRun($importerDryRun);
        $result = $importer->run();

        if ($configModel->language) {
            $GLOBALS['TL_LANGUAGE'] = $language;
        }

        return $result;
    }
}

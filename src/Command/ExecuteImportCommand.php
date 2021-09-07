<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Command;

use Contao\CoreBundle\Command\AbstractLockedCommand;
use Contao\Message;
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

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('huh:entity-import:execute');
        $this->setDescription('Runs a given importer config on the command line.');
        $this->addArgument('config-ids', InputArgument::REQUIRED, 'The importer config ids as a comma separated list');
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

        $this->import();

        return 0;
    }

    private function import()
    {
        $configIds = explode(',', $this->input->getArgument('config-ids'));
        $importerDryRun = $this->input->getOption('dry-run') ?: false;

        foreach ($configIds as $configId) {
            if (null === ($configModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $configId))) {
                $this->io->error("Importer config with ID $configId not found.");
            }

            if (!$configModel->useCron) {
                $this->io->warning("Importer with config ID $configId hasn't set useCron=1. Skipped.");

                continue;
            }

            if ($configModel->cronLanguage) {
                $language = $GLOBALS['TL_LANGUAGE'];

                $GLOBALS['TL_LANGUAGE'] = $configModel->cronLanguage;
            }

            /** @var ImporterInterface $importer */
            $importer = $this->importerFactory->createInstance($configModel->id);
            $importer->setDryRun($importerDryRun);
            $result = $importer->run();

            if ($result) {
                $this->io->success("Importer with config ID $configId finished successfully");
            } else {
                $this->io->error("Importer with config ID $configId failed");

                // transform backend messages to string
                $messages = Message::generate();
                $messages = str_replace('<br>', "\n", $messages);
                $messages = strip_tags($messages);
                Message::reset();

                $this->io->error($messages);
            }

            if ($configModel->cronLanguage) {
                $GLOBALS['TL_LANGUAGE'] = $language;
            }
        }
    }
}

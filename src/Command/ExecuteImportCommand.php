<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Command;

use Symfony\Component\Console\Command\Command;

class ExecuteImportCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'huh:entity-import:execute';

    /**
     * @var string
     */
    private $configId;

    public function __construct(string $configId)
    {
        $this->configId = $configId;

        parent::__construct(null);
    }
}

namespace HeimrichHannot\ContaoExporterBundle\Command;

use Contao\CoreBundle\Command\AbstractLockedCommand;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\System;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportCommand extends AbstractLockedCommand implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var InputInterface
     */
    private $input;

    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('huh:exporter:export')->setDescription('Runs a given exporter config on the command line.');
        $this->addArgument('importerConfig', InputArgument::REQUIRED, 'The importer config id');
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

        if ($this->export()) {
            $this->io->success('Export finished');
        }

        return 0;
    }

    protected function export()
    {
        $exporterConfigId = $this->input->getArgument('exporterConfig');

        if (null === ($exporterConfig = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_exporter', $exporterConfigId))) {
            $this->io->error('Exporter config with id '.$exporterConfigId.' not found.');

            return false;
        }

        if ($exporterConfig->language) {
            $language = $GLOBALS['TL_LANGUAGE'];

            $GLOBALS['TL_LANGUAGE'] = $exporterConfig->language;
        }

        System::getContainer()->get('huh.exporter.action.export')->export($exporterConfig);

        if ($exporterConfig->language) {
            $GLOBALS['TL_LANGUAGE'] = $language;
        }

        return true;
    }
}

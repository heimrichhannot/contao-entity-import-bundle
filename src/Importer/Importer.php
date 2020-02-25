<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

use HeimrichHannot\EntityImportBundle\Source\SourceInterface;

class Importer implements ImporterInterface
{
    public function applySourceMapping(SourceInterface $source): array
    {
        return $source->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function run($dry = false)
    {
        $items = $this->applySourceMapping($this->source);

        foreach ($items as $item) {
            //...
        }
    }
}

<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle;

use HeimrichHannot\EntityImportBundle\DependencyInjection\HeimrichHannotEntityImportExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoEntityImportBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new HeimrichHannotEntityImportExtension();
    }
}

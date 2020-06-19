<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Util;

class EntityImportUtil
{
    public function transformFieldMappingSourceValueToSelect($options)
    {
        $dca = &$GLOBALS['TL_DCA']['tl_entity_import_source']['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['sourceValue'];

        $dca['inputType'] = 'select';
        $dca['options'] = $options;
        $dca['eval']['includeBlankOption'] = true;
        $dca['eval']['mandatory'] = true;
        $dca['eval']['chosen'] = true;
    }
}

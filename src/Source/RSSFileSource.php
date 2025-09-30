<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\StringUtil;
use LitEmoji\LitEmoji;

class RSSFileSource extends AbstractFileSource
{
    public function getPostFieldsAsOptions()
    {
        $items = $this->getChannelItems();

        if (empty($items)) {
            return [];
        }

        $options = [];

        foreach ($items[0] as $field => $value) {
            if (\is_array($value)) {
                continue;
            }

            $options[$field] = $field;
        }

        return $options;
    }

    public function getChannelItems()
    {
        $fileContent = $this->getFileContent(true);

        if (!$fileContent) {
            return [];
        }

        $rss = new \DOMDocument();
        $rss->loadXML($fileContent);

        $items = [];

        foreach ($rss->getElementsByTagName('item') as $key => $node) {
            $item = [
                'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
                'pubDate' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
                'creator' => $node->getElementsByTagName('creator')->item(0)->nodeValue,
                'guid' => $node->getElementsByTagName('guid')->item(0)->nodeValue,
                'description' => $node->getElementsByTagName('description')->item(0)->nodeValue,
                'encoded' => $node->getElementsByTagName('encoded')->item(0)->nodeValue,
            ];

            // categories
            $categories = [];

            foreach ($node->getElementsByTagName('category') as $categoryNode) {
                $categories[] = $categoryNode->nodeValue;
            }

            $item['category'] = implode(', ', $categories);

            $items[] = $item;
        }

        return $items;
    }

    public function getMappedData(array $options = []): array
    {
        $sourceModel = $this->sourceModel;
        $mapping = StringUtil::deserialize($sourceModel->fieldMapping, true);

        $data = [];

        $items = $this->getChannelItems();

        if (\is_array($items) && !empty($items)) {
            foreach ($items as $item) {
                $data[] = $this->getMappedItemData($item, $mapping);
            }
        }

        return $data;
    }

    protected function getMappedItemData(?array $element, array $mapping): array
    {
        $result = [];

        foreach ($mapping as $mappingElement) {
            if ('static_value' === $mappingElement['valueType']) {
                $result[$mappingElement['name']] = $this->insertTagParser->replace($mappingElement['staticValue']);
            } elseif ('source_value' === $mappingElement['valueType']) {
                $value = $element[$mappingElement['sourceValue']];

                // arrays are not supported -> needs to be done in a custom event listener (AfterItemImportEvent in contao-entity-import-bundle)
                if (\is_array($value)) {
                    continue;
                }

                switch ($mappingElement['sourceValue']) {
                    case 'pubDate':
                        $result[$mappingElement['name']] = $this->parseDate($value);

                        break;

                    case 'encoded':
                        $result[$mappingElement['name']] = LitEmoji::encodeHtml($value);

                        break;

                    default:
                        $result[$mappingElement['name']] = null === $value ? '' : $value;

                        break;
                }
            }
        }

        return $result;
    }

    protected function parseDate($date)
    {
        $dateTime = \DateTime::createFromFormat('D, d M Y H:i:s T', $date);

        if (false !== $dateTime) {
            return $dateTime->getTimestamp();
        }

        return 0;
    }
}

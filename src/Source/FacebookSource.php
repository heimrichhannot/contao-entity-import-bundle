<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Message;
use Contao\StringUtil;
use HeimrichHannot\EntityImportBundle\Exception\ApiException;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * The posts of a Facebook page (Graph API /{page-id}/posts), read with a page access token that MetaLogin stores.
 */
class FacebookSource extends AbstractSource
{
    public const POSTS_URL = 'https://graph.facebook.com/%s/%s/posts';

    public const POST_FIELDS = 'id,message,full_picture,permalink_url,created_time,status_type';

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected Utils $utils,
        protected ParameterBagInterface $parameterBag,
        protected InsertTagParser $insertTagParser,
    ) {
        parent::__construct($this->utils, $this->parameterBag, $this->insertTagParser);
    }

    public function getMappedData(array $options = []): array
    {
        $mapping = StringUtil::deserialize($this->sourceModel->fieldMapping, true);

        return array_map(fn (array $post): array => $this->getMappedItemData($post, $mapping), $this->getPosts());
    }

    public function getPostFieldsAsOptions(): array
    {
        return static::getFieldOptions();
    }

    /**
     * The post fields for the field mapping.
     */
    public static function getFieldOptions(): array
    {
        $fields = explode(',', static::POST_FIELDS);

        return array_combine($fields, $fields);
    }

    public function getPosts(): array
    {
        $model = $this->sourceModel;

        if (!$model->facebookPageId || !$model->facebookAccessToken || !$model->metaApiVersion) {
            return [];
        }

        $query = [
            'fields' => static::POST_FIELDS,
            'limit' => 100,
            'access_token' => $model->facebookAccessToken,
        ];

        // required if the app demands it ("Require App Secret"), harmless otherwise
        if ($model->appSecret) {
            $query['appsecret_proof'] = hash_hmac('sha256', $model->facebookAccessToken, $model->appSecret);
        }

        try {
            $result = $this->getJson(sprintf(static::POSTS_URL, $model->metaApiVersion, $model->facebookPageId), $query);
        } catch (ApiException $e) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['MSC']['entityImport']['serviceConnectionError'] ?? '%s', StringUtil::specialchars($e->getMessage())));

            return [];
        }

        return array_values(array_filter($result['data'] ?? [], 'is_array'));
    }

    protected function getMappedItemData(?array $element, array $mapping): array
    {
        $result = [];

        foreach ($mapping as $mappingElement) {
            if ('static_value' === $mappingElement['valueType']) {
                $result[$mappingElement['name']] = $this->insertTagParser->replace((string) $mappingElement['staticValue']);

                continue;
            }

            if ('source_value' !== $mappingElement['valueType']) {
                continue;
            }

            $value = $element[$mappingElement['sourceValue']] ?? null;

            if (\is_array($value)) {
                continue;
            }

            $result[$mappingElement['name']] = match ($mappingElement['sourceValue']) {
                'created_time' => static::parseDate($value),
                'message' => htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                default => $value ?? '',
            };
        }

        return $result;
    }

    /**
     * The Graph API answers in UTC with an offset ("2026-11-05T16:43:33+0000").
     */
    public static function parseDate(mixed $date): int
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:sO', (string) $date);

        return false !== $dateTime ? $dateTime->getTimestamp() : 0;
    }
}

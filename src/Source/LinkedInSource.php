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
 * The posts of a LinkedIn company page (Posts API of the Community Management API, scope r_organization_social).
 */
class LinkedInSource extends AbstractSource
{
    public const API_URL = 'https://api.linkedin.com/rest';

    public const FIELDS = ['id', 'url', 'commentary', 'publishedAt', 'image_url', 'article_url', 'article_title'];

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

        // resolving images costs a request per post, so only if the mapping uses them
        $withImages = \in_array('image_url', array_column($mapping, 'sourceValue'), true);

        return array_map(fn (array $post): array => $this->getMappedItemData($post, $mapping), $this->getPosts($withImages));
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
        return array_combine(static::FIELDS, static::FIELDS);
    }

    /**
     * @return list<array<string, string|int>> The published posts in the page's feed, with the fields in FIELDS
     */
    public function getPosts(bool $withImages = true): array
    {
        $model = $this->sourceModel;

        if (!$model->appPageId || !$model->linkedinAccessToken || !$model->linkedInVersion) {
            return [];
        }

        try {
            $result = $this->getJson(static::API_URL.'/posts', [
                'author' => 'urn:li:organization:'.trim((string) $model->appPageId),
                'q' => 'author',
                'count' => max(1, min(100, (int) $model->numberPosts ?: 20)),
            ], $this->getHeaders());
        } catch (ApiException $e) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['MSC']['entityImport']['serviceConnectionError'] ?? '%s', StringUtil::specialchars($e->getMessage())));

            return [];
        }

        $posts = [];

        foreach ($result['elements'] ?? [] as $element) {
            // drafts, deleted posts and sponsored posts that are not shown in the feed ("dark posts")
            if (!\is_array($element) || 'PUBLISHED' !== ($element['lifecycleState'] ?? 'PUBLISHED')
                || 'NONE' === ($element['distribution']['feedDistribution'] ?? null)) {
                continue;
            }

            $content = $element['content'] ?? [];
            $mediaId = $content['media']['id'] ?? $content['multiImage']['images'][0]['id'] ?? $content['article']['thumbnail'] ?? null;

            $posts[] = [
                'id' => $element['id'],
                'url' => 'https://www.linkedin.com/feed/update/'.$element['id'].'/',
                'commentary' => static::littleTextToPlainText((string) ($element['commentary'] ?? '')),
                'publishedAt' => (int) (($element['publishedAt'] ?? $element['createdAt'] ?? 0) / 1000),
                'image_url' => $withImages && \is_string($mediaId) ? $this->getMediaUrl($mediaId) : '',
                'article_url' => $content['article']['source'] ?? '',
                'article_title' => $content['article']['title'] ?? '',
            ];
        }

        return $posts;
    }

    /**
     * The download URL of an image, the thumbnail of a video; '' if it cannot be read (the post is imported anyway).
     */
    protected function getMediaUrl(string $urn): string
    {
        try {
            if (str_starts_with($urn, 'urn:li:image:')) {
                return $this->getJson(static::API_URL.'/images/'.rawurlencode($urn), [], $this->getHeaders())['downloadUrl'] ?? '';
            }

            if (str_starts_with($urn, 'urn:li:video:')) {
                return $this->getJson(static::API_URL.'/videos/'.rawurlencode($urn), [], $this->getHeaders())['thumbnail'] ?? '';
            }
        } catch (ApiException) {
        }

        return '';
    }

    protected function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->sourceModel->linkedinAccessToken,
            'LinkedIn-Version' => trim((string) $this->sourceModel->linkedInVersion),
            'X-Restli-Protocol-Version' => '2.0.0',
        ];
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

            $value = $element[$mappingElement['sourceValue']] ?? '';

            $result[$mappingElement['name']] = match ($mappingElement['sourceValue']) {
                'commentary', 'article_title' => htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                default => $value,
            };
        }

        return $result;
    }

    /**
     * The commentary in LinkedIn's "little text format" as plain text: mentions become their names, hashtags "#tag",
     * escaped reserved characters the characters.
     */
    public static function littleTextToPlainText(string $text): string
    {
        $unescape = static fn (string $value): string => preg_replace('/\\\\([|{}@\[\]()<>#\\\\*_~])/', '$1', $value);

        // {hashtag|\#|tag}
        $text = preg_replace_callback('/\{hashtag\|\\\\?#\|((?:[^\\\\}]|\\\\.)*)\}/', static fn (array $m): string => '#'.$unescape($m[1]), $text);

        // @[Name](urn:li:organization:123)
        $text = preg_replace_callback('/@\[((?:[^\\\\\]]|\\\\.)*)\]\(urn:li:[^)]+\)/', static fn (array $m): string => $unescape($m[1]), $text);

        return $unescape($text);
    }
}

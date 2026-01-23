<?php

namespace HeimrichHannot\EntityImportBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Message;
use Contao\Model;
use Contao\System;
use Contao\Widget;
use Doctrine\DBAL\Connection;
use HeimrichHannot\EntityImportBundle\Event\AddSourceFieldMappingPresetsEvent;
use HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel;
use HeimrichHannot\EntityImportBundle\Source\AbstractFileSource;
use HeimrichHannot\EntityImportBundle\Source\CSVFileSource;
use HeimrichHannot\EntityImportBundle\Source\RSSFileSource;
use HeimrichHannot\EntityImportBundle\Source\SourceFactory;
use HeimrichHannot\EntityImportBundle\Util\EntityImportUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use League\OAuth2\Client\Provider\Facebook;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntityImportSourceContainer
{
    const TYPE_DATABASE = 'db';
    const TYPE_FILE = 'file';

    const TYPE_YOUTUBE = 'youtube';
    const TYPE_INSTAGRAM = 'instagram';
    const TYPES = [
        self::TYPE_DATABASE,
        self::TYPE_FILE,
        self::TYPE_YOUTUBE,
        self::TYPE_INSTAGRAM
    ];

    const RETRIEVAL_TYPE_HTTP = 'http';
    const RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM = 'contao_file_system';
    const RETRIEVAL_TYPE_ABSOLUTE_PATH = 'absolute_path';

    const RETRIEVAL_TYPES = [
        self::RETRIEVAL_TYPE_HTTP,
        self::RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM,
        self::RETRIEVAL_TYPE_ABSOLUTE_PATH,
    ];

    const FILETYPE_CSV = 'csv';
    const FILETYPE_JSON = 'json';
    const FILETYPE_RSS = 'rss';
    const FILETYPE_XML = 'xml';

    const FILETYPES = [
        self::FILETYPE_CSV,
        self::FILETYPE_JSON,
        self::FILETYPE_RSS,
        self::FILETYPE_XML
    ];

    protected $activeBundles;

    public function __construct(
        private readonly SourceFactory $sourceFactory,
        private readonly Utils $utils,
        private readonly Connection $conn,
        private readonly EntityImportUtil $util,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RouterInterface $router
    ) {
        $this->activeBundles = System::getContainer()->getParameter('kernel.bundles');
    }

    #[AsCallback('tl_entity_import_source', 'config.onsubmit')]
    public function setPreset(?DataContainer $dc): void
    {
        if (!($preset = $dc->activeRecord->fieldMappingPresets)) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_entity_import_source'];

        $this->conn->update('tl_entity_import_source', [
            'fieldMappingPresets' => '',
            'fieldMapping' => serialize($dca['fields']['fieldMappingPresets']['eval']['presets'][$preset]),
        ], ['id' => $dc->id]);
    }

    #[AsCallback('tl_entity_import_source', 'config.load')]
    public function initPalette(?DataContainer $dc): void
    {
        if (null === ($sourceModel = $this->utils->model()->findModelInstanceByPk($dc->table, $dc->id))) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA'][$dc->table];

        // database
        switch ($sourceModel->type) {
            case static::TYPE_DATABASE:
                if (!$sourceModel->dbSourceTable) {
                    $dca['palettes'][static::TYPE_DATABASE] = str_replace('fieldMappingCopier', '', $dca['palettes'][static::TYPE_DATABASE]);
                    $dca['palettes'][static::TYPE_DATABASE] = str_replace('fieldMapping', '', $dca['palettes'][static::TYPE_DATABASE]);
                } else {
                    try {
                        $options = array_values(Database::getInstance()->getFieldNames($sourceModel->dbSourceTable, true));

                        $this->util->transformFieldMappingSourceValueToSelect(
                            array_combine($options, $options)
                        );
                    } catch (\Exception) {
                    }
                }

                break;

            case static::TYPE_FILE:
                $fileType = $sourceModel->fileType;

                switch ($fileType) {
                    case static::FILETYPE_CSV:
                        /** @var CSVFileSource $source */
                        $source = $this->sourceFactory->createInstance($sourceModel->id);

                        $options = [];
                        $fields = $source->getHeadingLine();

                        foreach ($fields as $index => $field) {
                            if ($sourceModel->csvHeaderRow) {
                                $options[' '.$index] = $field.' ['.$GLOBALS['TL_LANG']['MSC']['entityImport']['column'].' '.$index.']';
                            } else {
                                $options[' '.$index] = $GLOBALS['TL_LANG']['MSC']['entityImport']['column'].' '.$index;
                            }
                        }

                        $this->util->transformFieldMappingSourceValueToSelect(
                            $options
                        );

                        $dca['fields']['fileContent']['eval']['rte'] = 'ace';

                        break;

                    case static::FILETYPE_JSON:
                        $dca['fields']['fileContent']['eval']['rte'] = 'ace|json';

                        break;

                    case static::FILETYPE_XML:
                        $dca['fields']['fileContent']['eval']['rte'] = 'ace|xml';

                        break;

                    case static::FILETYPE_RSS:
                        /** @var RSSFileSource $source */
                        $source = $this->sourceFactory->createInstance($sourceModel->id);

                        $options = $source->getPostFieldsAsOptions();

                        $this->util->transformFieldMappingSourceValueToSelect(
                            array_combine($options, $options)
                        );

                        $dca['fields']['fileContent']['eval']['rte'] = 'ace|xml';

                        break;

                    default:
                        break;
                }

                break;
        }

        // field mapping presets
        $event = $this->eventDispatcher->dispatch(
            new AddSourceFieldMappingPresetsEvent([], $sourceModel),
            AddSourceFieldMappingPresetsEvent::NAME
        );

        $presets = $event->getPresets();

        if (empty($presets)) {
            unset($dca['fields']['fieldMappingPresets']);
        } else {
            $options = array_keys($presets);

            asort($presets);

            $dca['fields']['fieldMappingPresets']['options'] = $options;
            $dca['fields']['fieldMappingPresets']['eval']['presets'] = $presets;
        }
    }

    #[AsCallback('tl_entity_import_source', 'fields.fileContent.load')]
    public function onLoadFileContent(?string $value, ?DataContainer $dc)
    {
        if (null === ($sourceModel = $this->utils->model()->findModelInstanceByPk('tl_entity_import_source', $dc->id))) {
            return '';
        }

        if ($sourceModel->type !== static::TYPE_FILE) {
            return '';
        }

        if (!$sourceModel->fileType) {
            return '';
        }

        return $this->getFileContent($sourceModel);
    }

    public function getFileContent(Model $sourceModel)
    {
        /** @var AbstractFileSource $source */
        $source = $this->sourceFactory->createInstance($sourceModel->id);

        switch ($sourceModel->fileType) {
            case static::FILETYPE_CSV:
                return $source->getLinesFromFile(25, true)."\n...";

            case static::FILETYPE_JSON:
                $string = json_decode($source->getFileContent(true));

                return substr(json_encode($string, JSON_PRETTY_PRINT), 0, 50000);

            case static::FILETYPE_XML:
                $xml = simplexml_load_string($source->getFileContent(true));
                $json = json_encode($xml);
                $string = json_decode($json, true);

                return substr(json_encode($string, JSON_PRETTY_PRINT), 0, 50000);

            case static::FILETYPE_RSS:
                return substr($source->getFileContent(true), 0, 50000);
        }

        return $source->getFileContent(true);
    }

    #[AsCallback('tl_entity_import_source', 'fields.dbSourceTable.options')]
    public function getAllTargetTables(?DataContainer $dc): array
    {
        if (null === ($source = $this->utils->model()->findModelInstanceByPk('tl_entity_import_source', $dc->id))) {
            return [];
        }

        try {
            $options = array_values(Database::getInstance()->listTables(null, true));
        } catch (\Exception $e) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['MSC']['entityImport']['dbConnectionError'], $e->getMessage()));

            return [];
        }

        return $options;
    }

    public function getMetaAccessTokenGenerationUrl(DataContainer $dc, Widget $widget)
    {
        if (null === ($sourceModel = EntityImportSourceModel::findByPk($dc->id)) || !$sourceModel->appId || !$sourceModel->appSecret) {
            return '#';
        }

        $redirectUri = Environment::get('url').$this->router->generate('contao_newsroom_facebook_redirect_callback', [
                'importSource' => $dc->id,
            ]);

        if (str_starts_with($redirectUri, 'http://')) {
            $redirectUri = 'https://'.substr($redirectUri, 7);
        }

        if (!str_starts_with($redirectUri, 'http://') && !str_starts_with($redirectUri, 'https://')) {
            $redirectUri = Environment::get('url').'/'.$redirectUri;
        }

        try {
            $facebook = new Facebook([
                'clientId' => $sourceModel->appId,
                'clientSecret' => $sourceModel->appSecret,
                'graphApiVersion' => 'v2.12',
                'redirectUri' => $redirectUri,
            ]);
        } catch (\Exception $e) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['MSC']['newsroom']['serviceConnectionError'], $e->getMessage()));

            return '#';
        }

        return $facebook->getAuthorizationUrl([
            'scope' => 'page' === $sourceModel->facebookMode ? ['email', 'pages_read_engagement', 'pages_show_list'] : ['email', 'user_posts'],
        ]);
    }

}

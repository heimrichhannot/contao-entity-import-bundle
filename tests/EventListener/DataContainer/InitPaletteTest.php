<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Tests\EventListener\DataContainer;

use Contao\DataContainer;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\LinkedIn\LinkedInLogin;
use HeimrichHannot\EntityImportBundle\Meta\MetaLogin;
use HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel;
use HeimrichHannot\EntityImportBundle\Source\SourceFactory;
use HeimrichHannot\EntityImportBundle\Tests\ContaoMessagesTrait;
use HeimrichHannot\EntityImportBundle\Token\AccessTokenRefresher;
use HeimrichHannot\EntityImportBundle\Util\EntityImportUtil;
use HeimrichHannot\UtilsBundle\Util\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * The onload callback adapts the palettes of the social media source types.
 */
class InitPaletteTest extends ContaoTestCase
{
    use ContaoMessagesTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpBackendRequest();

        $GLOBALS['TL_DCA']['tl_entity_import_source'] = [
            'palettes' => [
                'instagram' => '{config_legend},instagramApi,instagramAccessToken,refreshAccessToken,fieldMapping;',
                'linkedin' => '{config_legend},appId,linkedinAccessToken,refreshAccessToken,fieldMapping;',
                'facebook' => '{config_legend},appId,metaAccessTokenButton,fieldMapping;',
            ],
            'fields' => [
                'fieldMappingPresets' => [],
                'metaAccessTokenButton' => [],
                'linkedinAccessTokenButton' => [],
                'fieldMapping' => ['eval' => ['multiColumnEditor' => ['fields' => ['sourceValue' => ['inputType' => 'text']]]]],
            ],
        ];
        $GLOBALS['TL_LANG']['tl_entity_import_source']['metaAccessTokenButton'] = ['Request', 'Redirect URI: %s'];
        $GLOBALS['TL_LANG']['tl_entity_import_source']['linkedinAccessTokenButton'] = ['Request', 'Redirect URL: %s'];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TL_DCA'], $GLOBALS['TL_LANG']);

        parent::tearDown();
    }

    public function testHidesTheRefreshCheckboxForInstagramWithFacebookLogin(): void
    {
        $this->initPalette(['type' => 'instagram', 'instagramApi' => 'facebook', 'instagramAccessToken' => 'EAA-x'], false);

        $this->assertStringNotContainsString('refreshAccessToken', $GLOBALS['TL_DCA']['tl_entity_import_source']['palettes']['instagram']);
        $this->assertSame('Redirect URI: https://example.test/contao-newsroom/facebook/7/redirect_callback', $GLOBALS['TL_DCA']['tl_entity_import_source']['fields']['metaAccessTokenButton']['label'][1]);
    }

    public function testKeepsTheRefreshCheckboxForInstagramLogin(): void
    {
        $this->initPalette(['type' => 'instagram', 'instagramApi' => 'instagram', 'instagramAccessToken' => 'IGAA-x'], true);

        $this->assertStringContainsString(',refreshAccessToken,', $GLOBALS['TL_DCA']['tl_entity_import_source']['palettes']['instagram']);
    }

    public function testOffersTheLinkedInFieldsAndHidesTheRefreshCheckboxWithoutRefreshToken(): void
    {
        $this->initPalette(['type' => 'linkedin'], false);

        $dca = $GLOBALS['TL_DCA']['tl_entity_import_source'];
        $this->assertStringNotContainsString('refreshAccessToken', $dca['palettes']['linkedin']);
        $this->assertSame('select', $dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['sourceValue']['inputType']);
        $this->assertArrayHasKey('commentary', $dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['sourceValue']['options']);
        $this->assertSame('Redirect URL: https://example.test/contao-newsroom/auth/linkedin', $dca['fields']['linkedinAccessTokenButton']['label'][1]);
    }

    public function testOffersTheFacebookFields(): void
    {
        $this->initPalette(['type' => 'facebook'], false);

        $options = $GLOBALS['TL_DCA']['tl_entity_import_source']['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['sourceValue']['options'];
        $this->assertSame(['id', 'message', 'full_picture', 'permalink_url', 'created_time', 'status_type'], array_keys($options));
    }

    private function initPalette(array $row, bool $refreshable): void
    {
        $model = $this->createClassWithPropertiesStub(EntityImportSourceModel::class, $row + ['id' => 7]);
        $model->method('row')->willReturn($row + ['id' => 7]);

        $modelUtil = $this->createStub(ModelUtil::class);
        $modelUtil->method('findModelInstanceByPk')->willReturn($model);

        $utils = $this->createStub(Utils::class);
        $utils->method('model')->willReturn($modelUtil);

        $refresher = $this->createStub(AccessTokenRefresher::class);
        $refresher->method('supports')->willReturn($refreshable);

        $metaLogin = $this->createStub(MetaLogin::class);
        $metaLogin->method('getRedirectUri')->willReturn('https://example.test/contao-newsroom/facebook/7/redirect_callback');

        $linkedInLogin = $this->createStub(LinkedInLogin::class);
        $linkedInLogin->method('getRedirectUri')->willReturn('https://example.test/contao-newsroom/auth/linkedin');

        $listener = new EntityImportSourceContainer(
            $this->createStub(SourceFactory::class),
            $utils,
            $this->createStub(Connection::class),
            new EntityImportUtil(),
            new EventDispatcher(),
            $this->requestStack,
            $refresher,
            $metaLogin,
            $linkedInLogin,
        );

        $listener->initPalette($this->createClassWithPropertiesStub(DataContainer::class, ['id' => 7, 'table' => 'tl_entity_import_source']));
    }
}

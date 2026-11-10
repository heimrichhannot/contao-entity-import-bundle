<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Tests\Source;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel;
use HeimrichHannot\EntityImportBundle\Source\AbstractSource;
use HeimrichHannot\EntityImportBundle\Tests\ContaoMessagesTrait;
use HeimrichHannot\EntityImportBundle\Tests\HttpClientTrait;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class SourceTestCase extends ContaoTestCase
{
    use ContaoMessagesTrait;
    use HttpClientTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpBackendRequest();
        $GLOBALS['TL_LANG']['MSC']['entityImport']['serviceConnectionError'] = 'No posts: %s';
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TL_LANG']);

        parent::tearDown();
    }

    /**
     * @template T of AbstractSource
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    protected function createSource(string $class, array $model, array $responses): AbstractSource
    {
        $insertTagParser = $this->createStub(InsertTagParser::class);
        $insertTagParser->method('replace')->willReturnArgument(0);

        $source = new $class(
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(Utils::class),
            $this->createStub(ParameterBagInterface::class),
            $insertTagParser,
        );

        $source->setSourceModel($this->createClassWithPropertiesStub(EntityImportSourceModel::class, $model));
        $source->setFilesystemCache(new ArrayAdapter());
        $source->setHttpClient($this->mockHttpClient($responses));

        return $source;
    }

    /**
     * A field mapping that maps each given source field to a target field of the same name.
     */
    protected static function mapping(array $fields, array $static = []): string
    {
        $mapping = array_map(static fn (string $field): array => ['name' => $field, 'valueType' => 'source_value', 'sourceValue' => $field], $fields);

        foreach ($static as $name => $value) {
            $mapping[] = ['name' => $name, 'valueType' => 'static_value', 'staticValue' => $value];
        }

        return serialize($mapping);
    }
}

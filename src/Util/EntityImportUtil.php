<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Util;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\DcaLoader;
use Contao\File;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use GuzzleHttp\Client;
use HeimrichHannot\UtilsBundle\Util\Utils;

class EntityImportUtil
{
    protected ContaoFramework $contaoFramework;
    protected Connection      $connection;
    protected Utils           $utils;

    public function __construct(ContaoFramework $contaoFramework, Connection $connection, Utils $utils)
    {
        $this->contaoFramework = $contaoFramework;
        $this->connection = $connection;
        $this->utils = $utils;
    }

    public function transformFieldMappingSourceValueToSelect($options)
    {
        $dca = &$GLOBALS['TL_DCA']['tl_entity_import_source']['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['sourceValue'];

        $dca['inputType'] = 'select';
        $dca['options'] = $options;
        $dca['eval']['includeBlankOption'] = true;
        $dca['eval']['mandatory'] = true;
        $dca['eval']['chosen'] = true;
    }

    public function getDbalConnectionBySource(array $sourceParams): Connection
    {
        $mapping = [
            'dbDriver' => 'driver',
            'dbHost' => 'host',
            'dbUser' => 'user',
            'dbPass' => 'password',
            'dbDatabase' => 'dbname',
            'dbPconnect' => 'persistent',
            'dbCharset' => 'charset',
            'dbPort' => 'port',
            'dbSocket' => 'unix_socket',
        ];

        $dbalParams = [];

        foreach ($sourceParams as $k => $v) {
            if (isset($mapping[$k])) {
                $dbalParams[$mapping[$k]] = $v;
            }
        }

        $dbalParams['persistent'] = $dbalParams['persistent'] === 'true';

        $connection = DriverManager::getConnection($dbalParams);

        return $connection;
    }

    public function getLocalizedFieldName(string $strField, string $strTable): ?string
    {
        $loader = new DcaLoader($strTable);
        $loader->load();
        System::loadLanguageFile($strTable);

        return $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['label'][0] ?: $strField;
    }

    public function beginTransaction(): void
    {
//        $this->connection->beginTransaction();
    }

    public function commitTransaction(): void
    {
//        $this->connection->commit();
    }

    /**
     * Returns a database result for a given table and id(primary key).
     *
     * @param mixed $pk
     *
     * @return mixed
     */
    public function findResultByPk(string $table, $pk, array $options = [])
    {
        /* @var Database $db */
        if (!($db = $this->contaoFramework->getAdapter(Database::class))) {
            return null;
        }

        $options = array_merge(
            [
                'limit' => 1,
                'column' => 'id',
                'value' => $pk,
            ],
            $options
        );

        $options['table'] = $table;

        if (isset($options['selectFields'])) {
            $query = $this->createQueryWithoutRelations($options['selectFields']);
        } else {
            $query = \Contao\Model\QueryBuilder::find($options);
        }

        $statement = $db->getInstance()->prepare($query);

        // Defaults for limit and offset
        if (!isset($options['limit'])) {
            $options['limit'] = 0;
        }

        if (!isset($options['offset'])) {
            $options['offset'] = 0;
        }

        // Limit
        if ($options['limit'] > 0 || $options['offset'] > 0) {
            $statement->limit($options['limit'], $options['offset']);
        }

        return $statement->execute($options['value']);
    }

    /**
     * Return a single database result by table and search criteria.
     *
     * @return mixed
     */
    public function findOneResultBy(string $table, ?array $columns, ?array $values, array $options = [])
    {
        /* @var Database $db */
        if (!($db = $this->contaoFramework->getAdapter(Database::class))) {
            return null;
        }

        $options = array_merge(
            [
                'limit' => 1,
                'column' => $columns,
                'value' => $values,
            ],
            $options
        );

        $options['table'] = $table;

        if (isset($options['selectFields'])) {
            $query = $this->createQueryWithoutRelations($options['selectFields']);
        } else {
            $query = \Contao\Model\QueryBuilder::find($options);
        }

        $statement = $db->getInstance()->prepare($query);

        if (!isset($options['offset'])) {
            $options['offset'] = 0;
        }

        // Limit
        if ($options['limit'] > 0 || $options['offset'] > 0) {
            $statement->limit($options['limit'], $options['offset']);
        }

        return $statement->execute($options['value']);
    }

    public function findResultsBy(string $table, ?array $columns, ?array $values, array $options = [])
    {
        /* @var Database $db */
        if (!($db = $this->contaoFramework->getAdapter(Database::class))) {
            return null;
        }

        if (null !== $columns) {
            $options = array_merge(
                [
                    'column' => $columns,
                ],
                $options
            );
        }

        if (null !== $values) {
            $options = array_merge(
                [
                    'value' => $values,
                ],
                $options
            );
        }

        $options['table'] = $table;

        if (isset($options['selectFields'])) {
            $query = $this->createQueryWithoutRelations($options);
        } else {
            $query = \Contao\Model\QueryBuilder::find($options);
        }

        $statement = $db->getInstance()->prepare($query);

        // Defaults for limit and offset
        if (!isset($options['limit'])) {
            $options['limit'] = 0;
        }

        if (!isset($options['offset'])) {
            $options['offset'] = 0;
        }

        // Limit
        if ($options['limit'] > 0 || $options['offset'] > 0) {
            $statement->limit($options['limit'], $options['offset']);
        }

        return isset($options['value']) ? $statement->execute($options['value']) : $statement->execute();
    }

    /**
     * Adapted from \Contao\Model\QueryBuilder::find().
     *
     * @return string
     */
    private function createQueryWithoutRelations(array $options)
    {
        $fields = $options['selectFields'] ?? [];

        $query = 'SELECT '.(empty($fields) ? '*' : implode(', ', $fields)).' FROM '.$options['table'];

        // Where condition
        if (isset($options['column'])) {
            $query .= ' WHERE '.(\is_array($options['column']) ? implode(' AND ', $options['column']) : $options['table'].'.'.Database::quoteIdentifier($options['column']).'=?');
        }

        // Group by
        if (isset($options['group'])) {
            @trigger_error('Using the "group" option has been deprecated and will no longer work in Contao 5.0. See https://github.com/contao/contao/issues/1680', \E_USER_DEPRECATED);
            $query .= ' GROUP BY '.$options['group'];
        }

        // Having (see #6446)
        if (isset($options['having'])) {
            $query .= ' HAVING '.$options['having'];
        }

        // Order by
        if (isset($options['order'])) {
            $query .= ' ORDER BY '.$options['order'];
        }

        return $query;
    }

    /**
     * Return if the current alias already exist in table.
     */
    public function aliasExist(string $alias, int $id, string $table, $options = []): bool
    {
        $aliasField = $options['aliasField'] ?? 'alias';

        $stmt = $this->connection->prepare('SELECT id FROM '.$table.' WHERE '.$aliasField.'=? AND id!=?');

        return $stmt->executeQuery([$alias, $id])->rowCount() > 0;
    }

    /**
     * Generate an alias with unique check.
     *
     * @param mixed       $alias       The current alias (if available)
     * @param int         $id          The entity's id
     * @param string|null $table       The entity's table (pass a comma separated list if the validation should be expanded to multiple tables like tl_news AND tl_member. ATTENTION: the first table needs to be the one we're currently in). Pass null to skip unqiue check.
     * @param string      $title       The value to use as a base for the alias
     * @param bool        $keepUmlauts Set to true if German umlauts should be kept
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generateAlias(?string $alias, int $id, ?string $table, string $title, bool $keepUmlauts = true, $options = []): string
    {
        $autoAlias = false;
        $aliasField = $options['aliasField'] ?? 'alias';

        // Generate alias if there is none
        if (empty($alias)) {
            $autoAlias = true;
            $alias = StringUtil::generateAlias($title);
        }

        if (!$keepUmlauts) {
            $alias = preg_replace(['/ä/i', '/ö/i', '/ü/i', '/ß/i'], ['ae', 'oe', 'ue', 'ss'], $alias);
        }

        if (null === $table) {
            return $alias;
        }

        $originalAlias = $alias;

        // multiple tables?
        if (false !== strpos($table, ',')) {
            $tables = explode(',', $table);

            foreach ($tables as $i => $partTable) {
                // the table in which the entity is
                if (0 === $i) {
                    if ($this->aliasExist($alias, $id, $table, $options)) {
                        if (!$autoAlias) {
                            throw new \InvalidArgumentException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $alias));
                        }

                        $alias = $originalAlias.'-'.$id;
                    }
                } else {
                    // another table
                    $stmt = $this->connection->prepare("SELECT id FROM {$partTable} WHERE ' . $aliasField . '=?");

                    // Check whether the alias exists
                    if ($stmt->executeQuery([$alias])->rowCount() > 0) {
                        throw new \InvalidArgumentException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $alias));
                    }
                }
            }
        } else {
            if (!$this->aliasExist($alias, $id, $table, $options)) {
                return $alias;
            }

            // Check whether the alias exists
            if (!$autoAlias) {
                throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $alias));
            }

            // Add ID to alias
            $alias .= '-'.$id;
        }

        return $alias;
    }

    public function getExtensionFromFileContent($content): string|false
    {
        if (!class_exists('\finfo')) {
            return false;
        }

        $finfo = new \finfo(\FILEINFO_MIME_TYPE);

        return $this->getExtensionByMimeType($finfo->buffer($content));
    }

    public function getExtensionByMimeType($mimeType): string|false
    {
        foreach ($GLOBALS['TL_MIME'] as $extension => $data) {
            if ($data[0] === $mimeType) {
                if ('jpeg' === $extension) {
                    $extension = 'jpg';
                }

                return $extension;
            }
        }

        return false;
    }

    /**
     * Tries to get the binary content from a file in various sources and returns it if possible.
     *
     * Possible sources:
     *   - url
     *   - contao uuid
     *   - string is already a binary file content
     *
     * @param $source
     *
     * @return bool|mixed Returns false if the file content could not be retrieved
     */
    public function retrieveFileContent($source, $silent = true): mixed
    {
        // url
        if (Validator::isUrl($source)) {
            $client = new Client();
            $request = $client->request('GET', $source);

            if (200 === $request->getStatusCode()) {
                $content = $request->getBody()->__toString();

                if ($content) {
                    return $content;
                }
            } else {
                if (!$silent) {
                    $body = $request->getBody()->__toString();

                    Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['httpRequestError'], $source, 'Code '.$request->getStatusCode().': '.$body));
                }
            }
        }

        // contao uuid
        if (Validator::isUuid($source)) {
            $content = $this->getFileContentFromUuid($source);

            if (false !== $content) {
                return $content;
            }
        }

        // already binary -> ctype_print() checks if non-printable characters are in the string -> if so, it's most likely a file
        if (!ctype_print($source)) {
            return $source;
        }

        return false;
    }

    public function getFileContentFromUuid($uuid): false|string
    {
        $file = new File($this->utils->file()->getPathFromUuid($uuid));

        if (!$file || !$file->exists()) {
            return false;
        }

        return file_get_contents(System::getContainer()->getParameter('kernel.project_dir').'/'.$file->path);
    }
}

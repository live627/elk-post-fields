<?php

/**
 * @package   PostFields
 * @version   2.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2011-2016, John Rayes
 * @license   proprietary
 */

namespace ElkArte\addons\PostFields;

class Language
{
    private $parser;
    private $parsed;
    private $data;


    public function __construct()
    {
        $this->parser = new YamlParser();

        try {
            $parsed = $this->parser(__DIR__.'/lang.yml');
        } catch (YamlParserException $e) {
            throw new YamlParserException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function __invoke($filePath)
    {
        try {
            $this->data = $yaml->parse(file_get_contents('/path/to/file.yml'));
        } catch (ParseException $e) {
            throw new YamlParserException($e->getMessage(), $e->getCode(), $e);
        }
    }

    function __toString()
    {
        return (string) $this->data;
    }

    public function dump($array)
    {
        try {
            return sfYaml::dump($array);
        } catch (Exception $e) {
            throw new YamlParserException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

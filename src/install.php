<?php

/**
 * @package   PostFields
 * @version   2.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2011-2016, John Rayes
 * @license   http://opensource.org/licenses/ISC ISC
 */

// If we have found SSI.php and we are outside of ElkArte, then we are running standalone.
if (file_exists(dirname(__FILE__).'/SSI.php') && !defined('ELK')) {
    require_once(dirname(__FILE__).'/SSI.php');
} elseif (!defined('ELK')) // If we are outside ElkArte and can't find SSI.php, then throw an error
{
    die('<b>Error:</b> Cannot install - please verify you put this file in the same place as ElkArte\'s SSI.php.');
}

$db = database();
$dbtbl = db_table();

$columns = [
    [
        'name' => 'id_field',
        'type' => 'mediumint',
        'size' => 8,
        'unsigned' => true,
        'auto' => true,
    ],
    [
        'name' => 'name',
        'type' => 'varchar',
        'size' => 80,
    ],
    [
        'name' => 'type',
        'type' => 'varchar',
        'size' => 20,
    ],
    [
        'name' => 'description',
        'type' => 'varchar',
        'size' => 4096,
    ],
    [
        'name' => 'enclose',
        'type' => 'varchar',
        'size' => 4096,
    ],
    [
        'name' => 'options',
        'type' => 'varchar',
        'size' => 4096,
    ],
    [
        'name' => 'size',
        'type' => 'smallint',
        'size' => 5,
        'unsigned' => true,
    ],
    [
        'name' => 'default_value',
        'type' => 'varchar',
        'size' => 80,
    ],
    [
        'name' => 'mask',
        'type' => 'varchar',
        'size' => 20,
    ],
    [
        'name' => 'regex',
        'type' => 'varchar',
        'size' => 80,
    ],
    [
        'name' => 'boards',
        'type' => 'varchar',
        'size' => 80,
    ],
    [
        'name' => 'groups',
        'type' => 'varchar',
        'size' => 80,
    ],
    [
        'name' => 'bbc',
        'type' => 'enum(\'no\',\'yes\')',
    ],
    [
        'name' => 'can_search',
        'type' => 'enum(\'no\',\'yes\')',
    ],
    [
        'name' => 'active',
        'type' => 'enum(\'yes\',\'no\')',
    ],
    [
        'name' => 'required',
        'type' => 'enum(\'yes\',\'no\')',
    ],
    [
        'name' => 'eval',
        'type' => 'enum(\'no\',\'yes\')',
    ],
    [
        'name' => 'topic_only',
        'type' => 'enum(\'no\',\'yes\')',
    ],
    [
        'name' => 'mi',
        'type' => 'enum(\'no\',\'yes\')',
    ],
];

$indexes = [
    [
        'type' => 'primary',
        'columns' => ['id_field'],
    ],
];

$dbtbl->db_create_table('{db_prefix}message_fields', $columns, $indexes, [], 'update');

$columns = [
    [
        'name' => 'id_field',
        'type' => 'mediumint',
        'size' => 8,
        'unsigned' => true,
    ],
    [
        'name' => 'id_msg',
        'type' => 'int',
        'size' => 10,
        'unsigned' => true,
    ],
    [
        'name' => 'value',
        'type' => 'varchar',
        'size' => 4096,
    ],
];

$indexes = [
    [
        'type' => 'primary',
        'columns' => ['id_field', 'id_msg'],
    ],
];

$dbtbl->db_create_table('{db_prefix}message_field_data', $columns, $indexes, [], 'update');

if (!empty($ssi)) {
    echo 'Database installation complete!';
}

?>
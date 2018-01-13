<?php

/**
 * @package   PostFields
 * @version   2.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2011-2016, John Rayes
 * @license   proprietary
 */

namespace ElkArte\addons\PostFields;

class Integration
{
    public static function admin_areas(&$admin_areas)
    {
        $ohara = new Ohara();
        $ohara->name = 'PostFields';
        $admin_areas['layout']['areas']['postfields'] = [
            'label' => $ohara->text('title'),
            //~ 'icon' => 'transparent.png',
            'class' => 'admin_img_postfields',
            'permission' => ['admin_forum'],
            'function' => function () {
                Dispatcher::getInstance();
            },
            'subsections' => [
                'index' => [$ohara->text('menu_index'), 'admin_forum'],
                'edit' => [$ohara->text('menu_edit'), 'admin_forum'],
            ],
        ];
    }

    public static function remove_message($message, $decreasePostCount)
    {
        self::remove_messages((array) $message, $decreasePostCount);
    }

    public static function remove_messages($messages, $decreasePostCount)
    {
        if (!empty($messages)) {
            Database::query(
                '',
                '
                DELETE FROM {db_prefix}message_field_data
                WHERE id_msg IN ({array_int:message_list})',
                [
                    'message_list' => $messages,
                ]
            );
        }
    }

    public static function remove_topics($topics, $decreasePostCount, $ignoreRecycling)
    {
        $messages = [];
        $request = Database::query(
            '',
            '
            SELECT id_msg
            FROM {db_prefix}messages
            WHERE id_topic IN ({array_int:topics})',
            [
                'topics' => $topics,
            ]
        );
        while ($row = Database::fetch_assoc($request)) {
            $messages[] = $row['id_msg'];
        }
        Database::free_result($request);

        if (!empty($messages)) {
            self::remove_messages($messages, $decreasePostCount);
        }
    }

    public static function display_topics($topic_ids)
    {
        if (empty($topic_ids)) {
            return;
        }
        $messages = [];
        $request = Database::query(
            '',
            '
            SELECT id_first_msg
            FROM {db_prefix}topics
            WHERE id_topic IN ({array_int:topics})',
            [
                'topics' => $topic_ids,
            ]
        );
        while ($row = Database::fetch_row($request)) {
            $messages[] = $row[0];
        }
        Database::free_result($request);

        if (!empty($messages)) {
            self::display_message_list($messages, true);
        }
    }

    public static function fetchFields($messages, $is_message_index = false)
    {
        global $board, $context;

        $context['fields'] = [];
        $util = new Util;
        $field_list = iterator_to_array($util->filterFields($board));
        if (empty($field_list)) {
            return;
        }
        $values = $util->getFieldsValues($messages, array_keys($field_list));
        foreach ($values as $id_msg => list ($id_field, $value)) {
            $exists = !empty($value);
            $context['fields'][$id_msg][$id_field] = $util->renderField($field_list[$id_field], $value, $exists);
        }

        if (!empty($context['fields'])) {
            loadLanguage('PostFields');
            loadTemplate('PostFields');
        }
    }

    public static function displayFields(&$output, &$message)
    {
        global $context;

        if (!empty($context['fields'][$output['id']])) {
            $body = '
                            <br />
                            <dl class="settings">';

            foreach ($context['fields'][$output['id']] as $field) {
                $body .= '
                                <dt>
                                    <strong>'.$field['name'].': </strong><br />
                                </dt>
                                <dd>
                                    '.$field['output_html'].'
                                </dd>';
            }

            $output['body'] = $body.'
                            </dl>
                            <hr />
                            <br />'.$output['body'];
        }
    }
}

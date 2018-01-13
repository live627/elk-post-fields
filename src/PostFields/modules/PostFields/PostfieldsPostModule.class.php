<?php

/**
 * @package   PostFields
 * @version   2.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2011-2016, John Rayes
 * @license   proprietary
 */

use ElkArte\addons\PostFields\Database;
use ElkArte\addons\PostFields\Util;

class Postfields_Post_Module implements ElkArte\sources\modules\Module_Interface
{
    /**
     * {@inheritdoc}
     */
    public static function hooks(\Event_Manager $eventsManager)
    {
        return [
            ['prepare_post', [self::class, 'prepare_post'], []],
            ['prepare_context', [self::class, 'prepare_context'], []],
            ['before_save_post', [self::class, 'before_save_post'], []],
            ['after_save_post', [self::class, 'after_save_post'], []],
        ];
    }

    public function prepare_post()
    {
        Template_Layers::getInstance()->addAfter('input_post_fields', 'postarea');
    }

    public function before_save_post($post_errors)
    {
        global $board, $topic, $txt;

        $util = new Util();
        $field_list = $util->filterFields($board);
        loadLanguage('PostFields');

        if (!empty($topic) && isset($_REQUEST['msg'])) {
            $request = Database::query(
                '',
                '
                SELECT id_first_msg
                FROM {db_prefix}topics
                WHERE id_topic = {int:current_topic}',
                [
                    'current_topic' => $topic,
                ]
            );
            $topic_value = Database::fetch_row($request)[0] == $_REQUEST['msg'];
            Database::free_result($request);
        }
        $postfield = filter_input(INPUT_POST, 'postfield', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        foreach ($field_list as $field) {
            if (!empty($topic_value) && $field['topic_only'] != 'yes') {
                continue;
            }
            $value = !isset($postfield[$field['id_field']]) ?: $postfield[$field['id_field']];
            $type = $util->getFieldType($field, $value, !empty($value));
            $type->validate();
            if (false !== ($err = $type->getError())) {
                $post_errors->addError($err);
            }
        }
    }

    public function after_save_post($board, $topic, $msgOptions, $topicOptions)
    {
        global $modSettings, $user_info;

        $util = new Util();
        $field_list = iterator_to_array($util->filterFields($board));
        $changes = $log_changes = $values = [];
        $value = '';

        if (isset($_REQUEST['msg'])) {
            $values = $util->getFieldValues([$_REQUEST['msg']], array_keys($field_list));
        }
        if (!empty($topicOptions['id'])) {
            $request = Database::query(
                '',
                '
                SELECT id_first_msg
                FROM {db_prefix}topics
                WHERE id_topic = {int:current_topic}',
                [
                    'current_topic' => $topicOptions['id'],
                ]
            );
            $topic_value = Database::fetch_row($request)[0] == $msgOptions['id'];
            Database::free_result($request);
        }
        $postfield = filter_input(INPUT_POST, 'postfield', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        foreach ($field_list as $field) {
            if (!empty($topic_value) && $field['topic_only'] != 'yes') {
                continue;
            }
            $value = !isset($postfield[$field['id_field']]) ?: trim($postfield[$field['id_field']]);
            $class_name = 'ElkArte\\addons\\PostFields\\PostFields_'.$field['type'];

            if (isset($values[$field['id_field']]) && $values[$field['id_field']] == $value) {
                continue;
            }
            $type = $util->getFieldType($field, $value, !empty($value));
            $changes[] = [$field['id_field'], $type->getValue(), $msgOptions['id']];

            $log_changes[] = [
                'action' => 'message_field_'.$field['id_field'],
                'log_type' => 'user',
                'extra' => [
                    'old' => isset($values[$field['id_field']]) ? $values[$field['id_field']] : '',
                    'new' => $value,
                    'name' => $field['name'],
                    'message' => $msgOptions['id'],
                    'topic' => $topicOptions['id'],
                    'board' => $topicOptions['board'],
                ],
            ];
        }

        if (!empty($changes)) {
            Database::insert(
                'replace',
                '{db_prefix}message_field_data',
                ['id_field' => 'int', 'value' => 'string', 'id_msg' => 'int'],
                $changes,
                ['id_field', 'id_msg']
            );

            if (!empty($log_changes) && !empty($modSettings['modlog_enabled'])) {
                logActions($log_changes);
            }
        }
    }

    public function prepare_context($id_member_poster)
    {
        global $board, $boardurl, $context, $options, $user_info;

        $util = new Util();
        $context['fields'] = $util->load_fields($util->filterFields($board));
        loadLanguage('PostFields');
        loadTemplate('PostFields');
        $context['is_post_fields_collapsed'] =
            $user_info['is_guest'] ? !empty($_COOKIE['PostFields']) : !empty($options['PostFields']);
        $context['html_headers'] .= '
    <link rel="stylesheet" type="text/css" href="'.$boardurl.'/addons/PostFields/assets/postfieldsadmin.css" />';
    }
}

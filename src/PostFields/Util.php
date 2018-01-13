<?php

/**
 * @package   PostFields
 * @version   2.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2011-2016, John Rayes
 * @license   proprietary
 */

namespace ElkArte\addons\PostFields;

use Traversable;

class Util extends Ohara
{
    public $name = __CLASS__;
    protected $fields = [];

    public function __construct()
    {
        Database::getInstance();
        $this->fetchFields();
        parent::__construct();
    }

    public function fetchFields()
    {
        if (empty($this->fields)) {
            $request = Database::query(
                '',
                '
                SELECT *
                FROM {db_prefix}message_fields'
            );
            while ($row = Database::fetch_assoc($request)) {
                if (!in_array(false, call_integration_hook('integrate_fetch_post_fields', [&$row]), true)) {
                    $this->fields[$row['id_field']] = $row;
                }
            }
            Database::free_result($request);
        }

        return $this->fields;
    }

    public function getFieldsSearchable()
    {
        foreach ($this->fields as $id_field => $field) {
            if ($field['can_search'] == 'yes') {
                yield $id_field => $field;
            }
        }
    }

    public function getFieldValues($id_msg, array $field_list)
    {
        $request = Database::query(
            '',
            '
            SELECT id_field, value
            FROM {db_prefix}message_field_data
            WHERE id_msg = {int:msg}
                AND id_field IN ({array_int:field_list})',
            [
                'msg' => (int) $id_msg,
                'field_list' => $field_list,
            ]
        );
        $values = [];
        while (list ($id_field, $value) = Database::fetch_row($request)) {
            $values[$id_field] = isset($value) ? $value : '';
        }
        Database::free_result($request);

        return $values;
    }

    public function load_fields(Traversable $fields)
    {
        global $board, $context;

        if (empty($fields)) {
            return;
        }
        if (isset($_REQUEST['msg'])) {
            $fields = iterator_to_array($fields);
            $values = $this->getFieldValues([$_REQUEST['msg']], array_keys($fields));
        }
        $value = '';
        $exists = false;
        foreach ($fields as $id_field => $field) {
            // If this was submitted already then make the value the posted version.
            if (isset($_POST['postfield'], $_POST['postfield'][$field['id_field']])) {
                $value = $_POST['postfield'][$field['id_field']];
                if (in_array($field['type'], ['select', 'radio'])) {
                    $value =
                        ($options = json_decode($field['options'])) && isset($options[$value]) ? $options[$value] : '';
                }
            }
            if (isset($values[$id_field])) {
                $value = $values[$id_field];
            }
            $exists = !empty($value);
            yield $this->renderField($field, $value, $exists);
        }
    }

    public function getFieldsValues(array $messages, array $field_list)
    {
        $request = Database::query(
            '',
            '
            SELECT id_msg, id_field, value
            FROM {db_prefix}message_field_data
            WHERE id_msg IN ({array_int:message_list})
                AND id_field IN ({array_int:field_list})',
            [
                'message_list' => $messages,
                'field_list' => $field_list,
            ]
        );
        while (list ($id_msg, $id_field, $value) = Database::fetch_row($request)) {
            yield $id_msg => [$id_field, $value];
        }
        Database::free_result($request);
    }

    public function loadFields($messages, Traversable $fields)
    {
        global $board, $context;

        $fields = iterator_to_array($fields);
        if (empty($fields)) {
            return;
        }
        $values = $this->getFieldsValues($messages, array_keys($fields));
        foreach ($values as $id_msg => list ($id_field, $value)) {
            $exists = !empty($value);
            yield $id_field => $this->renderField($fields[$id_field], $value, $exists);
        }
    }

    public function filterFields($board = null)
    {
        global $user_info;

        foreach ($this->fields as $id_field => $field) {
            if ($board !== null) {
                $board_list = array_flip(json_decode($field['boards']));
                if (!isset($board_list[$board])) {
                    continue;
                }
            }

            $group_list = json_decode($field['groups']);
            $is_allowed = array_intersect($user_info['groups'], $group_list);
            if (empty($is_allowed)) {
                continue;
            }

            yield $id_field => $field;
        }
    }

    public function keys(Traversable $fields)
    {
        $retVal = [];
        foreach ($fields as $id_field => $field) {
            $retVal[] = $id_field;
        }

        return $retVal;
    }

    /**
     * @param boolean $exists
     */
    public function renderField($field, $value, $exists)
    {
        require_once(__DIR__.'/Class-PostFields.php');
        $class_name = __NAMESPACE__.'\\PostFields_'.$field['type'];
        if (!class_exists($class_name)) {
            throw new \Exception(
                sprintf(
                    'Param "%s" not found for field "%s" at ID #%d.',
                    $field['type'],
                    $field['name'],
                    $field['id_field']
                )
            );
        }

        $param = new $class_name($field, $value, $exists);
        $param->setHtml();
        // Parse BBCode
        if ($field['bbc'] == 'yes') {
            $param->output_html = \BBC\ParserWrapper::getInstance()->parseMessage($param->output_html, false);
        } // Allow for newlines at least
        elseif ($field['type'] == 'textarea') {
            $param->output_html = strtr($param->output_html, ["\n" => '<br>']);
        }

        // Enclosing the user input within some other text?
        if (!empty($field['enclose']) && !empty($param->output_html)) {
            $replacements = [
                '{SCRIPTURL}' => $this->scriptUrl,
                '{IMAGES_URL}' => $this->settings['images_url'],
                '{DEFAULT_IMAGES_URL}' => $this->settings['default_images_url'],
                '{INPUT}' => $param->output_html,
            ];
            call_integration_hook(
                'integrate_enclose_post_field',
                [$field['id_field'], &$field['enclose'], &$replacements]
            );
            $param->output_html = strtr($field['enclose'], $replacements);
        }

        return [
            'name' => $field['name'],
            'description' => $field['description'],
            'type' => $field['type'],
            'input_html' => $param->input_html,
            'output_html' => $param->getOutputHtml(),
            'id_field' => $field['id_field'],
            'value' => $value,
        ];
    }

    /**
     * Gets all membergroups and filters them according to the parameters.
     *
     * @param array $checked    list of all id_groups to be checked (have a mark in the checkbox).
     * @param array $disallowed list of all id_groups that are skipped. Default is an empty array.
     * @param bool  $inherited  whether or not to filter out the inherited groups. Default is false.
     *
     * @return array all the membergroups filtered according to the parameters; empty array if something went wrong.
     * @since 1.0
     */
    public function list_groups(
        array $checked,
        array $disallowed = [],
        $inherited = false,
        $permission = null,
        $board_id = null
    ) {
        global $context, $modSettings, $smcFunc, $sourcedir, $txt;

        // We'll need this for loading up the names of each group.
        if (!loadLanguage('ManageBoards')) {
            loadLanguage('ManageBoards');
        }

        // Are we also looking up permissions?
        if ($permission !== null) {
            require_once($sourcedir.'/Subs-Members.php');
            $member_groups = groupsAllowedTo($permission, $board_id);
            $disallowed = array_diff(array_keys(list_groups(-3)), $member_groups['allowed']);
        }

        $groups = [];
        if (!in_array(-1, $disallowed)) {
            // Guests
            $groups[-1] = [
                'id' => -1,
                'name' => $txt['parent_guests_only'],
                'checked' => in_array(-1, $checked) || in_array(-3, $checked),
                'is_post_group' => false,
                'color' => '',
            ];
        }

        if (!in_array(0, $disallowed)) {
            // Regular Members
            $groups[0] = [
                'id' => 0,
                'name' => $txt['parent_members_only'],
                'checked' => in_array(0, $checked) || in_array(-3, $checked),
                'is_post_group' => false,
                'color' => '',
            ];
        }

        // Load membergroups.
        $request = Database::query(
            '',
            '
            SELECT group_name, id_group, min_posts, online_color
            FROM {db_prefix}membergroups
            WHERE id_group > {int:is_zero}'.(!$inherited ? '
                AND id_parent = {int:not_inherited}' : '').(!$inherited && empty($modSettings['permission_enable_postgroups']) ? '
                AND min_posts = {int:min_posts}' : ''),
            [
                'is_zero' => 0,
                'not_inherited' => -2,
                'min_posts' => -1,
            ]
        );
        while ($row = Database::fetch_assoc($request)) {
            if (!in_array($row['id_group'], $disallowed)) {
                $groups[(int) $row['id_group']] = [
                    'id' => $row['id_group'],
                    'name' => trim($row['group_name']),
                    'checked' => in_array($row['id_group'], $checked) || in_array(-3, $checked),
                    'is_post_group' => $row['min_posts'] != -1,
                    'color' => $row['online_color'],
                ];
            }
        }
        Database::free_result($request);

        asort($groups);

        return $groups;
    }
}

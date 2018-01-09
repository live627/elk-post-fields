<?php

/**
 * @package   PostFields
 * @version   2.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2011-2016, John Rayes
 * @license   proprietary
 */

namespace ElkArte\addons\PostFields;

class Admin extends Ohara
{
    public $name = 'PostFields';
    private $util;

    public function __construct()
    {
        $this->util = new Util();
        parent::__construct();
    }

    public function Index()
    {
        global $context;

        // Deleting?
        if (isset($_POST['delete'], $_POST['remove'])) {
            checkSession();
            $this->deleteFields($_POST['remove']);
            redirectexit('action=admin;area=postfields');
        }

        // Changing the status?
        if (isset($_POST['save'])) {
            checkSession();
            foreach ($this->util->fetchFields() as $field) {
                $bbc = !empty($_POST['bbc'][$field['id_field']]) ? 'yes' : 'no';
                if ($bbc != $field['bbc']) {
                    Database::query(
                        '',
                        '
                        UPDATE {db_prefix}message_fields
                        SET bbc = {string:bbc}
                        WHERE id_field = {int:field}',
                        [
                            'bbc' => $bbc,
                            'field' => $field['id_field'],
                        ]
                    );
                }

                $active = !empty($_POST['active'][$field['id_field']]) ? 'yes' : 'no';
                if ($active != $field['active']) {
                    Database::query(
                        '',
                        '
                        UPDATE {db_prefix}message_fields
                        SET active = {string:active}
                        WHERE id_field = {int:field}',
                        [
                            'active' => $active,
                            'field' => $field['id_field'],
                        ]
                    );
                }

                $can_search = !empty($_POST['can_search'][$field['id_field']]) ? 'yes' : 'no';
                if ($can_search != $field['can_search']) {
                    Database::query(
                        '',
                        '
                        UPDATE {db_prefix}message_fields
                        SET can_search = {string:can_search}
                        WHERE id_field = {int:field}',
                        [
                            'can_search' => $can_search,
                            'field' => $field['id_field'],
                        ]
                    );
                }
                call_integration_hook('integrate_update_post_field', [$field]);
            }
            redirectexit('action=admin;area=postfields');
        }

        // New field?
        if (isset($_POST['new'])) {
            redirectexit('action=admin;area=postfields;sa=edit');
        }

        $listOptions = [
            'id' => 'pf_fields',
            'base_href' => $this->scriptUrl.'?action=admin;area=postfields',
            'default_sort_col' => 'name',
            'no_items_label' => $this->text('none'),
            'items_per_page' => 25,
            'get_items' => [
                'function' => [$this, 'process'],
                'params' => [
                    $this->util->fetchFields(),
                    'pf_fields',
                ],
            ],
            'get_count' => [
                'function' => function () {
                    return count($this->util->fetchFields());
                },
            ],
            'columns' => [
                'name' => [
                    'header' => [
                        'value' => $this->text('fieldname'),
                        'style' => 'text-align: left;',
                    ],
                    'data' => [
                        'function' => function ($rowData) {
                            return sprintf(
                                '<a href="%1$s?action=admin;area=postfields;sa=edit;fid=%2$d">%3$s</a><div class="smalltext">%4$s</div>',
                                $this->scriptUrl,
                                $rowData['id_field'],
                                $rowData['name'],
                                $rowData['description']
                            );
                        },
                        'style' => 'width: 40%;',
                    ],
                    'sort' => [
                        'default' => 'name',
                        'reverse' => 'name DESC',
                    ],
                ],
                'type' => [
                    'header' => [
                        'value' => $this->text('fieldtype'),
                    ],
                    'data' => [
                        'function' => function ($rowData) {
                            $textKey = sprintf('type_%1$s', $rowData['type']);

                            return $this->text($textKey);
                        },
                        'style' => 'width: 10%; text-align: center;',
                    ],
                    'sort' => [
                        'default' => 'type',
                        'reverse' => 'type DESC',
                    ],
                ],
                'bbc' => [
                    'header' => [
                        'value' => $this->text('bbc'),
                    ],
                    'data' => [
                        'function' => function ($rowData) {
                            $isChecked = $rowData['bbc'] == 'no' ? '' : ' checked';

                            return sprintf(
                                '<span id="bbc_%1$s" class="color_%4$s">%3$s</span>&nbsp;<input type="checkbox" name="bbc[%1$s]" id="bbc_%1$s" value="%1$s"%2$s>',
                                $rowData['id_field'],
                                $isChecked,
                                $this->text($rowData['bbc']),
                                $rowData['bbc']
                            );
                        },
                        'style' => 'width: 10%; text-align: center;',
                    ],
                    'sort' => [
                        'default' => 'bbc DESC',
                        'reverse' => 'bbc',
                    ],
                ],
                'active' => [
                    'header' => [
                        'value' => $this->text('active'),
                    ],
                    'data' => [
                        'function' => function ($rowData) {
                            $isChecked = $rowData['active'] == 'no' ? '' : ' checked';

                            return sprintf(
                                '<span id="active_%1$s" class="color_%4$s">%3$s</span>&nbsp;<input type="checkbox" name="active[%1$s]" id="active_%1$s" value="%1$s"%2$s>',
                                $rowData['id_field'],
                                $isChecked,
                                $this->text($rowData['active']),
                                $rowData['active']
                            );
                        },
                        'style' => 'width: 10%; text-align: center;',
                    ],
                    'sort' => [
                        'default' => 'active DESC',
                        'reverse' => 'active',
                    ],
                ],
                'can_search' => [
                    'header' => [
                        'value' => $this->text('can_search'),
                    ],
                    'data' => [
                        'function' => function ($rowData) {
                            $isChecked = $rowData['can_search'] == 'no' ? '' : ' checked';

                            return sprintf(
                                '<span id="can_search_%1$s" class="color_%4$s">%3$s</span>&nbsp;<input type="checkbox" name="can_search[%1$s]" id="can_search_%1$s" value="%1$s"%2$s>',
                                $rowData['id_field'],
                                $isChecked,
                                $this->text($rowData['can_search']),
                                $rowData['can_search']
                            );
                        },
                        'style' => 'width: 10%; text-align: center;',
                    ],
                    'sort' => [
                        'default' => 'can_search DESC',
                        'reverse' => 'can_search',
                    ],
                ],
                'modify' => [
                    'header' => [
                        'value' => $this->text('modify'),
                    ],
                    'data' => [
                        'sprintf' => [
                            'format' => '<a href="'.$this->scriptUrl.'?action=admin;area=postfields;sa=edit;fid=%1$s">'.$this->text(
                                    'modify'
                                ).'</a>',
                            'params' => [
                                'id_field' => false,
                            ],
                        ],
                        'style' => 'width: 10%; text-align: center;',
                    ],
                ],
                'remove' => [
                    'header' => [
                        'value' => $this->text('remove'),
                    ],
                    'data' => [
                        'function' => function ($rowData) {
                            return sprintf(
                                '<span id="remove_%1$s" class="color_no">%2$s</span>&nbsp;<input type="checkbox" name="remove[%1$s]" id="remove_%1$s" value="%1$s">',
                                $rowData['id_field'],
                                $this->text('no')
                            );
                        },
                        'style' => 'width: 10%; text-align: center;',
                    ],
                    'sort' => [
                        'default' => 'remove DESC',
                        'reverse' => 'remove',
                    ],
                ],
            ],
            'form' => [
                'href' => $this->scriptUrl.'?action=admin;area=postfields',
                'name' => 'postProfileFields',
            ],
            'additional_rows' => [
                [
                    'position' => 'below_table_data',
                    'value' => '<input type="submit" name="save" value="'.$this->text(
                            'save'
                        ).'" class="submit">&nbsp;&nbsp;<input type="submit" name="delete" value="'.$this->text(
                            'delete'
                        ).'" onclick="return confirm('.JavaScriptEscape(
                            $this->text('delete_sure')
                        ).');" class="delete">&nbsp;&nbsp;<input type="submit" name="new" value="'.$this->text(
                            'make_new'
                        ).'" class="new">',
                    'style' => 'text-align: right;',
                ],
            ],
        ];
        require_once(SUBSDIR.'/GenericList.class.php');
        call_integration_hook('integrate_list_post_fields', [&$listOptions]);
        createList($listOptions);
        $context['sub_template'] = 'show_list';
        $context['default_list'] = 'pf_fields';
    }

    public function Edit()
    {
        global $context;

        $context['fid'] = $this->_req->get('fid', 'intval', 0);
        $context['page_title'] =
            $this->text('title').' - '.($context['fid'] ? $this->text('edit') : $this->text('add'));
        $context['html_headers'] .= '
	<link rel="stylesheet" type="text/css" href="'.$this->boardUrl.'/addons/PostFields/assets/postfieldsadmin.css" />
	<script type="text/javascript" src="'.$this->boardUrl.'/addons/PostFields/assets/postfieldsadmin.js"></script>';
        loadTemplate('PostFields');
        require_once(__DIR__.'/Class-PostFields.php');
        require_once(SUBSDIR.'/Boards.subs.php');
        $context += getBoardList(['not_redirection' => true]);
        loadLanguage('Profile');

        if ($context['fid']) {
            $request = Database::query(
                '',
                '
                SELECT *
                FROM {db_prefix}message_fields
                WHERE id_field = {int:current_field}',
                [
                    'current_field' => $context['fid'],
                ]
            );
            $context['field'] = [];
            while ($row = Database::fetch_assoc($request)) {
                if ($row['type'] == 'textarea') {
                    list ($rows, $cols) = json_decode($row['default_value']);
                } else {
                    $rows = 3;
                    $cols = 30;
                }

                $context['field'] = [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'enclose' => $row['enclose'],
                    'type' => $row['type'],
                    'length' => $row['size'],
                    'rows' => $rows,
                    'cols' => $cols,
                    'bbc' => $row['bbc'] == 'yes',
                    'default_check' => $row['type'] == 'check' && $row['default_value'],
                    'default_select' => $row['type'] == 'select' || $row['type'] == 'radio' ? $row['default_value'] : '',
                    'options' => strlen($row['options']) > 1 ? json_decode($row['options']) : ['', '', ''],
                    'active' => $row['active'] == 'yes',
                    'can_search' => $row['can_search'] == 'yes',
                    'mask' => $row['mask'],
                    'regex' => $row['regex'],
                    'boards' => !empty($row['boards']) ? json_decode($row['boards']) : [],
                    'groups' => !empty($row['groups']) ? json_decode($row['groups']) : [],
                ];
            }
            Database::free_result($request);
        }

        // Are we saving?
        if (isset($_POST['save'])) {
            if (false !== ($retVal = $this->validateField($context['fid']))) {
                checkSession();
                $this->insertField($context['fid']);
                redirectexit('action=admin;area=postfields');
            }
        } elseif (isset($_POST['delete']) && $context['field']['name']) {
            checkSession();
            $this->deleteFields($context['fid']);
            redirectexit('action=admin;area=postfields');
        }

        // Setup the default values as needed.
        if (empty($context['field'])) {
            $context['field'] = [
                'name' => '',
                'description' => '',
                'enclose' => '',
                'type' => 'text',
                'length' => 255,
                'rows' => 4,
                'cols' => 30,
                'bbc' => false,
                'default_check' => false,
                'default_select' => '',
                'options' => ['', '', ''],
                'active' => true,
                'can_search' => false,
                'mask' => '',
                'regex' => '',
                'boards' => [],
                'groups' => [-3],
            ];
        }
        $context['field']['types'] = $this->get_extends_number('ElkArte\\addons\\PostFields\\PostFieldsBase');
        $context['field']['masks'] = $this->get_extends_number('ElkArte\\addons\\PostFields\\postFieldMaskBase');
        $context['groups'] = $this->util->list_groups($context['field']['groups']);
        $context['all_groups_checked'] = empty(array_diff_key(
            $context['groups'],
            array_filter(
                $context['groups'],
                function ($group) {
                    return $group['checked'];
                }
            )
        ));
        $context['all_boards_checked'] = true;
        foreach ($context['categories'] as $category) {
            foreach ($category['boards'] as $board) {
                $context['all_boards_checked'] &= in_array($board['id'], $context['field']['boards']);
            }
        }
    }

    public function validateField($field)
    {
        global $context;

        $name = $this->_req->getPost('name', 'trim|strval', '');
        $description = $this->_req->getPost('description', 'trim|strval', '');
        $type = $this->_req->getPost('type', 'trim|strval', '');
        $mask = $this->_req->getPost('mask', 'trim|strval', '');
        $regex = $this->_req->getPost('regex', 'trim|strval', '');
        $this->_dataValidator->input_processing(['select_option' => 'array']);

        $sanitation = [];
        $validation = [
            'name' => 'required',
            'type' => sprintf(
                'required|contains[%s]',
                implode(
                    ',',
                    iterator_to_array($this->get_extends_number('ElkArte\\addons\\PostFields\\PostFieldsBase'))
                )
            ),
        ];
        if ($type == 'text' || $type == 'textarea') {
            $validation['mask'] = sprintf(
                'required|contains[%s]',
                implode(
                    ',',
                    iterator_to_array($this->get_extends_number('ElkArte\\addons\\PostFields\\postFieldMaskBase'))
                )
            );
        }
        if ($mask == 'regex') {
            $validation['regex'] = 'required|regex_syntax';
        }

        $bbc = !empty($_POST['bbc']);
        $active = !empty($_POST['active']);
        $can_search = !empty($_POST['can_search']);

        $groups = !empty($_POST['groups']) ? array_keys($_POST['groups']) : [];
        $boards = !empty($_POST['boards']) ? array_keys($_POST['boards']) : [];

        // Time to check and clean what was placed in the form
        $validator = new DataValidator();

        $validator->sanitation_rules($sanitation);
        $validator->validation_rules($validation);

        // Any errors or are we good to go?
        if (!$validator->validate($this->_req->post)) {
            $context['errors'] = $validator->validation_errors();
            $context['field'] = [
                'length' => $this->_req->getPost('lengt', 'intval', 255),
                'rows' => $this->_req->getPost('rows', 'intval', 4),
                'cols' => $this->_req->getPost('cols', 'intval', 30),
                'active' => $active,
                'can_search' => $can_search,
                'bbc' => $bbc,
                'name' => $name,
                'description' => $description,
                'enclose' => $this->_req->getPost('enclose', 'trim|strval|htmlpurifier', ''),
                'type' => $this->_req->getPost('type', 'trim|strval', ''),
                'options' => $this->_req->getPost('select_option', 'trim|strval', ''),
                'default_select' => $this->_req->getPost('default_select', 'trim|strval', ''),
                'default_check' => $this->_req->getPost('default_check', 'trim|strval', ''),
                'mask' => $mask,
                'regex' => $regex,
                'groups' => $groups,
                'boards' => $boards,
            ];

            return false;
        }
    }

    public function insertField($field)
    {
        $name = $this->_req->getPost('name', 'trim|strval|Util::htmlspecialchars[ENT_QUOTES]', '');
        $description = $this->_req->getPost('description', 'trim|strval|Util::htmlspecialchars[ENT_QUOTES]', '');
        $mask = $this->_req->getPost('mask', 'trim|strval', '');
        $regex = $this->_req->getPost('regex', 'trim|strval', '');
        $type = $this->_req->getPost('type', 'trim|strval', '');
        $enclose = $this->_req->getPost('enclose', 'trim|strval|htmlpurifier', '');

        $bbc = !empty($_POST['bbc']) ? 'yes' : 'no';
        $active = !empty($_POST['active']) ? 'yes' : 'no';
        $can_search = !empty($_POST['can_search']) ? 'yes' : 'no';

        $length = isset($_POST['lengt']) ? (int) $_POST['lengt'] : 255;
        $groups = !empty($_POST['groups']) ? json_encode(array_keys($_POST['groups'])) : '';
        $boards = !empty($_POST['boards']) ? json_encode(array_keys($_POST['boards'])) : '';

        $options = '';
        $newOptions = [];
        $default = isset($_POST['default_check']) && $_POST['type'] == 'check' ? 1 : '';
        if (!empty($_POST['select_option']) && ($_POST['type'] == 'select' || $_POST['type'] == 'radio')) {
            foreach ($_POST['select_option'] as $k => $v) {
                $v = trim(\Util::htmlspecialchars($v, ENT_QUOTES));
                if ($v == '') {
                    continue;
                }
                $newOptions[$k] = $v;
                if (isset($_POST['default_select']) && $_POST['default_select'] == $k) {
                    $default = $v;
                }
            }
            $options = json_encode($newOptions);
        }

        $up_col = [
            'name = {string:name}',
            ' description = {string:description}',
            ' enclose = {string:enclose}',
            '`type` = {string:type}',
            ' size = {int:length}',
            'options = {string:options}',
            'active = {string:active}',
            ' default_value = {string:default_value}',
            'can_search = {string:can_search}',
            ' bbc = {string:bbc}',
            ' mask = {string:mask}',
            ' regex = {string:regex}',
            'groups = {string:groups}',
            ' boards = {string:boards}',
        ];
        $up_data = [
            'length' => $length,
            'active' => $active,
            'can_search' => $can_search,
            'bbc' => $bbc,
            'current_field' => $field,
            'name' => $name,
            'description' => $description,
            'enclose' => $enclose,
            'type' => $type,
            'options' => $options,
            'default_value' => $default,
            'mask' => $mask,
            'regex' => $regex,
            'groups' => $groups,
            'boards' => $boards,
        ];
        $in_col = [
            'name' => 'string',
            'description' => 'string',
            'enclose' => 'string',
            'type' => 'string',
            'size' => 'string',
            'options' => 'string',
            'active' => 'string',
            'default_value' => 'string',
            'can_search' => 'string',
            'bbc' => 'string',
            'mask' => 'string',
            'regex' => 'string',
            'groups' => 'string',
            'boards' => 'string',
        ];
        $in_data = [
            $name,
            $description,
            $enclose,
            $type,
            $length,
            $options,
            $active,
            $default,
            $can_search,
            $bbc,
            $mask,
            $regex,
            $groups,
            $boards,
        ];
        call_integration_hook('integrate_save_post_field', [&$up_col, &$up_data, &$in_col, &$in_data]);

        if ($field) {
            Database::query(
                '',
                '
                UPDATE {db_prefix}message_fields
                SET
                    '.implode(
                    ',
                    ',
                    $up_col
                ).'
                WHERE id_field = {int:current_field}',
                $up_data
            );
        } else {
            Database::insert(
                '',
                '{db_prefix}message_fields',
                $in_col,
                $in_data,
                ['id_field']
            );
        }
    }

    public function deleteFields(array $fields)
    {
        call_integration_hook('integrate_delete_post_fields', [$fields]);

        // Delete the user data first.
        Database::query(
            '',
            '
            DELETE FROM {db_prefix}message_field_data
            WHERE id_field IN ({array_int:fields})',
            [
                'fields' => $fields,
            ]
        );
        // Finally - the fields themselves are gone!
        Database::query(
            '',
            '
            DELETE FROM {db_prefix}message_fields
            WHERE id_field IN ({array_int:fields})',
            [
                'fields' => $fields,
            ]
        );
    }

    public function process($start, $length, $sort, $list, $listId)
    {
        global $context;

        $tmp = [];
        foreach ($list as $key => $row) {
            $tmp[$key] = $row[$sort];
        }
        array_multisort($tmp, substr($sort, -4, 4) == 'DESC' ? SORT_DESC : SORT_ASC, $list);

        if ($length) {
            $list = array_slice($list, $start, $length);
        }

        return $list;
    }

    function get_extends_number($base)
    {
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, $base)) {
                yield trim(strpbrk($class, '_'), '_');
            }
        }
    }
}

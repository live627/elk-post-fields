<?php

function template_edit()
{
    global $context, $txt, $settings, $scripturl;

    echo '
	<div id="admincenter">
		<form action="', $scripturl, '?action=admin;area=postfields;sa=edit" method="post" accept-charset="UTF-8">
			<h2 class="category_header">
				', $context['page_title'], '
			</h2>
			<div class="content">';

    // If an error occurred, explain what happened.
    if (!empty($context['errors'])) {
        echo '
					<div class="errorbox" id="errors">
						<strong>', $txt['validation_failure'], '</strong>
						<ul>';
        foreach ($context['errors'] as $error) {
            echo '
							<li>', $error, '</li>';
        }
        echo '
						</ul>
					</div>';
    }
    echo '
				<fieldset>
					<legend>', $txt['PostFields_general'], '</legend>

					<dl class="settings">
						<dt>
							<strong>', $txt['PostFields_name'], ':</strong>
						</dt>
						<dd>
							<input type="text" name="name" value="', $context['field']['name'], '" size="20" maxlength="40">
						</dd>
						<dt>
							<strong>', $txt['PostFields_description'], ':</strong>
						</dt>
						<dd>
							<textarea name="description" rows="3" cols="40">', $context['field']['description'], '</textarea>
						</dd>
						<dt>
							<a id="field_show_enclosed" href="', $scripturl, '?action=helpadmin;help=field_show_enclosed" onclick="return reqWin(this);" class="help" title="', $txt['help'], '"></a>
							<strong>', $txt['PostFields_enclose'], ':</strong>
							<br /><span class="smalltext">', $txt['PostFields_enclose_desc'], '</span>
						</dt>
						<dd>
							<textarea name="enclose" rows="10" cols="50">', $context['field']['enclose'], '</textarea>
						</dd>
						<dt>
							<strong>', $txt['PostFields_boards'], ':</strong>
						</dt>
						<dd>
							<div class="information">
								<label>
									<input type="checkbox" class="input_check" onclick="invertAll(this, this.form, \'boards\');"', $context['all_boards_checked'] ? ' checked="checked"' : '', ' /> <em>', $txt['check_all'], '</em></label><br />';

    foreach ($context['categories'] as $id_category => $category) {
        echo '
											<strong>', $category['name'], '</strong>
											<ul class="reset">';

        foreach ($category['boards'] as $board) {
            echo '
												<li><label><input type="checkbox" name="boards[', $board['id'], ']" class="input_check"', in_array(
                $board['id'],
                $context['field']['boards']
            ) ? ' checked ' : '', empty($board['child_level']) ?: ' style="margin-'.($context['right_to_left'] ? 'right' : 'left').': '.$board['child_level'] * 1.5 .'em;"', '>', $board['name'], '</label></li>';
        }

        echo '
											</ul>';
    }

    echo '
							</div>
						</dd>
						<dt>
							<strong>', $txt['PostFields_groups'], ':</strong>
						</dt>
						<dd>
							<div class="information">
								<label>
									<input type="checkbox" class="input_check" onclick="invertAll(this, this.form, \'groups\');"', $context['all_groups_checked'] ? ' checked="checked"' : '', ' /> <em>', $txt['check_all'], '</em></label><br />';

    foreach ($context['groups'] as $group) {
        echo '
								<label>
									<input type="checkbox" name="groups[', $group['id'], ']"', $group['checked'] ? ' checked' : '', '>
									<span', $group['is_post_group'] ? ' class="post_group" title="'.$txt['mboards_groups_post_group'].'"' : ($group['id'] == 0 ? ' class="regular_members" title="'.$txt['mboards_groups_regular_members'].'"' : ''), $group['color'] ? ' style="color: '.$group['color'].'"' : '', '>
										', $group['name'], '
									</span>
								</label>
								<br />';
    }

    echo '
							</div>
						</dd>
					</dl>
				</fieldset>
				<fieldset>
					<legend>', $txt['PostFields_input'], '</legend>
					<dl class="settings">
						<dt>
							<strong>', $txt['PostFields_picktype'], ':</strong>
						</dt>
						<dd>
							<select name="type" id="field_type" onchange="updateInputBoxes();">';

    foreach ($context['field']['types'] as $type) {
        printf(
            '
								<option value="%s"%s>%s</option>',
            $type,
            $context['field']['type'] == $type ? ' selected' : '',
            $txt['PostFields_type_'.$type]
        );
    }

    echo '
							</select>
						</dd>
						<dt id="max_length_dt">
							<strong>', $txt['PostFields_max_length'], ':</strong>
							<br /><span class="smalltext">', $txt['PostFields_max_length_desc'], '</span>
						</dt>
						<dd id="max_length_dd">
							<input type="text" name="lengt" value="', $context['field']['length'], '" size="7" maxlength="6">
						</dd>
						<dt id="dimension_dt">
							<strong>', $txt['PostFields_dimension'], ':</strong>
						</dt>
						<dd id="dimension_dd">
							<strong>', $txt['PostFields_dimension_row'], ':</strong> <input type="text" name="rows" value="', $context['field']['rows'], '" size="5" maxlength="3">
							<strong>', $txt['PostFields_dimension_col'], ':</strong> <input type="text" name="cols" value="', $context['field']['cols'], '" size="5" maxlength="3">
						</dd>
						<dt id="size_dt">
							<strong>', $txt['PostFields_size'], ':</strong>
							<br /><span class="smalltext">', $txt['PostFields_size_desc'], '</span>
						</dt>
						<dd id="size_dd">
							<strong>', $txt['PostFields_size_row'], ':</strong> <input type="text" name="rows" value="', $context['field']['rows'], '" size="5" maxlength="3">
							<strong>', $txt['PostFields_size_col'], ':</strong> <input type="text" name="cols" value="', $context['field']['cols'], '" size="5" maxlength="3">
						</dd>
						<dt id="bbc_dt">
							<strong>', $txt['PostFields_bbc'], '</strong>
						</dt>
						<dd id="bbc_dd">
							<input type="checkbox" name="bbc"', $context['field']['bbc'] ? ' checked' : '', '>
						</dd>
						<dt id="options_dt">
							<a href="', $scripturl, '?action=helpadmin;help=postoptions" onclick="return reqWin(this);" class="help" title="', $txt['help'], '"></a>
							<strong>', $txt['PostFields_options'], ':</strong>
							<br /><span class="smalltext">', $txt['PostFields_options_desc'], '</span>
						</dt>
						<dd id="options_dd">
							<div>';

    foreach ($context['field']['options'] as $k => $option) {
        printf(
            '
								%s<input type="radio" name="default_select" value="%s"%s><input type="text" name="select_option[%2$s]" value="%s">',
            $k == 0 ? '' : '<br>',
            $k,
            $context['field']['default_select'] == $k ? ' checked' : '',
            $option
        );
    }

    echo '
								<span id="addopt"></span>
								[<a href="" onclick="addOption(); return false;">', $txt['more'], '</a>]
							</div>
						</dd>
						<dt id="default_dt">
							<strong>', $txt['PostFields_default'], ':</strong>
						</dt>
						<dd id="default_dd">
							<input type="checkbox" name="default_check"', $context['field']['default_check'] ? ' checked' : '', '>
						</dd>
					</dl>
				</fieldset>
				<fieldset>
					<legend>', $txt['PostFields_advanced'], '</legend>
					<dl class="settings">
						<dt id="mask_dt">
							<a id="post_mask" href="', $scripturl, '?action=helpadmin;help=post_mask" onclick="return reqWin(this);" class="help" title="', $txt['help'], '"></a>
							<strong>', $txt['PostFields_mask'], ':</strong>
							<br /><span class="smalltext">', $txt['PostFields_mask_desc'], '</span>
						</dt>
						<dd id="mask_dd">
							<select name="mask" id="field_mask" onchange="updateInputBoxes2();">';

    foreach ($context['field']['masks'] as $mask) {
        printf(
            '
								<option value="%s"%s>%s</option>',
            $mask,
            $context['field']['mask'] == $mask ? ' selected' : '',
            $txt['PostFields_mask_'.$mask]
        );
    }

    echo '
							</select>
						</dd>
						<dt id="regex_dt">
							<a id="post_regex" href="', $scripturl, '?action=helpadmin;help=post_regex" onclick="return reqWin(this);" class="help" title="', $txt['help'], '"></a>
							<strong>', $txt['PostFields_regex'], ':</strong>
							<br /><span class="smalltext">', $txt['PostFields_regex_desc'], '</span>
						</dt>
						<dd id="regex_dd">
							<input type="text" name="regex" value="', $context['field']['regex'], '" size="30">
						</dd>
						<dt id="can_search_dt">
							<strong>', $txt['PostFields_can_search'], ':</strong>
							<br /><span class="smalltext">', $txt['PostFields_can_search_desc'], '</span>
						</dt>
						<dd id="can_search_dd">
							<input type="checkbox" name="can_search"', $context['field']['can_search'] ? ' checked' : '', '>
						</dd>
						<dt>
							<strong>', $txt['PostFields_active'], ':</strong>
							<br /><span class="smalltext">', $txt['PostFields_active_desc'], '</span>
						</dt>
						<dd>
							<input type="checkbox" name="active"', $context['field']['active'] ? ' checked' : '', '>
						</dd>
					</dl>
				</fieldset>
				<div class="righttext">
					<input type="submit" name="save" value="', $txt['save'], '" class="submit">';

    if ($context['fid']) {
        echo '
					<input type="submit" name="delete" value="', $txt['delete'], '" onclick="return confirm(', JavaScriptEscape(
            $txt['PostFields_delete_sure']
        ), ');" class="delete">';
    }

    echo '
				</div>
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">';

    if ($context['fid']) {
        echo '
			<input type="hidden" name="fid" value="', $context['fid'], '">';
    }

    echo '
		</form>
	</div>
	<script type="text/javascript">
		var startOptID = ', count($context['field']['options']), ';
		updateInputBoxes(true);
		updateInputBoxes2(true);
		</script>
	<br class="clear">';
}

function template_input_post_fields_below()
{
    global $context, $scripturl, $settings, $txt;

    if (!empty($context['fields'])) {

        echo '
					<fieldset id="postFieldsOnTopic" class="smalltext">
						<legend>', $txt['PostFields_title'], '</legend>';

        foreach ($context['fields'] as $field) {
            $call = strtr($field['name'], ' ', '_');

            if (is_callable('template_pf_'.$call)) {
                call_user_func_array('template_pf_'.$call, [$field]);
            } else {
                echo '
							<div>
								<strong>', $field['name'], ': </strong>';

                if ($field['description']) {
                    echo '<br />
								<span class="smalltext">', $field['description'], '</span>';
                }

                echo '
								', $field['input_html'], '
							</div>';
            }
        }
        echo '
					</fieldset>';
    }
}

function template_search_post_fields()
{
    global $context, $scripturl;

    if (!empty($context['fields'])) {
        foreach (array_reverse($context['fields']) as $field) {
            if (is_callable('template_search_pf_'.$field['name'])) {
                call_user_func_array('template_search_pf_'.$field['name'], [$field]);
            }
        }
    } else {
        echo '
						<dt>
							', $field['name'], ': <br />
							<span class="smalltext">', $field['description'], '</span>
						</dt>
						<dd>
							', $field['input_html'], '
						</dd>';
    }
}

function template_search_post_fields_simple()
{
    echo '
		<fieldset id="advanced_search">
				<dl id="search_options" style="padding-top: 0;">';

    template_search_post_fields();

    echo '
				</dl>
		</fieldset>';
}

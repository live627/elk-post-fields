<?php

/**
 * @package   PostFields
 * @version   2.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2011-2016, John Rayes
 * @license   proprietary
 */

namespace ElkArte\addons\PostFields;

class DataValidator extends \Data_Validator
{
    /**
     * regex ... Determine if the provided value is a regular expression
     *
     * Usage: '[key]' => 'regex'
     *
     * @param string       $field
     * @param mixed[]      $input
     * @param mixed[]|null $validation_parameters array or null
     */
    protected function _validate_regex_syntax($field, $input, $validation_parameters = null)
    {
        if (!isset($input[$field])) {
            return;
        }

        // Turn off all error reporting
        $errMask = error_reporting(0);

        // Catch any errors the regex may produce.
        set_error_handler([$this, 'handleError']);

        if (preg_match($input[$field], null) === false) {
            restore_error_handler();
            error_reporting($errMask);

            return [
                'error_msg' => error_get_last()['message'],
                'error' => 'validate_regex_syntax',
                'field' => $field,
            ];
        }
    }

    /**
     * Custom error handler
     *
     * @param integer  $code
     * @param string   $description
     * @param string   $file
     * @param interger $line
     * @param mixed    $context
     *
     * @return boolean
     */
    function handleError($code, $description, $file = null, $line = null, $context = null)
    {
        return false;
    }

    /**
     * htmlpurifier ... Determine if the provided value is a regular expressions
     *
     * Usage: '[key]' => 'htmlpurifier'
     *
     * @param string       $field
     * @param mixed[]|null $validation_parameters array or null
     */
    protected function _sanitation_htmlpurifier($field, $validation_parameters = null)
    {
        require_once __DIR__.'/HTMLPurifier.standalone.php';
        $definition = \HTMLPurifier_ConfigSchema::instance();
        $definition->add('HTML.TargetNoopener', true, 'bool', false);
        $definition->add('Core.LegacyEntityDecoder', false, 'bool', false);
        $definition->add('Core.AggressivelyRemoveScript', true, 'bool', false);
        $config = new \HTMLPurifier_Config($definition);
        $config->set('HTML.Doctype', 'XHTML 1.1');
        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
        $purifier = new \HTMLPurifier($config);

        return $purifier->purify($field);
    }
}

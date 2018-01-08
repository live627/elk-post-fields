<?php

/**
 * @package   PostFields
 * @version   2.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2011-2016, John Rayes
 * @license   proprietary
 *
 *
 * This file contains code by:
 *
 *  - Ohara v1.0
 *  - Copyright (c) 2014, Jessica González
 *  - license http://www.mozilla.org/MPL/2.0/
 */

namespace ElkArte\addons\PostFields;

class Ohara extends \Action_Controller
{
    /**
     * The main identifier for the class extending Ohar;
     * needs to be re-defined by each extending class.
     *
     * @access public
     * @var string
     */
    public $name = '';

    /**
     * Text array for holding your own text strings.
     *
     * @access protected
     * @var array
     */
    protected $text = [];

    /**
     * holds instance of the validator
     * @var DataValidator
     */
    protected $_dataValidator;

    /**
     * Sets many properties replacing SMF's global vars.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        global $scripturl, $settings, $boardurl;

        $this->scriptUrl = $scripturl;
        $this->settings = $settings;
        $this->boardUrl = $boardurl;
        parent::__construct();
        $this->_dataValidator = new DataValidator;
        $this->_req = new \HttpReq($this->_dataValidator);
    }

    /**
     * Noise.
     *
     * @access public
     * @abstracting \Action_Controller
     * @return void
     */
    public function action_index()
    {
    }

    /**
     * Getter for {@link $name} property.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Getter for {@link $text} property.
     *
     * @access public
     *
     * @param string $var The name of the $txt key you want to retrieve
     *
     * @return bool|string
     */
    public function text($var)
    {
        global $txt;

        // This should be extended by somebody else...
        if (empty($this->name) || empty($var)) {
            return false;
        }

        if (!isset($this->text[$var])) {
            $this->setText($var);
        }

        return $this->text[$var];
    }

    /**
     * Loads the extending class language file and sets a new key in {@link $text}
     * Ohara automatically adds the value of {@link $var} plus an underscore
     * to match the exact $txt key when fetching the var
     *
     * @access protected
     *
     * @param string $var The name of the $txt key you want to retrieve
     *
     * @return string
     */
    protected function setText($var)
    {
        global $txt;

        if (empty($var)) {
            return false;
        }
        // Load the mod's language file.
        loadLanguage($this->name);
        if (!empty($txt[$this->name.'_'.$var])) {
            $this->text[$var] = $txt[$this->name.'_'.$var];
        } elseif (!empty($txt[$var])) {
            $this->text[$var] = $txt[$var];
        } else {
            $this->text[$var] = false;
        }

        return $this->text[$var] !== false;
    }

    /**
     * Getter for {@link $text}
     * @access public
     * @return array
     */
    public function getAllText()
    {
        return $this->text;
    }

    /**
     * Checks for a $modSetting key and its state
     * returns true if the $modSetting exists and its not empty
     * regardless of what its value is
     *
     * @param string $var The name of the $modSetting key you want to retrieve
     *
     * @access public
     * @return boolean
     */
    public function enable($var)
    {
        global $modSettings;

        if (empty($var)) {
            return false;
        }

        if (isset($modSettings[$this->name.'_'.$var]) && !empty($modSettings[$this->name.'_'.$var])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the actual value of the selected $modSetting
     * uses Ohara::enable() to determinate if the var exists
     *
     * @param string $var The name of the $modSetting key you want to retrieve
     *
     * @access public
     * @return mixed|boolean
     */
    public function setting($var)
    {
        global $modSettings;

        // This should be extended by somebody else...
        if (empty($this->name) || empty($var)) {
            return false;
        }

        if (true == $this->enable($var)) {
            return $modSettings[$this->name.'_'.$var];
        } else {
            return false;
        }
    }

    /**
     * Returns the actual value of a generic $modSetting var
     * useful to check external $modSettings vars
     *
     * @param string $var The name of the $modSetting key you want to retrieve
     *
     * @access public
     * @return mixed|boolean
     */
    public function modSetting($var)
    {
        global $modSettings;

        // This should be extended by somebody else...
        if (empty($this->name)) {
            return false;
        }

        if (empty($var)) {
            return false;
        }

        if (isset($modSettings[$var])) {
            return $modSettings[$var];
        } else {
            return false;
        }
    }
}

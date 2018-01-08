<?php

/**
 * @package   PostFields
 * @version   2.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2011-2016, John Rayes
 * @license   proprietary
 */

namespace ElkArte\addons\PostFields;

class Settings
{
    protected $settings = [];

    public function getSettings()
    {
        return $this->settings;
    }

    protected function setSettings($settings)
    {
        $this->settings = $settings;
    }

    public function __construct($settings)
    {
        $this - setSettings($settings);
    }

    public function text($var)
    {
        global $txt;
        // This should be extended by somebody else...
        if (empty($this->settings) || empty($var)) {
            return false;
        }
        if (!isset($this->_text[$var])) {
            $this->setText($var);
        }

        return $this->_text[$var];
    }

    protected function setText($var)
    {
        global $txt;
        // No var no set.
        if (empty($var)) {
            return false;
        }
        // Load the mod's language file.
        loadLanguage($this->settings);
        if (!empty($txt[$this->settings.'_'.$var])) {
            $this->_text[$var] = $txt[$this->settings.'_'.$var];
        } else {
            $this->_text[$var] = false;
        }
    }

    public function getAllText()
    {
        return $this->_text;
    }

    public function enable($var)
    {
        global $modSettings;
        if (empty($var)) {
            return false;
        }
        if (isset($modSettings[$this->settings.'_'.$var]) && !empty($modSettings[$this->settings.'_'.$var])) {
            return true;
        } else {
            return false;
        }
    }

    public function setting($var)
    {
        global $modSettings;
        // This should be extended by somebody else...
        if (empty($this->settings) || empty($var)) {
            return false;
        }
        if (true == $this->enable($var)) {
            return $modSettings[$this->settings.'_'.$var];
        } else {
            return false;
        }
    }

    public function modSetting($var)
    {
        global $modSettings;
        // This should be extended by somebody else...
        if (empty($this->settings)) {
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

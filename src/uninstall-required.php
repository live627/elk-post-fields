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

disableModules('postfields', ['post', 'display']);
Hooks::instance()->disableIntegration(self::class);
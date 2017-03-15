<?php
/*
 -------------------------------------------------------------------------
 RaiseManager plugin for GLPI
 Copyright (C) 2017 by the RaiseManager Development Team.

 https://github.com/pluginsGLPI/raisemanager
 -------------------------------------------------------------------------

 LICENSE

 This file is part of RaiseManager.

 RaiseManager is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 RaiseManager is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with RaiseManager. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_RAISEMANAGER_VERSION', '0.0.1');

foreach (glob(GLPI_ROOT . '/plugins/raisemanager/inc/*.php') as $file) {
   include_once ($file);
}

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_raisemanager() {
   global $PLUGIN_HOOKS,$LANG,$CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['raisemanager'] = true;

   $plugin = new Plugin();
   if ($plugin->isInstalled("raisemanager") && $plugin->isActivated("raisemanager")) {

      Plugin::registerClass('PluginRaisemanagerRaiseTemplate');
      Plugin::registerClass('PluginRaisemanagerMenu');

      if (Session::getLoginUserID()) {

         if (Session::haveRight("config", UPDATE)) {
            $PLUGIN_HOOKS['config_page']['raisemanager'] = 'front/config.form.php';
         }

         //if (PluginRaiseManagerRaiseTemplate::canView()) {
            $PLUGIN_HOOKS['menu_toadd']['raisemanager'] = array('config' => 'PluginRaiseManagerMenu');
         //}

      }
   }

}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_raisemanager() {
   return [
      'name'           => 'RaiseManager',
      'version'        => PLUGIN_RAISEMANAGER_VERSION,
      'author'         => '<a href="http://www.teclib.com">Teclib\'</a>',
      'license'        => '',
      'homepage'       => '',
      'minGlpiVersion' => '9.1'
   ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_raisemanager_check_prerequisites() {
   // Strict version check (could be less strict, or could allow various version)
   if (version_compare(GLPI_VERSION, '9.1', 'lt')) {
      if (method_exists('Plugin', 'messageIncompatible')) {
         echo Plugin::messageIncompatible('core', '9.1');
      } else {
         echo "This plugin requires GLPI >= 9.1";
      }
      return false;
   }
   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_raisemanager_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      _e('Installed / not configured', 'raisemanager');
   }
   return false;
}

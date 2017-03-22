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

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_raisemanager_install() {

   $migration = new Migration(PLUGIN_RAISEMANAGER_VERSION);

   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginRaisemanager' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'install')) {
            $classname::install($migration);
         }
      }
   }
   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_raisemanager_uninstall() {

   $migration = new Migration(PLUGIN_RAISEMANAGER_VERSION);

   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginRaisemanager' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'uninstall')) {
            $classname::uninstall($migration);
         }
      }
   }
   return true;
}

/**
 * Define Dropdown tables to be manage in GLPI :
 */
function plugin_raisemanager_getDropdown() {

   $pluginDropdowns = [ 'PluginRaisemanagerRaisetemplate' => __('RaiseTemplate', 'raisemanager'),
                        'PluginRaisemanagerRaiselevel'    => __('RaiseLevel', 'raisemanager')];

   return $pluginDropdowns;
}

function plugin_raisemanager_add_events(NotificationTargetCommonITILObject $target) {
   $target->events['plugin_raisemanager'] = __("RaiseEvent", 'raisemanager');
}

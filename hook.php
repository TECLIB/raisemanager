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

foreach (glob(GLPI_ROOT . '/plugins/raisemanager/inc/*.php') as $file) {
   include_once ($file);
}

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_raisemanager_install() {
   $migration = new Migration(PLUGIN_RAISEMANAGER_VERSION);
   PluginRaisemanagerRaiseTemplate::install($migration);
   PluginRaisemanagerRaiseLevel::install($migration);
   PluginRaisemanagerRaiseLevelTemplate::install($migration);

   CronTask::Register('PluginRaisemanagerNotification', 'SendNotifications', MINUTE_TIMESTAMP);

   return true;
}

function plugin_raisemanager_add_events(NotificationTargetCommonITILObject $target) {
   $target->events['plugin_raisemanager'] = __("Raise event", 'raisemanager');
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_raisemanager_uninstall() {
   return true;
}

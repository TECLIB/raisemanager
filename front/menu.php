<?php
/*
 LICENSE

 This file is part of the raisemanager plugin.

 RaiseManager plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 RaiseManager plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with RaiseManager. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   raisemanager
 @author    Teclib'
 @copyright Copyright (c) 2017-2018 Teclib'
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://github.com/TECLIB/raisemanager
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

include ("../../../inc/includes.php");

Html::header(
   __("Raises", "raisemanager"),
   $_SERVER['PHP_SELF'],
   "management",
   "PluginRaisemanagerMenu"
);

$PluginRaisemanagerRaiseTemplate     = new PluginRaisemanagerRaiseTemplate();
$PluginRaisemanagerRaiseLevel = new PluginRaisemanagerRaiseLevel();

//If there's only one possibility, do not display menu!
if (PluginRaisemanagerRaiseTemplate::canView() && !PluginRaisemanagerRaiseLevel::canView()) {
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginRaisemanagerRaiseTemplate'));

} else if (!PluginRaisemanagerRaiseTemplate::canView() && PluginRaisemanagerRaiseLevel::canView()) {
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginRaisemanagerRaiseLevel'));
}

if (PluginRaisemanagerRaiseTemplate::canView() || PluginRaisemanagerRaiseLevel::canView()) {
   echo "<div class='center'>";
   echo "<table class='tab_cadre'>";
   echo "<tr><th colspan='2'>" . __("Raises", "raisemanager") . "</th></tr>";

   if (PluginRaisemanagerRaiseTemplate::canView()) {
      echo "<tr class='tab_bg_1' align='center'>";
      //echo "<td><img src='../pics/order-icon.png'></td>";
      echo "<td><a href='".Toolbox::getItemTypeSearchURL('PluginRaisemanagerRaiseTemplate')."'>" .
         __("RaiseTemplates", "raisemanager") . "</a></td></tr>";
   }

   if (PluginRaisemanagerRaiseLevel::canView()) {
      echo "<tr class='tab_bg_1' align='center'>";
      //echo "<td><img src='../pics/reference-icon.png'></td>";
      echo "<td><a href='".Toolbox::getItemTypeSearchURL('PluginRaisemanagerRaiseLevel')."'>" .
         __("RaiseLevels", "raisemanager") . "</a></td></tr>";
   }

   echo "</table></div>";
} else {
   echo "<div align='center'><br><br><img src=\"" . $CFG_GLPI["root_doc"] .
         "/pics/warning.png\" alt=\"warning\"><br><br>";
   echo "<b>" . __("Access denied") . "</b></div>";
}

Html::footer();
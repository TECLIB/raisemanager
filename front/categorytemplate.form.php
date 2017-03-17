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

$raisecategorytemplate = new PluginRaisemanagerCategoryTemplate();

if (isset($_POST["additem"]) && PluginRaisemanagerCategoryTemplate::canCreate()) {
   $newID = $raisecategorytemplate->add($_POST);
}

if (isset($_POST["delete_items"]) && PluginRaisemanagerCategoryTemplate::canDelete()) {
   if (isset($_POST['todelete'])) {
      foreach ($_POST['todelete'] as $id => $val) {
         if ($val == 'on') {
            $ok = $raisecategorytemplate->delete(array('id' => $id));
         }
      }
   }
}
Html::back();

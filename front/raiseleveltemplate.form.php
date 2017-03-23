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

include ('../../../inc/includes.php');

Session::haveRightsOr("dropdown", [CREATE, UPDATE, DELETE, PURGE]);

$PluginRaisemanagerRaiseleveltemplate = new PluginRaisemanagerRaiseleveltemplate();

if (isset($_POST["add"])) {

   $PluginRaisemanagerRaiseleveltemplate->check(-1, CREATE, $_POST);
   if ($PluginRaisemanagerRaiseleveltemplate->add($_POST)) {
      Session::addMessageAfterRedirect(__('Operation successful'));
   } else {
      Session::addMessageAfterRedirect(__('Failed operation'), false, ERROR);
   }
   Html::back();

}

Html::displayErrorAndDie("lost");

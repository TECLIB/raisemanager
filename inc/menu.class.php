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

class PluginRaisemanagerMenu extends CommonGLPI {

   public static function getTypeName($nb = 0) {
      return __("Raises", "raisemanager");
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu          = array();
      $menu['title'] = self::getTypeName(2);
      $menu['page']  = self::getSearchURL(false);

      if (PluginRaisemanagerRaiseTemplate::canView()) {
         $menu['options']['raisetemplate']['title']                 = PluginRaisemanagerRaiseTemplate::getTypeName(2);
         $menu['options']['raisetemplate']['page']                  = PluginRaisemanagerRaiseTemplate::getSearchURL(false);
         $menu['options']['raisetemplate']['links']['search']       = PluginRaisemanagerRaiseTemplate::getSearchURL(false);
         if (PluginRaisemanagerRaiseTemplate::canCreate()) {
            $menu['options']['raisetemplate']['links']['add']       = PluginRaisemanagerRaiseTemplate::getFormURL(false);
         }
      }

      if (PluginRaisemanagerRaiseLevel::canView()) {
         $menu['options']['raiselevel']['title']                 = PluginRaisemanagerRaiseLevel::getTypeName(2);
         $menu['options']['raiselevel']['page']                  = PluginRaisemanagerRaiseLevel::getSearchURL(false);
         $menu['options']['raiselevel']['links']['search']       = PluginRaisemanagerRaiseLevel::getSearchURL(false);
         if (PluginRaisemanagerRaiseLevel::canCreate()) {
            $menu['options']['raiselevel']['links']['add']       = PluginRaisemanagerRaiseLevel::getFormURL(false);
         }
      }

      return $menu;
   }

   function install() {
   }
}

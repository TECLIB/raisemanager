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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginRaisemanagerRaiseLevel extends CommonDBTM {
   public $dohistory = true;

   // Templates of this level
   public $raisetemplates     = array();

   public static function getTypeName($nb = 0) {
      return __("Raise level", "raisemanager");
   }

   public static function canCreate() {
      return true;
   }

   public static function canPurge() {
      return true;
   }

   public static function canDelete() {
      return true;
   }

   public static function canUpdate() {
      return true;
   }

   public static function canView() {
      return true;
   }

   public function defineTabs($options=array()) {
      $ong = array();
      //add main tab for current object
      $this->addDefaultFormTab($ong);

      if ($this->fields['id'] > 0) {
         $this->addStandardTab('PluginRaisemanagerRaiseLevelTemplate', $ong, $options);
      }
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   /**
    * Définition du nom de l'onglet
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      switch ($item::getType()) {
         case __CLASS__:
            return __('RaiseLevel', 'raisemanager');
            break;
      }
      return '';
   }

   /**
    * Show form
    *
    * @param integer $ID      Item ID
    * @param array   $options Options
    *
    * @return void
    */
   function showForm($ID, $options=array()) {
      global $CFG_GLPI, $DB;

      $this->getFromDB($ID);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      echo "<td rowspan='3'>".__('Comments')."</td>";
      echo "<td rowspan='3'>";
      echo "<textarea name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__('Is Multiple')."</td>";
      echo "<td>";

      Dropdown::showYesNo('trigger_is_multiple', $this->fields["trigger_is_multiple"]);

      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__('Trigger Type')."</td>";
      echo "<td>";

      $aDurationTypes = array('Secondes', 'Minutes', 'Jours', 'Semaines', 'Mois', 'Années');

      Dropdown::showFromArray('trigger_unit', $aDurationTypes, array(
        'values'     => explode(', ', $this->fields["trigger_unit"]),
        'multiple'   => false
      ));

      echo "</td></tr>";

      echo "<tr><td>" . __('Trigger Value') . "</td>";
      echo "<td><input type='text' name='trigger_value' value='".$this->fields["trigger_value"]."'>";
      echo "</td></tr>";

      $this->showFormButtons($options);

      return true;
   }

   public static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");
         $query ="CREATE TABLE IF NOT EXISTS `".getTableForItemType(__CLASS__)."` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `entities_id` int(11) NOT NULL DEFAULT '0',
                    `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
                    `trigger_value` int(11) NOT NULL DEFAULT '1',
                    `trigger_unit` int(11) NOT NULL DEFAULT '0',
                    `trigger_is_multiple` tinyint(1) NOT NULL DEFAULT '0',
                    `comment` text COLLATE utf8_unicode_ci,
                    PRIMARY KEY (`id`),
                    KEY `name` (`name`),
                    KEY `entities_id` (`entities_id`),
                    KEY `is_recursive` (`is_recursive`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
         $DB->query($query) or die ($DB->error());
      }

      return true;
   }

   public static function uninstall() {
      global $DB;

      $DB->query("DROP TABLE IF EXISTS `" . getTableForItemType(__CLASS__) . "`") or die ($DB->error());
   }
}

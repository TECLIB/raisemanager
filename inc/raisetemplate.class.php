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

class PluginRaisemanagerRaisetemplate extends CommonDropdown {

   public $dohistory = true;

   // ITIL Categories linked to this template
   public $itilcategories  = array();
   // Levels of this template
   public $raiselevels     = array();

   public static function getTypeName($nb = 0) {
      return __("RaiseTemplate", "raisemanager");
   }

   public function defineTabs($options=array()) {
      $ong = array();
      //add main tab for current object
      $this->addDefaultFormTab($ong);

      if ($this->fields['id'] > 0) {
         $this->addStandardTab('PluginRaisemanagerCategorytemplate', $ong, $options);
         $this->addStandardTab('PluginRaisemanagerRaiseleveltemplate', $ong, $options);
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
            return __('RaiseTemplate', 'raisemanager');
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

      echo "<tr class='tab_bg_1'><td>".__('Calendar')."</td>";
      echo "<td>";

      Calendar::dropdown(array('value'      => $this->fields["calendars_id"],
                               'emptylabel' => __('24/7'),
                               'toadd'      => array('-1' => __('Calendar of the template'))));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".__('Subtypes', 'raisemanager')."</td>";
      echo "<td>";

      $aItilObjects  = array();
      foreach (get_declared_classes() as $class) {
         if ($class instanceof CommonITILObject) {
            $aItilObjects[] = $class;
         }
      }

      $aItilObjects = array('Ticket' => 'Ticket', 'Problem' => 'Problem', 'Change' => 'Change');

      Dropdown::showFromArray('itemtypes', $aItilObjects, array(
        'values'     => explode(', ', $this->fields["itemtypes"]),
        'multiple'   => true
      ));

      echo "</td></tr>";

      $this->showFormButtons($options);

      return true;
   }

   /**
    * @since version 0.83.3
    *
    * @see CommonDBTM::prepareInputForAdd()
   **/
   public function prepareInputForAdd($input) {

      if (isset($input['itemtypes']) && is_array($input['itemtypes'])) {
         $input['itemtypes'] = implode(', ', $input['itemtypes']);
      }

      return parent::prepareInputForAdd($input);
   }

   /**
    * @since version 0.83.3
    *
    * @see CommonDBTM::prepareInputForUpdate()
   **/
   public function prepareInputForUpdate($input) {

      if (isset($input['itemtypes']) && is_array($input['itemtypes'])) {
         $input['itemtypes'] = implode(', ', $input['itemtypes']);
      }

      return parent::prepareInputForUpdate($input);
   }

   /**
    * Install all necessary table for the plugin
    *
    * @return boolean True if success
    */
   static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);

      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");

         $query ="CREATE TABLE IF NOT EXISTS `$table` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `entities_id` int(11) NOT NULL DEFAULT '0',
                    `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
                    `calendars_id` int(11) NOT NULL DEFAULT '-2',
                    `itemtypes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `comment` text COLLATE utf8_unicode_ci,
                    PRIMARY KEY (`id`),
                    KEY `name` (`name`),
                    KEY `entities_id` (`entities_id`),
                    KEY `is_recursive` (`is_recursive`),
                    KEY `calendars_id` (`calendars_id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
         $DB->query($query) or die ($DB->error());
      }
   }

   /**
    * Uninstall previously installed table of the plugin
    *
    * @return boolean True if success
    */
   static function uninstall(Migration $migration) {

      $table = getTableForItemType(__CLASS__);

      $migration->displayMessage("Uninstalling $table");

      $migration->dropTable($table);
   }
}

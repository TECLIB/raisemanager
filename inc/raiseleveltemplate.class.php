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

class PluginRaisemanagerRaiseleveltemplate extends CommonDBTM {

   static $rightname = 'dropdown';

   /**
    * @see CommonGLPI::getTabNameForItem()
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      switch ($item->getType()) {
         case 'PluginRaisemanagerRaisetemplate' :
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginRaisemanagerRaiselevel::getTypeName(2),
                                           self::countForTemplate($item));
            } else {
               return PluginRaisemanagerRaiselevel::getTypeName(2);
            }
            break;
         case 'PluginRaisemanagerRaiselevel' :
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginRaisemanagerRaisetemplate::getTypeName(2),
                                           self::countForLevel($item));
            } else {
               return PluginRaisemanagerRaisetemplate::getTypeName(2);
            }
            break;
      }

      return '';
   }

   /**
    * @see CommonGLPI::displayTabContentForItem()
   **/
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      switch ($item->getType()) {
         case 'PluginRaisemanagerRaisetemplate' :
            self::showForTemplate($item);
            break;
         case 'PluginRaisemanagerRaiselevel' :
            self::showForLevel($item);
            break;
      }
      return true;
   }

   /**
    *
    * Count the number of associated items for a raisetemplate item
    *
    * @param $item   RaiseTemplate object
    **/
   static function countForTemplate(PluginRaisemanagerRaisetemplate $item) {
      return countElementsInTable(getTableForItemType(__CLASS__),
                                    "`templates_id` = '".$item->getID()."'");
   }

   /**
    *
    * Count the number of associated items for a raiselevel item
    *
    * @param $item   PluginRaisemanagerRaiselevel object
    **/
   static function countForLevel(PluginRaisemanagerRaiselevel $item) {
      return countElementsInTable(getTableForItemType(__CLASS__),
                                    "`items_id` = '".$item->getID()."'");
   }

   /**
    * Hook called After an item is uninstall or purge
    */
   static function cleanForItem(CommonDBTM $item) {
      $oObj = new self();

      $aCriteria = array(
         'itemtype' => $item->getType(),
         'items_id' => $item->getField('id')
      );

      $oObj->deleteByCriteria($aCriteria);
   }

   /**
    * Get all levels for a raisetemplate
    *
    * @param $ID           integer     raisetemplate ID
    * @param $start        integer     first line to retrieve (default 0)
    * @param $limit        integer     max number of line to retrive (0 for all) (default 0)
    * @param $sqlfilter    string      to add an SQL filter (default '')
    * @return array of levels
   **/
   static function getAllForTemplate($ID, $start=0, $limit=0, $sqlfilter='') {
      global $DB;

      $query = "SELECT *
                FROM `" . getTableForItemType(__CLASS__) . "`
                WHERE `templates_id` = '$ID'";
      if ($sqlfilter) {
         $query .= "AND ($sqlfilter) ";
      }
      $query .= "ORDER BY `id` DESC";

      if ($limit) {
         $query .= " LIMIT ".intval($start)."," . intval($limit);
      }

      $raisetemplates = array();
      foreach ($DB->request($query) as $data) {
         $raisetemplates[$data['id']] = $data;
      }

      return $raisetemplates;
   }

   /**
    * Get all templates for a raiselevel
    *
    * @param $ID           integer     raiselevel ID
    * @param $start        integer     first line to retrieve (default 0)
    * @param $limit        integer     max number of line to retrive (0 for all) (default 0)
    * @param $sqlfilter    string      to add an SQL filter (default '')
    * @return array of levels
   **/
   static function getAllForLevel($ID, $start=0, $limit=0, $sqlfilter='') {
      global $DB;

      $query = "SELECT *
                FROM `" . getTableForItemType(__CLASS__) . "`
                WHERE `items_id` = '$ID'";
      if ($sqlfilter) {
         $query .= "AND ($sqlfilter) ";
      }
      $query .= "ORDER BY `id` DESC";

      if ($limit) {
         $query .= " LIMIT ".intval($start)."," . intval($limit);
      }

      $raiselevels = array();
      foreach ($DB->request($query) as $data) {
         $raiselevels[$data['id']] = $data;
      }

      return $raiselevels;
   }

   /**
    * Show levels attached for a RaiseTemplate
    *
    * @param $template PluginRaisemanagerRaisetemplate object
   **/
   static function showForTemplate(PluginRaisemanagerRaisetemplate $template) {
      global $DB, $CFG_GLPI;

      $ID = $template->getField('id');
      if (!$template->can($ID, READ)) {
         return false;
      }

      $canedit = $template->canEdit($ID);
      $number  = self::countForTemplate($template);
      $used    = array();

      $out = "";
      $out .= "<div class='spaced'>";
      $out .= "<table class='tab_cadre_fixe'>";
      $out .= "<tr class='tab_bg_1'><th colspan='2'>";
      $out .= PluginRaisemanagerRaiselevel::getTypeName(2);
      $out .= "</th></tr></table></div>";
      $out .= "<div class='spaced'>";

      if ($number) {

         if ($canedit) {
            $rand = mt_rand();
            echo $out; $out = "";
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams
               = array('num_displayed'
                         => $number,
                       'container'
                         => 'mass'.__CLASS__.$rand,
                       'rand' => $rand,
                       'specific_actions'
                         => array('purge' => _x('button', 'Delete permanently')));
            Html::showMassiveActions($massiveactionparams);
         }

         $out .= "<table class='tab_cadre_fixehov'>";
         $header_begin  = "<tr>";
         $header_top    = '';
         $header_bottom = '';
         $header_end    = '';
         if ($canedit) {
            $header_begin  .= "<th width='10'>";
            $header_top    .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            $header_bottom .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            $header_end    .= "</th>";
         }
         $header_end .= "<th>".__('Name')."</th>";
         $header_end .= "<th>".__('Notification')."</th>";
         $header_end .= "<th>".__('Is Multiple', 'raisemanager')."</th>";
         $header_end .= "<th>".__('Send after', 'raisemanager')."</th>";
         $header_end .= "<th>".__('Trigger', 'raisemanager')."</th>";
         $header_end .= "</tr>\n";
         $out.= $header_begin.$header_top.$header_end;

         foreach (self::getAllForTemplate($ID) as $data) {

            $used[] = $data['items_id'];

            $out .= "<tr class='tab_bg_2'>";
            if ($canedit) {
               $out .= "<td width='10'>";
               echo $out; $out = "";
               Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
               $out .= "</td>";
            }

            $item = new $data['itemtype'];
            $item->getFromDB($data['items_id']);

            $out .= "<td width='40%' class='center'>";
            $out .= $item->getLink();
            $out .= "</td>";
            $out .= "<td class='center'>";
            $out .= Dropdown::getDropdownName(getTableForItemType('Notification'),
                                 $item->getField('notifications_id'));
            $out .= "</td>";
            $out .= "<td class='center'>";
            $out .= ($item->getField('trigger_is_multiple')) ? __('Yes') : __('No');
            $out .= "</td>";
            $out .= "<td class='center'>";
            $out .= $item->getField('send_value');
            $out .= " ";
            $out .= $item->getDurationType($item->getField('send_unit'));
            $out .= "</td>";
            $out .= "<td class='center'>";
            $out .= $item->getField('trigger_value');
            $out .= " ";
            $out .= $item->getDurationType($item->getField('trigger_unit'));
            $out .= "</td></tr>";
         }

         $out .= $header_begin.$header_bottom.$header_end;
         $out .= "</table>\n";

         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            echo $out; $out = "";
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
      } else {
         $out .= "<p class='center b'>".__('No level was linked', 'raisemanager')."</p>";
      }
      $out .= "</div>\n";

      if ($canedit) {
         $rand = mt_rand();
         $out .= "<div class='firstbloc'>";
         $out .= "<form name='raiseleveltemplate_form$rand' id='raiseleveltemplate_form$rand' method='post' action='";
         $out .= Toolbox::getItemTypeFormURL(__CLASS__)."'>";
         $out .= "<table class='tab_cadre_fixe'>";
         $out .= "<tr class='tab_bg_1'>";
         $out .= "<th>" . __('Add a level', 'raisemanager') . "</th>";
         $out .= "<th><input type='hidden' name='templates_id' value='$ID'>";
         $out .= "<input type='hidden' name='itemtype' value='PluginRaisemanagerRaiselevel'>";
         echo $out; $out = "";
         PluginRaisemanagerRaiselevel::dropdown(['name' => 'items_id',
                                                 'used' => $used]);
         $out .= "</th><th class='tab_bg_2 right'>";
         $out .= "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         $out .= "</th></tr>";
         $out .= "</table>";
         echo $out; $out = "";
         Html::closeForm();
         $out .= "</div>";
      }
      echo $out;
   }
   /**
    * Show templates attached for a RaiseLevel
    *
    * @param $level PluginRaisemanagerRaiselevel object
   **/
   static function showForLevel(PluginRaisemanagerRaiselevel $level) {
      global $DB, $CFG_GLPI;

      $ID = $level->getField('id');
      if (!$level->can($ID, READ)) {
         return false;
      }

      $canedit = $level->canEdit($ID);
      $number  = self::countForLevel($level);
      $used    = array();

      $out = "";
      $out .= "<div class='spaced'>";
      $out .= "<table class='tab_cadre_fixe'>";
      $out .= "<tr class='tab_bg_1'><th colspan='2'>";
      $out .= PluginRaisemanagerRaisetemplate::getTypeName(2);
      $out .= "</th></tr></table></div>";
      $out .= "<div class='spaced'>";

      if ($number) {

         if ($canedit) {
            $rand = mt_rand();
            echo $out; $out = "";
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams
               = array('num_displayed'
                         => $number,
                       'container'
                         => 'mass'.__CLASS__.$rand,
                       'rand' => $rand,
                       'specific_actions'
                         => array('purge' => _x('button', 'Delete permanently')));
            Html::showMassiveActions($massiveactionparams);
         }

         $out .= "<table class='tab_cadre_fixehov'>";
         $header_begin  = "<tr>";
         $header_top    = '';
         $header_bottom = '';
         $header_end    = '';
         if ($canedit) {
            $header_begin  .= "<th width='10'>";
            $header_top    .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            $header_bottom .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            $header_end    .= "</th>";
         }
         $header_end .= "<th>".__('Name')."</th>";
         $header_end .= "<th>".__('Calendar')."</th>";
         $header_end .= "<th>".__('Subtypes', 'raisemanager')."</th>";
         $header_end .= "</tr>\n";
         $out.= $header_begin.$header_top.$header_end;

         foreach (self::getAllForLevel($ID) as $data) {

            $used[] = $data['templates_id'];

            $out .= "<tr class='tab_bg_2'>";
            if ($canedit) {
               $out .= "<td width='10'>";
               echo $out; $out = "";
               Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
               $out .= "</td>";
            }

            $item = new PluginRaisemanagerRaisetemplate();
            $item->getFromDB($data['templates_id']);

            $out .= "<td width='40%' class='center'>";
            $out .= $item->getLink();
            $out .= "</td>";
            $out .= "<td class='center'>";
            $out .= Dropdown::getDropdownName(getTableForItemType('Calendar'),
                                                $item->getField('calendars_id'));
            $out .= "</td>";
            $out .= "<td class='center'>";
            $out .= $item->getField('itemtypes');
            $out .= "</td></tr>";
         }

         $out .= $header_begin.$header_bottom.$header_end;
         $out .= "</table>\n";

         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            echo $out; $out = "";
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
      } else {
         $out .= "<p class='center b'>".__('No template was linked', 'raisemanager')."</p>";
      }
      $out .= "</div>\n";

      if ($canedit) {
         $rand = mt_rand();
         $out .= "<div class='firstbloc'>";
         $out .= "<form name='raiseleveltemplate_form$rand' id='raiseleveltemplate_form$rand' method='post' action='";
         $out .= Toolbox::getItemTypeFormURL(__CLASS__)."'>";
         $out .= "<table class='tab_cadre_fixe'>";
         $out .= "<tr class='tab_bg_1'>";
         $out .= "<th>" . __('Add a template', 'raisemanager') . "</th>";
         $out .= "<th><input type='hidden' name='items_id' value='$ID'>";
         $out .= "<input type='hidden' name='itemtype' value='PluginRaisemanagerRaiselevel'>";
         echo $out; $out = "";
         PluginRaisemanagerRaisetemplate::dropdown(['name' => 'templates_id',
                                                    'used' => $used]);
         $out .= "</th><th class='tab_bg_2 right'>";
         $out .= "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         $out .= "</th></tr>";
         $out .= "</table>";
         echo $out; $out = "";
         Html::closeForm();
         $out .= "</div>";
      }
      echo $out;
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
                    `items_id` int(11) NOT NULL DEFAULT '0' COMMENT 'RELATION to various table, according to itemtype (id)',
                    `templates_id` int(11) NOT NULL DEFAULT '0',
                    `itemtype` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `templates_id` (`templates_id`),
                    KEY `item` (`itemtype`,`items_id`)
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

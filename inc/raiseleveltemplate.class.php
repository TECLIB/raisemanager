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

class PluginRaisemanagerRaiseLevelTemplate extends CommonDBTM {

   static public $itemtype_1 = 'PluginRaisemanagerRaiseTemplate';
   static public $items_id_1 = 'templates_id';
   static public $itemtype_2 = 'itemtype';
   static public $items_id_2 = 'items_id';

   static protected $linkableClasses = array('PluginRaisemanagerRaiseLevel');


   static function getTypeName($nb=0) {
      global $LANG;
      return __s('Elements');
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

   static function countForItem($id) {
      return countElementsInTable(getTableForItemType(__CLASS__), "`templates_id`='$id'");
   }

   /**
    * Count the number of relations having the itemtype of $item
    *
    * @param CommonDBTM $item Item whose relations to raisetemplates shall be counted
    * @return integer count of relations between the item and raisetemplates
    */
   static function countForItemByItemtype(CommonDBTM $item) {
      $id = $item->getField('id');
      $itemtype = $item->getType();

      return countElementsInTable(getTableForItemType(__CLASS__), "`items_id`='$id' AND `itemtype`='$itemtype'");
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

   static function getClasses() {
      return self::$linkableClasses;
   }

   /**
    * Declare a new itemtype to be linkable to a raise template
    */
   static function registerItemtype($itemtype) {
      if (!in_array($itemtype, self::$linkableClasses)) {
         array_push(self::$linkableClasses, $itemtype);
         Plugin::registerClass('PluginRaisemanagerRaiseLevelTemplate',
               array('addtabon' => $itemtype));
      }
   }

   static function showForTemplate(PluginRaisemanagerRaiseTemplate $template) {
      global $DB, $LANG;

      if (!$template->canView()) {
         return false;
      }
      $results = getAllDatasFromTable(getTableForItemType(__CLASS__),
                                     "`templates_id` = '".$template->getID()."'");
      echo "<div class='spaced'>";
      echo "<form id='items' name='items' method='post' action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr><th colspan='6'>".__("RaiseLevels", 'raisemanager')."</th></tr>";
      if (!empty($results)) {
         echo "<tr><th></th>";
         echo "<th>".__s("Type")."</th>";
         echo "<th>".__s("Name")."</th>";
         echo "</tr>";
         foreach ($results as $data) {
            $item = new $data['itemtype'];
            $item->getFromDB($data['items_id']);
            echo "<tr>";
            echo "<td>";
            if (PluginRaisemanagerRaiseTemplate::canUpdate()) {
               echo "<input type='checkbox' name='todelete[".$data['id']."]'>";
            }
            echo "</td>";
            echo "<td>";
            echo call_user_func(array($data['itemtype'], 'getTypeName'));
            echo "</td>";
            echo "<td>";
            echo $item->getLink();
            echo "</td>";
            echo "</tr>";
         }
      }

      if (PluginRaisemanagerRaiseTemplate::canUpdate()) {
         echo "<tr class='tab_bg_1'><td colspan='4' class='center'>";
         if (empty($results)) {
            echo "<input type='hidden' name='templates_id' value='".$template->getID()."'>";
            // TODO : Dropdown::showAllItems is deprecated, use Dropdown::showSelectItemFromItemtypes instead
            Dropdown::showAllItems("items_id", 0, 0, $template->fields['entities_id'], self::getClasses());
            echo "</td>";
            echo "<td colspan='2' class='center' class='tab_bg_2'>";
            echo "<input type='submit' name='additem' value=\""._sx('button', 'Add')."\" class='submit'>";
            echo "</td></tr>";
         }

         if (!empty($results)) {
            Html::openArrowMassives('items', true);
            Html::closeArrowMassives(array('delete_items' => _sx('button', 'Disconnect')));
         }
      }
      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }

   static function showForItem(CommonDBTM $item) {
      global $DB, $LANG;

      if (!$item->canView()) {
         return false;
      }

      $results = getAllDatasFromTable(getTableForItemType(__CLASS__),
                                     "`items_id` = '".$item->getID()."' AND `itemtype`='".get_class($item)."'");
      echo "<div class='spaced'>";
      echo "<form id='items' name='items' method='post' action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr><th colspan='6'>".__s('Associated item')."</th></tr>";
      if (!empty($results)) {
         echo "<tr><th></th>";
         echo "<th>".__s('Entity')."</th>";
         echo "<th>".__s('Name')."</th>";
         echo "<th>".__s('Itemtypes')."</th>";
         echo "</tr>";
         foreach ($results as $data) {
            $tmp = new PluginRaisemanagerRaiseTemplate();
            $tmp->getFromDB($data['templates_id']);
            echo "<tr>";
            echo "<td>";
            if (PluginRaisemanagerRaiseTemplate::canDelete()) {
               echo "<input type='checkbox' name='todelete[".$data['id']."]'>";
            }
            echo "</td>";
            echo "<td>";
            echo Dropdown::getDropdownName('glpi_entities', $tmp->fields['entities_id']);
            echo "</td>";
            echo "<td>";
            echo $tmp->getLink();
            echo "</td>";
            echo "<td>";
            echo $tmp->fields['itemtypes'];
            echo "</td>";
            echo "</tr>";
         }
      }

      if (PluginRaisemanagerRaiseTemplate::canUpdate()) {
         echo "<tr class='tab_bg_1'><td colspan='4' class='center'>";
         echo "<input type='hidden' name='items_id' value='".$item->getID()."'>";
         echo "<input type='hidden' name='itemtype' value='".$item->getType()."'>";
         $used = array();
         $query = "SELECT `id`
                   FROM `".getTableForItemType('PluginRaisemanagerRaiseTemplate')."`
                   WHERE `id` IN (SELECT `templates_id`
                                   FROM `".getTableForItemType(__CLASS__)."`AND itemtype = '".$item->getType()."' AND items_id = '".$item->getID()."')";
         foreach ($DB->request($query) as $use) {
            $used[] = $use['id'];
         }
         Dropdown::show('PluginRaisemanagerRaiseTemplate',
                        array ('name' => "templates_id",
                               'entity' => $item->fields['entities_id'], 'used' => $used));
         echo "</td>";
         echo "<td colspan='2' class='center' class='tab_bg_2'>";
         echo "<input type='submit' name='additem' value=\""._sx('button', 'Save')."\" class='submit'>";
         echo "</td></tr>";

         if (!empty($results)) {
            Html::openArrowMassives('items', true);
            Html::closeArrowMassives(array ('delete_items' => _sx('button', 'Disconnect')));
         }
      }
      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $CFG_GLPI;

      if (PluginRaisemanagerRaiseTemplate::canView()) {
         switch ($item->getType()) {
            case 'PluginRaisemanagerRaiseTemplate' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(_n('Associated item', 'Associated items', 2), self::countForTemplate($item));
               }
               return _n('Associated item', 'Associated items', 2);
            default :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(PluginRaisemanagerRaiseTemplate::getTypeName(2), self::countForItemByItemtype($item));
               }
               return _n('RaiseTemplate', 'Raise templates', 2);
         }
      }
      return '';
   }

   /**
    *
    * Count the number of associated items for a raisetemplate item
    *
    * @param $item   RaiseTemplate object
    **/
   static function countForTemplate(PluginRaisemanagerRaiseTemplate $item) {

      $restrict = "`".getTableForItemType(__CLASS__)."`.`templates_id` = '".$item->getField('id')."'";

      return countElementsInTable(array(getTableForItemType(__CLASS__)), $restrict);
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      if (in_array(get_class($item), PluginRaisemanagerRaiseLevelTemplate::getClasses())) {
         self::showForItem($item);
      } else if (get_class($item) == 'PluginRaisemanagerRaiseTemplate') {
         self::showForTemplate($item);
      }
      return true;
   }

   public static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");
         $query ="CREATE TABLE IF NOT EXISTS `".getTableForItemType(__CLASS__)."` (
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

      return true;
   }

   public static function uninstall() {
      global $DB;

      $DB->query("DROP TABLE IF EXISTS `" . getTableForItemType(__CLASS__) . "`") or die ($DB->error());
   }
}

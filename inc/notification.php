<?php

class PluginRaisemanagerNotification extends CommonDBTM {

   /**
    * Give cron information
    *
    * @param $name : automatic action's name
    *
    * @return arrray of information
   **/
   static function cronInfo($name) {

      return array('description' => __('Send raises to techs'),
                         'parameter'   => __('None'));
   }

   /**
    * Cron action on raises : send raises as notifications to techs
    *
    * @param CronTask $task for log, if NULL display (default NULL)
    *
    * @return integer 1 if an action was done, 0 if not
   **/
   static function cronSendRaises($task=NULL) {
      global $DB, $CFG_GLPI;
      $cron_status = 0;

      $sRaiseTemplateQuery = 'SELECT tmpl.name, tmpl.itemtypes, lvl.id, lvl.name AS levelname, lvl.send_value, lvl.send_unit, lvl.trigger_value, lvl.trigger_unit, lvl.trigger_is_multiple 
      FROM glpi_plugin_raisemanager_raiselevels AS lvl 
      LEFT JOIN glpi_plugin_raisemanager_raiseleveltemplates AS lvltmpl ON lvltmpl.items_id = lvl.id AND itemtype = \'PluginRaisemanagerRaiseLevel\' 
      LEFT JOIN glpi_plugin_raisemanager_raisetemplates AS tmpl ON lvltmpl.templates_id = tmpl.id';

      $oQuery = $DB->request($sRaiseTemplateQuery);
      $aQueries = array();

      foreach ($oQuery as $id => $row) {
        $iCurrentLevelID = $row['id'];
        $aItemTypes = explode(', ', $row['itemtypes']);
        $sTriggerAfter = 'INTERVAL '.$row['send_value'].' '.$row['send_unit'];

        $task->log('Itemtypes in scope : '.$row['itemtypes'].' for RaiseLevel \''.$row['levelname'].'\'');

        switch ($row['trigger_is_multiple']) {
          case '1':
            $sRepeatAfter = 'INTERVAL '.$row['trigger_value'].' '.$row['trigger_unit'];
            $sQuery = '';
            break;
          
          default:
            foreach ($aItemTypes as $k => $sItemType) {
              $aQueries[$sItemType] = 'SELECT T.id FROM '.getTableForItemType($sItemType).' AS T LEFT JOIN '.getTableForItemType('PluginRaisemanagerRaiseLevelTemplate').' AS lvltmpl ON T.id = lvltmpl.items_id AND lvltmpl.itemtype = "'.$sItemType.'" WHERE lvltmpl.items_id IS NULL AND status < 5 AND due_date != ""';
            }
            break;
        }        
      }

      $task->log(print_r($aQueries, true));

      foreach ($aQueries as $sItemType => $sQuery) {
        $oCurrentQuery = $DB->request($sQuery);

        $task->log(__('Looping for items with type '.$sItemType.' !', 'raisemanager'));

        foreach ($oCurrentQuery as $l => $aResultSet) {
         $oCurrentObject = new $sItemType();
         $oCurrentObject->getFromDB($aResultSet['id']);

         if (NotificationEvent::raiseEvent('plugin_raisemanager', $oCurrentObject, array())) {
          $task->log(__($sItemType.'::'.$aResultSet['id'].' triggered !', 'raisemanager'));
          //$raisetemplate = new PluginRaisemanagerRaiseTemplate();
          //$raisetemplate->add($_POST);
         }
        }
      }

      return $cron_status;
   }
}
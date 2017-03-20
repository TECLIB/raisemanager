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

      return array('description' => __('RaiseNotification', 'raisemanager'),
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

      $sRaiseTemplateQuery = '
      SELECT GROUP_CONCAT(DISTINCT tmplitilcat.itilcategories_id ORDER BY tmplitilcat.itilcategories_id DESC SEPARATOR \', \') AS itilcategories, tmpl.name, tmpl.itemtypes, lvl.id, lvl.name AS levelname, lvl.send_value, lvl.send_total_value, lvl.send_unit, lvl.trigger_value, lvl.trigger_unit, lvl.trigger_is_multiple 
      FROM glpi_plugin_raisemanager_raiselevels AS lvl 
      LEFT JOIN glpi_plugin_raisemanager_raiseleveltemplates AS lvltmpl ON lvltmpl.items_id = lvl.id AND itemtype = \'PluginRaisemanagerRaiseLevel\' 
      LEFT JOIN glpi_plugin_raisemanager_raisetemplates AS tmpl ON lvltmpl.templates_id = tmpl.id 
      LEFT JOIN glpi_plugin_raisemanager_categorytemplates AS tmplitilcat ON tmplitilcat.templates_id = tmpl.id 
      LEFT JOIN glpi_calendars AS cal ON tmpl.calendars_id = cal.id
      LEFT JOIN glpi_calendarsegments AS calseg ON calseg.calendars_id = cal.id
      WHERE calseg.day = DAYOFWEEK(NOW()) AND DATE_FORMAT(NOW(), \'%H:%i:%s\') BETWEEN calseg.begin AND calseg.end 
      GROUP BY lvl.id ORDER BY send_total_value DESC';

      $aQueries = array();

      foreach ($DB->request($sRaiseTemplateQuery) as $id => $row) {
        $iCurrentLevelID = $row['id'];
        $iCurrentLevelValue = $row['send_total_value'];
        $aItemTypes = explode(', ', $row['itemtypes']);
        $sTriggerAfter = 'INTERVAL '.$row['send_value'].' '.$row['send_unit'];
        $sItilCategories = 'AND itilcategories_id IN ('.$row['itilcategories'].')';

        $task->log('Itemtypes in scope : '.$row['itemtypes'].' for RaiseLevel \''.$row['levelname'].'\'');

        switch ($row['trigger_is_multiple']) {
          case '1':
            foreach ($aItemTypes as $k => $sItemType) {
              $sRepeatAfter = 'INTERVAL '.$row['trigger_value'].' '.$row['trigger_unit'];

              $aQueries[$iCurrentLevelID.':'.$iCurrentLevelValue.':'.$sItemType] = 'SELECT T.id FROM '.getTableForItemType($sItemType).' AS T 
              LEFT JOIN '.getTableForItemType('PluginRaisemanagerRaiseLog').' AS log ON T.id = log.items_id AND log.itemtype = "'.$sItemType.'" AND log.levels_id = '.$iCurrentLevelID.' WHERE status < 5 AND due_date != "" AND ((log.items_id IS NULL AND NOW() > DATE_ADD(date, '.$sTriggerAfter.')) OR (NOW() > DATE_ADD(log.date_last_sent, '.$sRepeatAfter.') AND log.items_id IS NOT NULL)) '.$sItilCategories;
            }
            break;
          
          default:
            foreach ($aItemTypes as $k => $sItemType) {
              $aQueries[$iCurrentLevelID.':'.$iCurrentLevelValue.':'.$sItemType] = 'SELECT T.id FROM '.getTableForItemType($sItemType).' AS T 
              LEFT JOIN '.getTableForItemType('PluginRaisemanagerRaiseLog').' AS log ON T.id = log.items_id AND log.itemtype = "'.$sItemType.'" AND levels_id = '.$iCurrentLevelID.' 
              WHERE log.items_id IS NULL AND status < 5 AND due_date != "" AND NOW() > DATE_ADD(date, '.$sTriggerAfter.') '.$sItilCategories;
            }
            break;
        }        
      }

      $aAlreadyNotified = array();
      foreach ($aQueries as $sReference => $sQuery) {
          //$task->log('Requête SQL compilée : '.base64_encode($sQuery));
          $oCurrentQuery = $DB->request($sQuery);
  
          $aReferences = explode(':', $sReference);

          $iLevelID = $aReferences[0];
          $iLevelValue = $aReferences[1];
          $sItemType = $aReferences[2];
  
          $task->log(__('Looping for items with type '.$sItemType.' !', 'raisemanager'));
  
          foreach ($oCurrentQuery as $l => $aResultSet) {
           $oCurrentObject = new $sItemType();
           $oCurrentObject->getFromDB($aResultSet['id']);

           $iSuperiorLevelsCheck = countElementsInTable(
            getTableForItemType('PluginRaisemanagerRaiseLog'), 'itemtype = "'.$sItemType.'" 
            AND items_id = '.$aResultSet['id'].' 
            AND level_value > '.$iLevelValue);

           //$task->log($sItemType.' #'.$aResultSet['id']. ' : '.$iSuperiorLevelsCheck);

           if ($iSuperiorLevelsCheck > 0) {
            continue;
           }
  
           if (isset($aAlreadyNotified[$aResultSet['id']])) {
            $task->log($sItemType.' #'.$aResultSet['id'].' already notified, next');
            continue;
           }

           $oRaiseLevel = new PluginRaisemanagerRaiseLevel();
           $oRaiseLevel->getFromDB($iLevelID);
  
           $task->log('Notification sent : #'.$oRaiseLevel->fields['notifications_id']);

           $aOptions = array('notifications_id' => $oRaiseLevel->fields['notifications_id']);

           if (PluginRaisemanagerNotificationEvent::raiseEvent('plugin_raisemanager', $oCurrentObject, $aOptions)) {
            $aAlreadyNotified[$aResultSet['id']] = true;
  
            $task->log($sItemType.'::'.$aResultSet['id'].' triggered !');
            $oRaiseLog = new PluginRaisemanagerRaiseLog();
            $oRaiseLog->deleteByCriteria(array('items_id' => $aResultSet['id'], 'itemtype' => $sItemType, 'levels_id' => $iLevelID));
  
            $aData = array();
            $aData['items_id'] = $aResultSet['id'];
            $aData['levels_id'] = $iLevelID;
            $aData['level_value'] = $iLevelValue;
            $aData['itemtype'] = $sItemType;
            $aData['date_last_sent'] = date("Y-m-d H:i:s");
            $oRaiseLog->add($aData);
           }
        }
      }

      return $cron_status;
   }
}
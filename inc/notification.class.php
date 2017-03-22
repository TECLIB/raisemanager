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

      // select each raise level linked to raise template with an active calendar.
      // if current time is out of scope of the calendar, these templates are not selected
      $sRaiseTemplateQuery = '
      SELECT GROUP_CONCAT(DISTINCT tmplitilcat.itilcategories_id ORDER BY tmplitilcat.itilcategories_id DESC SEPARATOR \', \') AS itilcategories, tmpl.name, tmpl.itemtypes, lvl.id, lvl.name AS levelname, tmpl.entities_id, tmpl.is_recursive, lvl.send_value, lvl.send_total_value, lvl.send_unit, lvl.trigger_value, lvl.trigger_unit, lvl.trigger_is_multiple 
      FROM glpi_plugin_raisemanager_raiselevels AS lvl 
      LEFT JOIN glpi_plugin_raisemanager_raiseleveltemplates AS lvltmpl ON lvltmpl.items_id = lvl.id AND itemtype = \'PluginRaisemanagerRaiselevel\' 
      LEFT JOIN glpi_plugin_raisemanager_raisetemplates AS tmpl ON lvltmpl.templates_id = tmpl.id 
      LEFT JOIN glpi_plugin_raisemanager_categorytemplates AS tmplitilcat ON tmplitilcat.templates_id = tmpl.id 
      LEFT JOIN glpi_calendars AS cal ON tmpl.calendars_id = cal.id
      LEFT JOIN glpi_calendarsegments AS calseg ON calseg.calendars_id = cal.id
      WHERE calseg.day = DAYOFWEEK(NOW()) AND DATE_FORMAT(NOW(), \'%H:%i:%s\') BETWEEN calseg.begin AND calseg.end  
      GROUP BY lvl.id ORDER BY send_total_value DESC';

      $aQueries = array();

      foreach ($DB->request($sRaiseTemplateQuery) as $id => $row) {
         // get ID of raise level and value, which is the total of secondes before first raise
         $iCurrentLevelID = $row['id'];
         $iCurrentLevelValue = $row['send_total_value'];

         // get itemtypes (instanceof CommonITILObject) and prepare condition for itil categories and date_add functions
         $aItemTypes = explode(', ', $row['itemtypes']);
         $sTriggerAfter = 'INTERVAL '.$row['send_value'].' '.$row['send_unit'];
         $sItilCategories = 'AND itilcategories_id IN ('.$row['itilcategories'].')';

         $task->log('itemtypes in scope : '.$row['itemtypes'].' for RaiseLevel \''.$row['levelname'].'\'');

         switch ($row['trigger_is_multiple']) {
            case '1':
               // Multiple raises
               foreach ($aItemTypes as $k => $sItemType) {
                  // Prepare queries for date_date on date_last_sent
                  $sRepeatAfter = 'INTERVAL '.$row['trigger_value'].' '.$row['trigger_unit'];

                  // Retrieve imtemtypes which passed minimum triggering time and has never been notified,
                  // or itemtypes which has been already notified but which last notify time is greater than frequency
                  $aQueries[$iCurrentLevelID.':'.$iCurrentLevelValue.':'.$sItemType] = 'SELECT T.id FROM '.getTableForItemType($sItemType).' AS T 
                 LEFT JOIN '.getTableForItemType('PluginRaisemanagerRaiselog').' AS log ON T.id = log.items_id AND log.itemtype = "'.$sItemType.'" AND log.levels_id = '.$iCurrentLevelID.' WHERE status < 5 AND due_date != "" AND ((log.items_id IS NULL AND NOW() > DATE_ADD(date, '.$sTriggerAfter.')) OR (NOW() > DATE_ADD(log.date_last_sent, '.$sRepeatAfter.') AND log.items_id IS NOT NULL)) '.getEntitiesRestrictRequest("AND", 'T', 'entities_id', $row['entities_id'], $row['is_recursive']).$sItilCategories;
               }
             break;

            default:
               // One time raise
               foreach ($aItemTypes as $k => $sItemType) {
                  // Retrieve itemtypes concerned and which hasn't been notified yet
                  $aQueries[$iCurrentLevelID.':'.$iCurrentLevelValue.':'.$sItemType] = 'SELECT T.id FROM '.getTableForItemType($sItemType).' AS T 
                 LEFT JOIN '.getTableForItemType('PluginRaisemanagerRaiselog').' AS log ON T.id = log.items_id AND log.itemtype = "'.$sItemType.'" AND levels_id = '.$iCurrentLevelID.' 
                 WHERE log.items_id IS NULL AND status < 5 AND due_date != "" AND NOW() > DATE_ADD(date, '.$sTriggerAfter.') '.getEntitiesRestrictRequest("AND", 'T', 'entities_id', $row['entities_id'], $row['is_recursive']).$sItilCategories;
               }
             break;
         }
      }

      $aAlreadyNotified = array();

      // Pass through each query, each query contain objects to notify
      foreach ($aQueries as $sReference => $sQuery) {
          //$task->log('Requête SQL compilée : '.base64_encode($sQuery));
          $oCurrentQuery = $DB->request($sQuery);

          // Retreive references in array keys and raise level informations from it
          $aReferences = explode(':', $sReference);
          $iLevelID = $aReferences[0];
          $iLevelValue = $aReferences[1];
          $sItemType = $aReferences[2];

          $task->log(__('Looping for items with type '.$sItemType.' !', 'raisemanager'));

         foreach ($oCurrentQuery as $l => $aResultSet) {
            $oCurrentObject = new $sItemType();
            $oCurrentObject->getFromDB($aResultSet['id']);

            // Check if current object has already been notified by greater raise level, if so, don't notify it
            $iSuperiorLevelsCheck = countElementsInTable(
            getTableForItemType('PluginRaisemanagerRaiselog'), 'itemtype = "'.$sItemType.'" 
            AND items_id = '.$aResultSet['id'].' 
            AND level_value > '.$iLevelValue);

            if ($iSuperiorLevelsCheck > 0) {
               continue;
            }

            // initially check if a raiselevel has already triggered a notification for this object in the current loop
            // may has to be deleted, previous check seems to have done the same job and more
            if (isset($aAlreadyNotified[$aResultSet['id']])) {
               $task->log($sItemType.' #'.$aResultSet['id'].' already notified, next');
               continue;
            }

            // Retrieve raiselevel to pass notifications_id toe the raiseevent function
            // raiseEvent function is ovveridden in PluginRaisemanagerNotificationevent class
            $oRaiseLevel = new PluginRaisemanagerRaiselevel();
            $oRaiseLevel->getFromDB($iLevelID);
            $aOptions = array('notifications_id' => $oRaiseLevel->fields['notifications_id']);

            if (PluginRaisemanagerNotificationevent::raiseEvent('plugin_raisemanager', $oCurrentObject, $aOptions)) {
               $aAlreadyNotified[$aResultSet['id']] = true;

               $task->log($sItemType.'::'.$aResultSet['id'].' notified with Notification #'.$oRaiseLevel->fields['notifications_id']);

               // Delete previous history of notificaztion with this level
               $oRaiseLog = new PluginRaisemanagerRaiselog();
               $oRaiseLog->deleteByCriteria(array('items_id' => $aResultSet['id'], 'itemtype' => $sItemType, 'levels_id' => $iLevelID));

               // Insert new entry in raise log history
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

   /**
    * Install all necessary table for the plugin
    *
    * @return boolean True if success
    */
   static function install(Migration $migration) {

      CronTask::Register('PluginRaisemanagerNotification',
                         'SendRaises',
                         MINUTE_TIMESTAMP);
   }
}
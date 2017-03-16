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

      switch ($name) {
         case 'raisemanager' :
            return array('description' => __('Send raises to techs'),
                         'parameter'   => __('None'));
      }
      return array();
   }

   /**
    * Cron action on raises : send raises as notifications to techs
    *
    * @param CronTask $task for log, if NULL display (default NULL)
    *
    * @return integer 1 if an action was done, 0 if not
   **/
   static function SendNotifications($task=NULL) {
      global $DB, $CFG_GLPI;

      $cron_status = 0;

      


      return $cron_status;
   }
}
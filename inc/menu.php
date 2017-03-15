<?php

class PluginRaisemanagerMenu extends CommonGLPI {

   public static function getTypeName($nb = 0) {
      return __("Raises", "raisemanager");
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu          = array();
      $menu['title'] = self::getTypeName(2);
      $menu['page']  = self::getSearchURL(false);

      if (PluginRaiseManagerRaiseTemplate::canView()) {
         $menu['options']['raisemanager']['title']                = PluginRaisemanagerRaiseTemplate::getTypeName(2);
         $menu['options']['raisemanager']['page']                 = PluginRaisemanagerRaiseTemplate::getSearchURL(false);
         $menu['options']['raisemanager']['links']['add']         = PluginRaisemanagerRaiseTemplate::getFormURL(false);
      }

      return $menu;
   }
   
   function install(){
      return true;
   }

}

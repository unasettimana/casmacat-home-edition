<?php

class indexController extends viewcontroller {
    private $cat_server_online = 0;
    private $mt_server_online = 0;
    private $msg = '';

    public function __construct() {
        parent::__construct();
       	parent::makeTemplate("index.html");
    }
    
    public function doAction(){
      global $current_engine_is_thot, $thot_itp_conf;

      if (array_key_exists("do",$_GET)) {
        if ($_GET['do'] == 'start-mt-server') {
          exec('scripts/update-language-setting-in-web-server.perl');
          exec('scripts/start-mt-server.perl');
          usleep(2000000);
        }
        if ($_GET['do'] == 'start-cat-server') {
          exec('scripts/itp-server.sh stop');
          exec('scripts/stop-cat-server.sh');
          if ($current_engine_is_thot) {
            exec('scripts/itp-server.sh $thot_itp_conf 9999');
          }
          else {
            exec('scripts/start-cat-server.sh');
          }
          usleep(2000000);
        }
        if ($_GET['do'] == 'reset-server') {
          exec('scripts/itp-server.sh stop');
          exec('scripts/stop-cat-server.sh');
          exec('scripts/update-language-setting-in-web-server.perl');
          exec('scripts/start-mt-server.perl');
          if ($current_engine_is_thot) {
            exec('scripts/itp-server.sh $thot_itp_conf 9999');
          }
          else {
            exec('scripts/start-cat-server.sh');
          }
          usleep(2000000);
        }
        if ($_GET['do'] == 'update') {
          exec('/opt/casmacat/install/update.sh');
        }
      }

      $ret = array();
      exec('/bin/ps -ef',$ret);
      foreach($ret as $line) {
        if (strpos($line,"/opt/casmacat/cat-server/cat-server.py --port 9999") !== false) {
          $this->cat_server_online++;
        }
        if (strpos($line,"/opt/moses/bin/mosesserver") !== false) {
          $this->mt_server_online++;
        }
        if (strpos($line,"/opt/casmacat/mt-server/python_server/server.py") !== false) {
          $this->mt_server_online++;
        }
        if (strpos($line,"/opt/casmacat/itp-server/server/casmacat-server.py") !== false) {
          $this->mt_server_online = 2;
        }
      }
    }
    
    public function setTemplateVars() {
      global $ip,$current_engine_is_thot;
      if ($current_engine_is_thot) {
        $this->template->show_start_cat_server = ! $this->ol_server_online;
        $this->template->show_start_mt_server = ! $this->mt_server_online;
      }
      else {
        $this->template->show_start_cat_server = ! $this->cat_server_online;
        $this->template->show_start_mt_server = ($this->mt_server_online != 2);
      }
      $this->template->show_translate_document = 
        ($this->cat_server_online == 1 && $this->mt_server_online == 2);
      $this->template->url = "http://$ip:8000/";
      $this->template->url_list = "http://$ip:8000/?action=listDocuments";
      $this->template->msg = $this->msg;
    }
}

?>

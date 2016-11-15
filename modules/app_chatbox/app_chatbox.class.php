<?php
/**
* chat 
*
* chat
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.3 (wizard, 23:01:48 [Jan 30, 2007])
*/
//
//
class app_chatbox extends module {
/**
* chat
*
* Module class constructor
*
* @access private
*/
function app_chatbox() {
  $this->name="app_chatbox";
  $this->title="ChatBox";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
 $data=array();
 if (IsSet($this->id)) {
  $data["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $data["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $data["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $data["tab"]=$this->tab;
 }
 return parent::saveParams($data);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams($data = 1) {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}

/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['TAB']=$this->tab;

  $res=SQLSelect("SELECT * FROM users ORDER BY ID");
  $textdata ='
                            <div class=\"table-responsive\">
                                <table class=\"table\">
                                    <thead>
										<tr>
											<th>ID</th>
											<th>ИМЯ</th>
										</tr>
									</thead>';
  $total=count($res);
  for($i=0;$i<$total;$i++) {
	$textdata .= "					
									<tbody>
										<tr>
											<td>".$res[$i]['ID']."</td>
											<td>".$res[$i]['NAME']."</td>
										</tr>\n";
  }
  $textdata .= '
									</tbody>
								</table>
							</div>';
  $out['TXT_ADMIN']=$textdata;

  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}



/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
$this->getConfig();
$out['AVATAR']=$this->config['AVATAR'];
$out['COLOR']=$this->config['COLOR'];
global $session;
global $color;
global $avatar;
global $avatar_name;
   if ($avatar!='') {
    if ($out['AVATAR']!='') {
     @unlink(ROOT.'cms/avatars/'.$out['AVATAR']);
    }
    $out['AVATAR']='_'.$avatar_name;
    copy($avatar, ROOT.'cms/avatars/'.$out['AVATAR']);
   } 

if ($this->view_mode=='update_settings') {
   if ($avatar!='') {$this->config['AVATAR']=$out['AVATAR'];}
   $this->config['COLOR']=$color;
   $this->saveConfig();
   $this->redirect("?");
 }
 
 
if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1; 
 }




// echo $textdata;

 if ($this->data_source=='shouts' || $this->data_source=='') {
    if ($this->view_mode=='' || $this->view_mode=='search_shouts') {
	$this->search_shouts($out);
    }
    if ($this->view_mode=='delete_shouts') {
	$this->delete_shouts($this->id);
	$this->redirect("?");
    }
 }
}


/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 SQLExec("DELETE FROM shouts WHERE (TO_DAYS(NOW())-TO_DAYS(ADDED))>7");
 $this->admin($out);
}

/**
* shouts search
*
* @access public
*/
 function search_shouts(&$out) {
  require(DIR_MODULES.$this->name.'/chat_search.inc.php');
 }


/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($parent_name="") {
  parent::install($parent_name);
  $this->getConfig();
  if ($this->config['AVATAR']=="") {
	  $this->config['AVATAR'] = "_alice.png"
	  $this->saveConfig();
  }
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS shouts');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
shouts - chat
*/
  $data = <<<EOD
 shouts: ID int(10) unsigned NOT NULL auto_increment
 shouts: ROOM_ID int(10) NOT NULL DEFAULT '0'
 shouts: MEMBER_ID int(10) NOT NULL DEFAULT '0'
 shouts: MESSAGE varchar(255) NOT NULL DEFAULT ''
 shouts: IMPORTANCE int(10) NOT NULL DEFAULT '0'
 shouts: ADDED datetime
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDMwLCAyMDA3IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>

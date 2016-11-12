<?php
/**
* Title
*
* Description
*
* @access public
*/
 function saydym($ph, $level=0, $ding=1, $member_id=0) 
 {
        global $commandLine;
        global $voicemode;

if ($ding >= 5){$ding=1;};

DebMes('SAY FUNC: '.$ph);

        $rec = array();
        $rec['MESSAGE']   = $ph;
        $rec['ADDED']     = date('Y-m-d H:i:s');
        $rec['ROOM_ID']   = 0;
        $rec['MEMBER_ID'] = $member_id;
        $rec['SOURCE'] = $source;
  
        if ($level>0) $rec['IMPORTANCE']=$level;
        
        $rec['ID'] = SQLInsert('shouts', $rec);

        if (defined('SETTINGS_HOOK_BEFORE_SAY') && SETTINGS_HOOK_BEFORE_SAY!='') {
         eval(SETTINGS_HOOK_BEFORE_SAY);
        }
        if ($level >= (int)getGlobal('minMsgLevel'))
        { 

           if (!defined('SETTINGS_SPEAK_SIGNAL') || SETTINGS_SPEAK_SIGNAL=='1') {
              $passed=SQLSelectOne("SELECT (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(ADDED)) as PASSED FROM shouts WHERE ID!='".$rec['ID']."' ORDER BY ID DESC LIMIT 1");
              if ($passed['PASSED']>20) { // play intro-sound only if more than 30 seconds passed from the last one
                    if ($ding == 1){playSound('dingdong', 1, $level);}
                    if ($ding == 2){playSound('tone1', 1, $level);}
                    if ($ding == 3){playSound('tone2', 1, $level);}
                    if ($ding == 4){playSound('tone3', 1, $level);}
                }
           }
        }
       setGlobal('lastSayTime', time());
       setGlobal('lastSayMessage', $ph);
       processSubscriptions('SAY', array('level' => $level, 'message' => $ph, 'member_id' => $member_id, 'ignoreVoice'=>$ignoreVoice));

       if (!$noPatternMode)
       {
          include_once(DIR_MODULES . 'patterns/patterns.class.php');
          $pt = new patterns();
          $pt->checkAllPatterns($member_id);
       }

       if (defined('SETTINGS_HOOK_AFTER_SAY') && SETTINGS_HOOK_AFTER_SAY != '')
       {
            eval(SETTINGS_HOOK_AFTER_SAY);
       }

       $terminals=SQLSelect("SELECT NAME FROM terminals WHERE IS_ONLINE=1 AND MAJORDROID_API=1");
       $total=count($terminals);
       for($i=0;$i<$total;$i++) {
            sayTo($ph, $level, $terminals[$i]['NAME']);
       }
 }

?>
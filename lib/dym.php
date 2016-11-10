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
  
        if ($level>0) $rec['IMPORTANCE']=$level;
        
        $rec['ID'] = SQLInsert('shouts', $rec);

        if (defined('SETTINGS_HOOK_BEFORE_SAY') && SETTINGS_HOOK_BEFORE_SAY!='') {
         eval(SETTINGS_HOOK_BEFORE_SAY);
        }

        if ($level >= (int)getGlobal('minMsgLevel'))
        { 
                //$voicemode!='off' && 

           $lang='en';
           if (defined('SETTINGS_SITE_LANGUAGE')) {
                $lang=SETTINGS_SITE_LANGUAGE;
           }
           if (defined('SETTINGS_VOICE_LANGUAGE')) {
                $lang=SETTINGS_VOICE_LANGUAGE;
           }

           if (!defined('SETTINGS_TTS_GOOGLE') || SETTINGS_TTS_GOOGLE) {
                $google_file=GoogleTTS($ph, $lang);
           } else {
                $google_file=false;
           }

           if (!defined('SETTINGS_SPEAK_SIGNAL') || SETTINGS_SPEAK_SIGNAL=='1') {
              $passed=SQLSelectOne("SELECT (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(ADDED)) as PASSED FROM shouts WHERE ID!='".$rec['ID']."' ORDER BY ID DESC LIMIT 1");
              if ($passed['PASSED']>20) { // play intro-sound only if more than 30 seconds passed from the last one
            if ($ding == 1){playSound('dingdong', 1, $level);}
            if ($ding == 2){playSound('tone1', 1, $level);}
            if ($ding == 3){playSound('tone2', 1, $level);}
            if ($ding == 4){playSound('tone3', 1, $level);}
                  }
           }

           if ($google_file) {
                @touch($google_file);
                        playSound($google_file, 1, $level);
           } else {
                safe_exec('cscript '.DOC_ROOT.'/rc/sapi.js '.$ph, 1, $level);
           }
        }
        global $ignorePushover;
        if (defined('SETTINGS_PUSHOVER_USER_KEY') && SETTINGS_PUSHOVER_USER_KEY && !$ignorePushover) {
                include_once(ROOT.'lib/pushover/pushover.inc.php');
                if (defined('SETTINGS_PUSHOVER_LEVEL')){
                        if($level>=SETTINGS_PUSHOVER_LEVEL) {
                                postToPushover($ph);
                        }
                } elseif ($level>0) {
                        postToPushover($ph);
                }
        }

        global $ignorePushbullet;
        if (defined('SETTINGS_PUSHBULLET_KEY') && SETTINGS_PUSHBULLET_KEY && !$ignorePushbullet) {
                include_once(ROOT.'lib/pushbullet/pushbullet.inc.php');
                if (defined('SETTINGS_PUSHBULLET_PREFIX') && SETTINGS_PUSHBULLET_PREFIX) {
                 $prefix=SETTINGS_PUSHBULLET_PREFIX.' ';
                } else {
                 $prefix='';
                }

                if (defined('SETTINGS_PUSHBULLET_LEVEL')){
                        if($level>=SETTINGS_PUSHBULLET_LEVEL) {
                                postToPushbullet($prefix.$ph);
                        }
                } elseif ($level>0) {
                        postToPushbullet($prefix.$ph);
                }
        }

        global $ignoreGrowl;
        if (defined('SETTINGS_GROWL_ENABLE') && SETTINGS_GROWL_ENABLE && $level>=SETTINGS_GROWL_LEVEL && !$ignoreGrowl) {
         include_once(ROOT.'lib/growl/growl.gntp.php');
         $growl = new Growl(SETTINGS_GROWL_HOST, SETTINGS_GROWL_PASSWORD);
         $growl->setApplication('MajorDoMo','Notifications');
         //$growl->registerApplication('http://localhost/img/logo.png');
         $growl->notify($ph);
        }

        global $ignoreTwitter;
        if (defined('SETTINGS_TWITTER_CKEY') && SETTINGS_TWITTER_CKEY && !$ignoreTwitter) {
         postToTwitter($ph);
        }

        if (defined('SETTINGS_HOOK_AFTER_SAY') && SETTINGS_HOOK_AFTER_SAY!='') {
         eval(SETTINGS_HOOK_AFTER_SAY);
        }


 }

?>

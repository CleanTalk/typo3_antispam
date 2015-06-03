<?php
namespace TYPO3\CMS\Cleantalk\Hook;

class InitFEuser {

	public function hookPostData() {
		$conf=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cleantalk']);
		if(sizeof($_POST)>0&&$conf['customForms']==1)
		{
			$sender_email = null;
		    $message = '';
		    InitFEuser::cleantalkGetFields($sender_email,$message,$_POST);
		    if($sender_email!==null)
		    {
				$aMessage = array();
				$aMessage['type'] = 'comment';
				$aMessage['sender_email'] = $sender_email;
				$aMessage['sender_nickname'] = '';
				$aMessage['message_title'] = '';
				$aMessage['message_body'] = $message;
				$aMessage['example_title'] = '';
				$aMessage['example_body'] = '';
				$aMessage['example_comments'] = '';
				
				$aResult = InitFEuser->CheckSpam($aMessage, FALSE);
				
				if(isset($aResult) && is_array($aResult))
				{
					if($aResult['errno'] == 0)
					{
						if($aResult['allow'] == 0)
						{
							if (preg_match('//u', $aResult['ct_result_comment']))
							{
								$comment_str = preg_replace('/^[^\*]*?\*\*\*|\*\*\*[^\*]*?$/iu', '', $aResult['ct_result_comment']);
								$comment_str = preg_replace('/<[^<>]*>/iu', '', $comment_str);
							}
							else
							{
								$comment_str = preg_replace('/^[^\*]*?\*\*\*|\*\*\*[^\*]*?$/i', '', $aResult['ct_result_comment']);
								$comment_str = preg_replace('/<[^<>]*>/i', '', $comment_str);
							}
							InitFEuser::CleantalkDie($comment_str);
						}
					}
				}
		    }
		}
		if(isset($_GET['get_auto_key']))
		{
			if($GLOBALS['BE_USER']['user']['admin']==1)
			{
				require_once '../../Resources/cleantalk.class.php';
				$admin_email=$GLOBALS['BE_USER']['user']['email'];
				$site=$_SERVER['HTTP_HOST'];
				$result = getAutoKey($admin_email,$site,'typo3');
				if ($result)
				{
					$result = json_decode($result, true);
					if (isset($result['data']) && is_array($result['data']))
					{
						$result = $result['data'];
					}
					else if(isset($result['error_no']))
					{
						header('Location: ?cleantalk_message='.$result['error_message']);
						die();
					}
					if(isset($result['auth_key']))
					{
						$conf=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cleantalk']);
						$conf['accessKey']=$result['auth_key'];
						$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cleantalk']=serialize($conf);
					}
					header('Location: .');
					die();
				}
			}
		}
	}
	
	static function cleantalkGetFields(&$email,&$message,$arr)
	{
		$is_continue=true;
		foreach($arr as $key=>$value)
		{
			if(strpos($key,'ct_checkjs')!==false)
			{
				$email=null;
				$message='';
				$is_continue=false;
			}
		}
		if($is_continue)
		{
			foreach($arr as $key=>$value)
			{
				if(!is_array($value))
				{
					if ($email === null && preg_match("/^\S+@\S+\.\S+$/", $value))
			    	{
			            $email = $value;
			        }
			        else
			        {
			        	$message.="$value\n";
			        }
				}
				else
				{
					InitFEuser::cleantalkGetFields($email,$message,$value);
				}
			}
		}
	}
	
	
	static function CleantalkDie($message)
	{
		$error_tpl=file_get_contents(dirname(__FILE__)."/error.html");
		print str_replace('%ERROR_TEXT%',$message,$error_tpl);
		die();
	}
	
	static function CheckSpam(&$arEntity, $bSendEmail = FALSE) {
	$conf=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cleantalk']);
      if(!is_array($arEntity) || !array_key_exists('type', $arEntity)) return;

        $type = $arEntity['type'];
        if($type != 'comment' && $type != 'register') return;

	$ct_key = $conf['accessKey'];

        if (!session_id()) session_start();

	if (!isset($_COOKIE['ct_checkjs'])) {
	    $checkjs = NULL;
	}
	elseif ($_COOKIE['ct_checkjs'] == self::GetCheckJSValue()) {
	    $checkjs = 1;
	}
	else {
	    $checkjs = 0;
	}

        if(isset($_SERVER['HTTP_USER_AGENT']))
            $user_agent = htmlspecialchars((string) $_SERVER['HTTP_USER_AGENT']);
        else
            $user_agent = NULL;

        if(isset($_SERVER['HTTP_REFERER']))
            $refferrer = htmlspecialchars((string) $_SERVER['HTTP_REFERER']);
        else
            $refferrer = NULL;

	$ct_language = 'en';

        $sender_info = array(
            'cms_lang' => $ct_language,
            'REFFERRER' => $refferrer,
            'post_url' => $refferrer,
            'USER_AGENT' => $user_agent
        );
        $sender_info = json_encode($sender_info);

        require_once '../../Resources/cleantalk.class.php';
        
        $ct = new Cleantalk();

	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
	    $forwarded_for = (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ? htmlentities($_SERVER['HTTP_X_FORWARDED_FOR']) : '';
	}
        $sender_ip = (!empty($forwarded_for)) ? $forwarded_for : $_SERVER['REMOTE_ADDR'];

        $ct_request = new CleantalkRequest();
        $ct_request->auth_key = $ct_key;
        $ct_request->sender_email = isset($arEntity['sender_email']) ? $arEntity['sender_email'] : '';
        $ct_request->sender_nickname = isset($arEntity['sender_nickname']) ? $arEntity['sender_nickname'] : '';
        $ct_request->sender_ip = isset($arEntity['sender_ip']) ? $arEntity['sender_ip'] : $sender_ip;
        $ct_request->agent = 'magento-120';
        $ct_request->js_on = $checkjs;
        $ct_request->sender_info = $sender_info;

        $ct_submit_time = NULL;
        if(isset($_SESSION['ct_submit_time']))
        $ct_submit_time = time() - $_SESSION['ct_submit_time'];

        switch ($type) {
            case 'comment':
                $timelabels_key = 'mail_error_comment';
                $ct_request->submit_time = $ct_submit_time;

                $message_title = isset($arEntity['message_title']) ? $arEntity['message_title'] : '';
                $message_body = isset($arEntity['message_body']) ? $arEntity['message_body'] : '';
                $ct_request->message = $message_title . " \n\n" . $message_body;

                $example = '';
                $a_example['title'] = isset($arEntity['example_title']) ? $arEntity['example_title'] : '';
                $a_example['body'] =  isset($arEntity['example_body']) ? $arEntity['example_body'] : '';
                $a_example['comments'] = isset($arEntity['example_comments']) ? $arEntity['example_comments'] : '';

                // Additional info.
                $post_info = '';
                $a_post_info['comment_type'] = 'comment';

                // JSON format.
                $example = json_encode($a_example);
                $post_info = json_encode($a_post_info);

                // Plain text format.
                if($example === FALSE){
                    $example = '';
                    $example .= $a_example['title'] . " \n\n";
                    $example .= $a_example['body'] . " \n\n";
                    $example .= $a_example['comments'];
                }
                if($post_info === FALSE)
                    $post_info = '';

                // Example text + last N comments in json or plain text format.
                $ct_request->example = $example;
                $ct_request->post_info = $post_info;
                $ct_result = $ct->isAllowMessage($ct_request);
                break;
            case 'register':
                $timelabels_key = 'mail_error_reg';
                $ct_request->submit_time = $ct_submit_time;
                $ct_request->tz = isset($arEntity['user_timezone']) ? $arEntity['user_timezone'] : NULL;
                $ct_result = $ct->isAllowUser($ct_request);
        }

        $ret_val = array();
        $ret_val['ct_request_id'] = $ct_result->id;

        if($ct->server_change)
            self::SetWorkServer(
                $ct->work_url, $ct->server_url, $ct->server_ttl, time()
            );

        // First check errstr flag.
        if(!empty($ct_result->errstr)
            || (!empty($ct_result->inactive) && $ct_result->inactive == 1)
        ){
            // Cleantalk error so we go default way (no action at all).
            $ret_val['errno'] = 1;
            $err_title = $_SERVER['SERVER_NAME'] . ' - CleanTalk module error';

            if(!empty($ct_result->errstr)){
		    if (preg_match('//u', $ct_result->errstr)){
            		    $err_str = preg_replace('/^[^\*]*?\*\*\*|\*\*\*[^\*]*?$/iu', '', $ct_result->errstr);
		    }else{
            		    $err_str = preg_replace('/^[^\*]*?\*\*\*|\*\*\*[^\*]*?$/i', '', $ct_result->errstr);
		    }
            }else{
		    if (preg_match('//u', $ct_result->comment)){
			    $err_str = preg_replace('/^[^\*]*?\*\*\*|\*\*\*[^\*]*?$/iu', '', $ct_result->comment);
		    }else{
			    $err_str = preg_replace('/^[^\*]*?\*\*\*|\*\*\*[^\*]*?$/i', '', $ct_result->comment);
		    }
	    }
            $ret_val['errstr'] = $err_str;

            

            return $ret_val;
        }

        $ret_val['errno'] = 0;
        if ($ct_result->allow == 1) {
            // Not spammer.
            $ret_val['allow'] = 1;
        }else{
            $ret_val['allow'] = 0;
            $ret_val['ct_result_comment'] = $ct_result->comment;
            // Spammer.
            // Check stop_queue flag.
            if($type == 'comment' && $ct_result->stop_queue == 0) {
                // Spammer and stop_queue == 0 - to manual approvement.
                $ret_val['stop_queue'] = 0;
            }else{
                // New user or Spammer and stop_queue == 1 - display message and exit.
                $ret_val['stop_queue'] = 1;
            }
        }
        return $ret_val;
    }
    
    static function PageAddon() {
        if (!session_id()) session_start();
	$_SESSION['ct_submit_time'] = time();

	$field_name = 'ct_checkjs';	// todo - move this to class constant
	$ct_check_def = '0';
	if (!isset($_COOKIE[$field_name])) setcookie($field_name, $ct_check_def, 0, '/');

	$ct_check_value = self::GetCheckJSValue();
	$js_template = '<script type="text/javascript">
// <![CDATA[
function ctSetCookie(c_name, value) {
 document.cookie = c_name + "=" + escape(value) + "; path=/";
}
ctSetCookie("%s", "%s");
// ]]>
</script>
';
	$ct_template_addon_body = sprintf($js_template, $field_name, $ct_check_value);
	return $ct_template_addon_body;
    }
    
    static function GetCheckJSValue() {
    $conf=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cleantalk']);
	return md5($conf['access_key'] . '_' . $GLOBALS['BE_USER']['user']['email']);
    }
    
    public function hookRegisterData($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL)
    {
    	$sender_email = null;
	    $message = '';
	    InitFEuser::cleantalkGetFields($sender_email,$message,$_POST);
	    if($sender_email!==null)
	    {
			$aMessage = array();
			$aMessage['type'] = 'register';
			$aMessage['sender_email'] = $sender_email;
			$aMessage['sender_nickname'] = '';
			$aMessage['message_title'] = '';
			$aMessage['message_body'] = $message;
			$aMessage['example_title'] = '';
			$aMessage['example_body'] = '';
			$aMessage['example_comments'] = '';
			
			$aResult = InitFEuser->CheckSpam($aMessage, FALSE);
			
			if(isset($aResult) && is_array($aResult))
			{
				if($aResult['errno'] == 0)
				{
					if($aResult['allow'] == 0)
					{
						if (preg_match('//u', $aResult['ct_result_comment']))
						{
							$comment_str = preg_replace('/^[^\*]*?\*\*\*|\*\*\*[^\*]*?$/iu', '', $aResult['ct_result_comment']);
							$comment_str = preg_replace('/<[^<>]*>/iu', '', $comment_str);
						}
						else
						{
							$comment_str = preg_replace('/^[^\*]*?\*\*\*|\*\*\*[^\*]*?$/i', '', $aResult['ct_result_comment']);
							$comment_str = preg_replace('/<[^<>]*>/i', '', $comment_str);
						}
						$ajaxObj->setResult($comment_str);
						return;
					}
				}
			}
	    }
    }
    
    public function hookCommentData($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL)
    {
    	$sender_email = null;
	    $message = '';
	    InitFEuser::cleantalkGetFields($sender_email,$message,$_POST);
	    if($sender_email!==null)
	    {
			$aMessage = array();
			$aMessage['type'] = 'comment';
			$aMessage['sender_email'] = $sender_email;
			$aMessage['sender_nickname'] = '';
			$aMessage['message_title'] = '';
			$aMessage['message_body'] = $message;
			$aMessage['example_title'] = '';
			$aMessage['example_body'] = '';
			$aMessage['example_comments'] = '';
			
			$aResult = InitFEuser->CheckSpam($aMessage, FALSE);
			
			if(isset($aResult) && is_array($aResult))
			{
				if($aResult['errno'] == 0)
				{
					if($aResult['allow'] == 0)
					{
						if (preg_match('//u', $aResult['ct_result_comment']))
						{
							$comment_str = preg_replace('/^[^\*]*?\*\*\*|\*\*\*[^\*]*?$/iu', '', $aResult['ct_result_comment']);
							$comment_str = preg_replace('/<[^<>]*>/iu', '', $comment_str);
						}
						else
						{
							$comment_str = preg_replace('/^[^\*]*?\*\*\*|\*\*\*[^\*]*?$/i', '', $aResult['ct_result_comment']);
							$comment_str = preg_replace('/<[^<>]*>/i', '', $comment_str);
						}
						$ajaxObj->setResult($comment_str);
						return;
					}
				}
			}
	    }
    }
}

?>
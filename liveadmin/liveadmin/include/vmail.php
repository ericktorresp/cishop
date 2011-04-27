<?php

/***************************************************************
 * Live Admin Standalone
 * Copyright 2008-2011 Dayana Networks Ltd.
 * All rights reserved, Live Admin  is  protected  by  Canada and
 * International copyright laws. Unauthorized use or distribution
 * of  Live Admin  is  strictly  prohibited,  violators  will  be
 * prosecuted. To  obtain  a license for using Live Admin, please
 * register at http://www.liveadmin.net/register.php
 *
 * For more information please refer to Live Admin official site:
 *    http://www.liveadmin.net
 *
 * Translation service provided by Google Inc.
 ***************************************************************/

if(!defined('LIVEADMIN')) exit;
require_once("mail/email_message.php");
class LV_Mail
{
	private $lv_admin;
	private $abcode;
	private $StandAlone;
	function LV_Mail(&$lv_admin_in)
	{
		$this->lv_admin = $lv_admin_in;
		$this->StandAlone = array();
		$this->abcode = array ( 'SendLogEmail' => 'SLE', 'SendClientEmail' => 'SCM' );
	}
	function AbuseCode($tcode)
	{
		if(LIVEADMIN_STANDALONE)
		{
			$params = $this->StandAlone;
			$params['message_code'] = $tcode;
			$mt = new GetTemplate(LIVEADMIN_FT.'/disclaimer.txt',$params);
			$text_message = $mt->GetPart('footer');
		}
		return($text_message);
	}
	function SendLogEmail($r)
	{
		$RV = array();
		$RV['status']=0;
		if(!isset($r['chatid']) || $r['chatid']=='')
		{
			$RV['error']='Invalid or unspecified chat log.';
			return($RV);
		}
		if(!isset($r['remail']) || $r['remail']=='' || !IsValidEmailSyntax($r['remail']))
		{
			$RV['error']='Receiver email address is invalid.';
			return($RV);
		}
		if(!isset($r['semail']) || $r['semail']=='' || !IsValidEmailSyntax($r['semail']))
		{
			$RV['error']='Sender email address is invalid.';
			return($RV);
		}
		if(!isset($r['subject']) || $r['subject']=='') $r['subject'] = 'Chat transcript';
		if(strpos($r['chatid'],",")!==false)
		{
			$chatid = explode(",",$r['chatid']);
		}
		else
		{
			$chatid = array($r['chatid']);
		}
		$params = array();
		$chats = array();
		foreach($chatid as $a=>$v)
		{
			$res = $this->lv_admin->GetLogMsg(array('chatid'=>$v));
			if($res['status']==1)
			{
				$params['body'] = liveadmin_decode64($res['result']);
				$params['id'] = $v;
				$mt = new GetTemplate(LIVEADMIN_FT.'/chat_log.txt',$params);
				$chats[$v] = array ( "Data"=>$mt->GetPart('body'), "Name"=>trim(str_replace(array("\r","\n","\s"),'',$mt->GetPart('name'))), "Content-Type"=>"automatic/name", "Disposition"=>"attachment" );
			}
		}
		if(LIVEADMIN_STANDALONE)
		{
			$SInfoParams = array();
			$SInfoParams = AddArray($SInfoParams,$_SERVER['sinfo'],'sinfo-');
			$this->StandAlone = $SInfoParams;
		}
		$from_address=$r['semail'];
		$from_name=$r['sname'];
		$reply_name=$from_name;
		$reply_address=$from_address;
		$reply_address=$from_address;
		$error_delivery_name=$from_name;
		$error_delivery_address=$from_address;
		$to_name=$r['rname'];
		$to_address=$r['remail'];
		$tcode = rand(1000,9999).'-'.ArrayMember($this->abcode,__FUNCTION__,'UN1').'-'.$_SERVER['sinfo']['siteid'].'-'.rand(10,99).$_SERVER['uinfo']['userid'].'-'.time();
		$subject=$r['subject'];
		$text_message = $r['message']."\n\n\n";
		$text_message .= $this->AbuseCode($tcode);
		$email_message=new email_message_class;
		$email_message->SetEncodedEmailHeader("To",$to_address,$to_name);
		$email_message->SetEncodedEmailHeader("From",$from_address,$from_name);
		$email_message->SetEncodedEmailHeader("Reply-To",$reply_address,$reply_name);
		$email_message->SetHeader("Sender",$from_address);
		$email_message->SetEncodedHeader("Subject",$subject);
		$email_message->AddQuotedPrintableTextPart($email_message->WrapText($text_message));
		foreach($chats as $a=>$v)
		{
			$email_message->AddFilePart($v);
		}
		$error=$email_message->Send();
		if(strcmp($error,""))
		{
			$RV['status'] = 0;
			$RV['error'] = $error;
		}
		else
		{
			$RV['status'] = 2;
			$RV['info'] = "Message sent to $to_name ($to_address)";
		}
		return($RV);
	}
	function SendClientEmail($inp)
	{
		foreach($inp as $a=>$v)
		{
			if(is_array($v)) continue;
			if($a!='body') $v = str_replace(array("\n","\r"),'',$v);
			$inp[$a] = trim($v);
		}
		if(!isset($inp['subject']) || $inp['subject']=='') return false;
		if(!isset($inp['body']) || trim(str_replace(array("\r","\n"),"",$inp['body']))=='') return false;
		if(!isset($inp['to_email']) || $inp['to_email']=='' || !IsValidEmailSyntax($inp['to_email'])) return false;
		$inp2 = $inp;
		if(!isset($inp['from_email']) || $inp['from_email']=='' || !IsValidEmailSyntax($inp['from_email'])) $inp['from_email'] = $inp['to_email'];
		if(!isset($inp['from_name']) || $inp['from_name']=='') $inp['from_name'] = $inp['from_email'];
		if(!isset($inp['to_name']) || $inp['to_name']=='') $inp['to_name'] = $inp['to_email'];
		require_once("lang.php");
		$lang = new LV_Lang($inp['language']);
		$inp2 = AddArray($inp2,$lang->GetAllBase(),'language-');
		if($inp2['type']==1)
		{
			$inp2['body'] = nl2br($inp2['body']);
		}
		$mt = new GetTemplate(LIVEADMIN_FT.'/client_message.txt',$inp2);
		if(LIVEADMIN_STANDALONE)
		{
			$SInfoParams = array();
			$SInfoParams = AddArray($SInfoParams,$inp['stnl_sinfo'],'sinfo-');
			$this->StandAlone = $SInfoParams;
		}
		$from_address=$inp['from_email'];
		$from_name=$inp['from_name'];
		$reply_name=$inp['from_name'];
		$reply_address=$inp['from_email'];
		$error_delivery_name=$inp['from_name'];
		$error_delivery_address=$inp['from_email'];
		$to_name=$inp['to_name'];
		$to_address=$inp['to_email'];
		$tcode = rand(1000,9999).'-'.ArrayMember($this->abcode,__FUNCTION__,'UN6').'-'.$inp['siteid'].'-'.rand(10,99).'-'.time();
		$email_message=new email_message_class;
		$email_message->SetEncodedEmailHeader("To",$to_address,$to_name);
		$email_message->SetEncodedEmailHeader("From",$from_address,$from_name);
		$email_message->SetEncodedEmailHeader("Reply-To",$reply_address,$reply_name);
		$email_message->SetHeader("Sender",$from_address);
		switch($inp['type'])
		{
			case 0: $mail_subject = $mt->GetPart('subject');
			$mail_body = $mt->GetPart('body_text');
			$subject=$mail_subject;
			$text_message = $mail_body."\n\n";
			$text_message .= $this->AbuseCode($tcode);
			$email_message->SetEncodedHeader("Subject",$subject);
			$email_message->SetEncodedHeader("X-LiveAdmin-Code",$tcode);
			$email_message->AddQuotedPrintableTextPart($email_message->WrapText($text_message));
			$error=$email_message->Send();
			break;
			case 1: $mail_subject = $mt->GetPart('subject');
			$mail_body_text = $mt->GetPart('body_text');
			$mail_body_html = $mt->GetPart('body_html');
			$subject=$mail_subject;
			$mail_body_text .= "\n\n";
			$mail_body_text .= $this->AbuseCode($tcode);
			$email_message->SetEncodedHeader("Subject",$subject);
			$email_message->SetEncodedHeader("X-LiveAdmin-Code",$tcode);
			$email_message->CreateQuotedPrintableHTMLPart($mail_body_html,$inp2['language-charset'],$html_part);
			$email_message->CreateQuotedPrintableTextPart($email_message->WrapText($mail_body_text),$inp2['language-charset'],$text_part);
			$alternative_parts=array( $text_part, $html_part );
			$email_message->AddAlternativeMultipart($alternative_parts);
			$error=$email_message->Send();
			break;
		}
	}
}
?>
<? ?>
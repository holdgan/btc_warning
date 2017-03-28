<?php

$wms=fopen("messageid.txt","r");
$lastid=fgets($wms);
fclose($wms);

$ok_btc_url='https://www.okcoin.cn/api/v1/ticker.do?symbol=btc_cny';
$yb_btc_url='https://yuanbao.com/api_market/getinfo_cny/coin/btc';
$yb_message_url='https://www.yuanbao.com/news/detail/?id='.$lastid;

$ok_html = file_get_contents($ok_btc_url);
$yb_html=file_get_contents($yb_btc_url);
$yb_message_html=file_get_contents($yb_message_url);
//print_r($ok_html);
//print_r($yb_html);

$ok_json=json_decode($ok_html,true);
$yb_json=json_decode($yb_html,true);

//print_r($ok_json['ticker']['buy']);
//print_r($yb_json['price']);

$ok_btc=$ok_json['ticker']['buy'];
$yb_btc=$yb_json['price'];

$cha=$ok_btc-$yb_btc;
$email='your email';
$content='ok_btc - yb_btc = '.$cha;
$label='okcoin:'.$ok_btc.'   yuanbao:'.$yb_btc;

if(!empty($yb_message_html)){
		$data_arr=array(
                        'to'=>$email,
                        'label'=>'YuanbaoWang id:'.$lastid,
                        'title'=>'new announcements id:'.$lastid,
                        'content'=>$yb_message_html
                );
		//echo $yb_message_html;
	       	sendAction($data_arr,2);
		$tms=fopen("messageid.txt","w");
       		 fwrite($tms,$lastid+1);
       		 fclose($tms);

		echo "send ".$lastid."\n";
}

$data_arr=array(
                        'to'=>$email,
                        'label'=>$label,
                        'title'=>$cha,
                        'content'=>$content
                );
if($ok_btc<5500||$ok_btc>7000){
	sendAction($data_arr);
}else if($cha>500){
	//yb high 200
	//sendAction($data_arr);
}else{
	if($cha>2000||$cha<-2000){
	//data err
	}else{
		if($cha>400||$cha<-400){
        		sendAction($data_arr);
		}else if($cha>50||$cha<-50){
			if(date('H',time())>=8&&date('H',time())<=23){
				sendAction($data_arr);
			}
		}else{
			echo "price difference is small\n";
		}
	}
}


function sendAction($params,$type=1){
$wxw=fopen("data.txt","r");
$lasttime=fgets($wxw);
fclose($wxw);
if(time()-$lasttime>=600||$type==2){
       // $params = $this->getRequest()->getParams();
        if(!isset($params['to'],$params['title'],$params['content'])){
            exit('erorr params');
        }
        $to = explode(',', $params['to']);
        $label = urldecode($params['label']);
        $title = urldecode($params['title']);
        $content = urldecode($params['content']);
        if(empty($to) || empty($title) || empty($content)){
            exit('erorr params');
        }
       // foreach($to as $v){
           // $mail_host = explode('@', $v);
            //if($mail_host[1] != 'o2btc.com'){
              //  Tool_Fnc::mailto($params['to'], $title, $content);
               // exit;
          //  }
       // }
        mailto($to,$label, $title, $content);
	$txt=fopen("data.txt","w");
	fwrite($txt,time());
	fclose($txt);
	echo "send success\n";
    }else{
	//need wait
	echo "need wait \n";
}
}

 function mailto($pAddress,$pLabel, $pSubject, $pBody, $pCcAddress = NULL){
		 $mail='';
		if(!$mail){
			require './PHPmailer.php';
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->CharSet = 'utf-8';
			$mail->SMTPAuth = true;
			$mail->Port = 25;
			$mail->Host = "smtp.163.com";
			$mail->From = "your send email";
			$mail->Username = "your email user";
			$mail->Password = "your email password";
			$mail->FromName = $pLabel;
			$mail->IsHTML(true);
		}
		$mail->ClearAddresses();
		$mail->ClearCCs();
		$mail->ClearBCCs();
        if(is_array($pAddress)){
            foreach($pAddress as $v){
		        $mail->AddAddress($v);
            }
            unset($v);
        } else {
		    $mail->AddAddress($pAddress);
        }
		$pCcAddress && $mail->AddBCC($pCcAddress);
		$mail->Subject = $pSubject;
		$mail->MsgHTML(preg_replace('/\\\\/', '', $pBody));
		if($mail->Send()){
			return 1;
		}else{
			return $mail->ErrorInfo;
		}
	}

?>

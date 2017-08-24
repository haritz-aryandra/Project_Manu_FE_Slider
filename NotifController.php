<?php

class NotifController extends Controller {
    
    public function actionIndex($service = false) {
        if ((!isset($_POST)) || (empty($_POST))) {
            $_POST = json_decode(file_get_contents("php://input"),true);
        }
        
        $member_id = $_POST['user_id'];
        $imei = $_POST['imei'];
        
        $result = array();
        $result['isLogOut'] = 1;
        $result['isNotification'] = 0;
        $result['title'] = "";
        $result['content'] = "";
        
        $member = TblMember::model()->findByPk($member_id);
        if ($member != NULL) {
            if ($member->imei == $imei) {
                $result['isLogOut'] = 0;
                $notifMember = TblNotifMember::model()->findByAttributes(array(
                    'member_id'=>$member_id,
                    'status'=>0
                ));
                if ($notifMember != NULL) {
                    $notif = TblNotif::model()->findByPk($notifMember->notif_id);
                    if ($notif != NULL) {
                        $result['isNotification'] = 1;
                        $result['title'] = $notif->title;
                        $result['content'] = $notif->content;
                        $notifMember->status = 1;
                        $notifMember->save();
                    }
                }
            }
        }
        
        echo json_encode($result);
    }
    
}
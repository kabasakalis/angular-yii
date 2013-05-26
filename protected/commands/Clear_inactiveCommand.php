<?php
/**
 *  class TestCommand
 * Created by Spiros Kabasakalis.
 * Date: 8/15/12
 * Time: 10:18 AM
 * yiic clear_inactive delete --days=2
 *
 */

class Clear_inactiveCommand extends CConsoleCommand
{

    public function init() {
        echo "Deleting Inactive Users Older than days specified \n";
        parent::init();
    }

    public function actionDelete($days='1'){

                             $criteria = new CDbCriteria;
                             $criteria->condition = 'DATE_SUB(CURDATE(),INTERVAL '. $days.'  DAY) >= create_time AND status=:status';
                             $criteria->params = array(':status' => User::STATUS_INACTIVE);
                              $users = User::model()->findAll($criteria);
                              $result=array();
                              foreach ($users as $user) $result[]=$user->attributes;
                              print_r($result);
                              $users_deleted = User::model()->deleteAll($criteria);
                               echo ($users_deleted .' inactive users for longer than '.$days. ' day(s) deleted!');
    }

}

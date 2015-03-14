<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Frontend\Controller;
use Admin\Entity\Orders;
use Velacolib\Utility\Utility;

use Admin\Model\orderdetailModel;
use Admin\Model\orderModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Velacolib\Utility\TransactionUtility;
use Velacolib\Utility\MailUtility;
use Velacolib\Utility\DropboxUtility;

class BackupController extends AbstractActionController
{
    protected   $modelOrder;
    protected   $modelOrderDetail;
    protected   $translator;
    protected  $userLogin;


    public function onDispatch(\Zend\Mvc\MvcEvent $e){
        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrineService = $this->sm->get($service_locator_str);
        $this->modelOrder = new orderModel($doctrineService);
        $this->modelOrderDetail = new orderdetailModel($doctrineService);

        return parent::onDispatch($e);

    }

    public function indexAction()
    {
        // delete coupon expire
       // $coupon = Utility::deleteExpireCoupon();

        $appKey = 'rqvezrg3abysf3x';
        $appSecret = 'uoye0pmyap6fuoy';
        $service_locator_str = 'doctrine.connection.orm_default';
        $sm = $this->getServiceLocator();
        $service = $sm->get($service_locator_str);
        $params = $service->getParams();
        $host = $params['host'];
        $user = $params['user'];
        $pass = $params['password'];
        $name = $params['dbname'];
        $tables = "*";
        $return = '';
        $link = mysql_connect($host,$user,$pass);
        if($link){
            mysql_select_db($name,$link);
            //get all of the tables
            if($tables == '*')
            {
                $tables = array();
                $result = mysql_query('SHOW TABLES');
                while($row = mysql_fetch_row($result))
                {
                    $tables[] = $row[0];
                }
            }
            else
            {
                $tables = is_array($tables) ? $tables : explode(',',$tables);
            }

            //cycle through
            foreach($tables as $table)
            {
                $result = mysql_query('SELECT * FROM '.$table);
                $num_fields = mysql_num_fields($result);

                $return.= 'DROP TABLE '.$table.';';
                $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
                $return.= "\n\n".$row2[1].";\n\n";

                for ($i = 0; $i < $num_fields; $i++)
                {
                    while($row = mysql_fetch_row($result))
                    {
                        $return.= 'INSERT INTO '.$table.' VALUES(';
                        for($j=0; $j<$num_fields; $j++)
                        {
                            $row[$j] = addslashes($row[$j]);
                            $row[$j] = ereg_replace("\n","\\n",$row[$j]);
                            if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                            if ($j<($num_fields-1)) { $return.= ','; }
                        }
                        $return.= ");\n";
                    }
                }
                $return.="\n\n\n";
            }
            $files = glob('public\backup\*'); // get all file names
            foreach($files as $file){ // iterate files
                if(is_file($file))
                    unlink($file); // delete file
            }

            $directoryBk = 'public/backup/';

            $backupName = 'backup-'.time().'-'.date("d",time()).'-'.date('m',time()).'-'.date('Y',time()).'.sql';

            //save file

            $handle = fopen($directoryBk.$backupName,"w+");

            // write file
            fwrite($handle,$return);
            // close file
            fclose($handle);

            DropboxUtility::enable_implicit_flush();

            $dropbox = new \DropboxClient(array(
                'app_key' => $appKey,
                'app_secret' => $appSecret,
                'app_full_access' => false,
            ),'en');
            $accessToken = "AD4_9TIkfjoAAAAAAAA0jFsQImeJnIyvWDrjBa_ND7vP5YjuZFNMbS7jMdvwhCem";
            $accessToken =  DropboxUtility::load_token('access');
            if(!empty($accessToken)){
                $dropbox->SetAccessToken($accessToken);
            }elseif(!empty($_GET['auth_callback'])){
                $requestToken = DropboxUtility::load_token($_GET['oauth_token']);
                if(empty($requestToken)) die('Request token not found!');

                // get & store access token, the request token is not needed anymore
                $accessToken = $dropbox->GetAccessToken($requestToken);
                DropboxUtility::store_token($accessToken, "access");
                DropboxUtility::delete_token($_GET['oauth_token']);

            }

            if(!$dropbox->IsAuthorized())
            {
                // redirect user to dropbox auth page
                $return_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?auth_callback=1";
                $auth_url = $dropbox->BuildAuthorizeUrl($return_url);
                $request_token = $dropbox->GetRequestToken();
                DropboxUtility::store_token($request_token, $request_token['t']);
                die("Authentication required. <a href='$auth_url'>Click here.</a>");
            }else{
                $fileUpload = $dropbox->UploadFile($directoryBk.$backupName);
                print_r($fileUpload);
            }

            // send mail
               $sendMail = MailUtility::sendMailAttachment($directoryBk.$backupName,$backupName,'Kaffa DB','nguyenthanhhungbt1905@gmail.com',true);

            die('Backup success!');


        }else{
           die( mysql_error() );
        }
    }

    public function mailAction(){

        TransactionUtility::checkAndSendNotifyEmail();
        die;

    }

    public function updateAction(){

        $orderDetailArr = $this->modelOrderDetail->updateQuery(
            3149
        );

        foreach($orderDetailArr as $item){

            $orderSingle = $this->modelOrder->findOneBy(array(
                'id'=>$item->getOrderId()
            ));
            $item->setTime(date("Y-m-d H:i:s",$orderSingle->getCreateDate()));
            $this->modelOrderDetail->edit($item);

        }



    }



}
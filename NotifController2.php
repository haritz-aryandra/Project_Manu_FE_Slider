<?php

class NotifController extends Controller
{

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('index','create','update','delete'),
				'expression'=>'Yii::app()->user->getRole() != -1',
			),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('broadcast'),
				'expression'=>'Yii::app()->user->getRole() == 0',
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new TblNotif;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['TblNotif']))
		{
			$model->attributes=$_POST['TblNotif'];
			if($model->save()) {
				$this->redirect(array('index'));
            }
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['TblNotif']))
		{
			$model->attributes=$_POST['TblNotif'];
			if($model->save())
				$this->redirect(array('index'));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model=new TblNotif('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['TblNotif']))
			$model->attributes=$_GET['TblNotif'];

		$this->render('index',array(
			'model'=>$model,
		));
	}
    
    public function actionBroadcast($id) {
        $notifsMember = TblNotifMember::model()->findAll();
        foreach ($notifsMember as $notif) {
            $notif->delete();
        }
        $model = $this->loadModel($id);
        $members = TblMember::model()->findAll();
        foreach ($members as $member) {
            $notifMember = new TblNotifMember();
            $notifMember->member_id = $member->id;
            $notifMember->notif_id = $model->id;
            $notifMember->status = 0;
            $notifMember->save();
        }
        $ret = array('status'=>'ok');
        echo CJSON::encode($ret);
    }

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return TblNotif the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=TblNotif::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param TblNotif $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='tbl-notif-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}

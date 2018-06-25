<?php

namespace app\controllers;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\blog;

use Yii;
use app\models\Event;

class BlogController extends \yii\web\Controller
{
    public function actionIndex()
    {
    	$blogs = Blog::find()->orderBy('created_at desc')->all();
        return $this->render('index', ['blogs' => $blogs]);
    }


    public function behaviors()
	{
	    return [
	        'access' => [
	            'class' => AccessControl::className(),
	            'only' => ['store','repost'],
	            'rules' => [
	                [
	                    'allow' => true,
	                    'roles' => ['@'],
	                ],
	            ],
	        ],
	        'verbs' => [
	            'class' => VerbFilter::className(),
	            'actions' => [
	                'store' => ['post'],
	                'repost' => ['post'],
	            ],
	        ],
	    ];
	}


	# 弹窗对应方法 状态保存
	public function actionStore()
	{
		$model = new Blog();
		
		if($filenames = Yii::$app->request->post('filenames')) {
	        $model->img = json_encode($filenames);
	    }
		
		if ($model->load(Yii::$app->request->post()) &&  $model->save()) {
			Event::create(Blog::className(), $model->id, Event::PUBLISH);
        	return $this->goHome();
	    }
	    return $model->errors;
	}



	 # 获取源、转发博客 id
    public function actionRepost()
	{
	    if (!($repost_blog = Blog::findOne(Yii::$app->request->post('Blog')['id']))) {
	            return $this->goBack();
	    }

	    $model = new Blog();
	    $model->parent_id = $repost_blog->id;
	    $model->origin_id = $repost_blog->origin_id ?? $repost_blog->id;

	    if ($model->load(Yii::$app->request->post()) &&  $model->save()) {
	    	Event::create(Blog::className(), $model->id, Event::REPOST);
	        return $this->goBack();
	    }
	    return $model->errors;
	}


	public function actionDelete($id)
	{
		# 查找对应的状态列表

	    $blog = Blog::findOne($id);

	    # 判断用户身份
	    if ($blog->user_id == Yii::$app->user->id ) {
	        Event::destroy(Blog::className(),$blog->id, Blog::className());
	        $blog->delete();
	    }
	    return $this->goHome();
	}


}

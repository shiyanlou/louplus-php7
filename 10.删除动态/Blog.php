<?php

namespace app\models;

use Yii;
use yii\data\Pagination;
use yii\bootstrap\Html;

/**
 * This is the model class for table "blogs".
 *
 * @property int $id
 * @property int $user_id
 * @property int $parent_id
 * @property int $origin_id
 * @property string $text
 * @property string $img
 * @property string $updated_at
 * @property string $created_at
 */
class Blog extends \yii\db\ActiveRecord
{
    public $topic_id;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'blogs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['user_id', 'default','value'=>Yii::$app->user->id],
            [['text'], 'string', 'max' => 255],
            [['updated_at', 'created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'origin_user_id' => 'Origin User ID',
            'text' => '说点什么',
           'img' => 'Img',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    //用户 博客
    public function getUser()
    {
        return $this->hasOne(User::className(),['id' => 'user_id']);
    }
    //博客 评论
    public function getComments()
    {
        return $this->hasMany(Comment::className(),['blog_id'=>'id']);
    }
    public function getTopics()
    {
        return $this->hasMany(Topic::className(),['id'=>'topic_id'])
            ->viaTable(TopicBlog::tableName(),['blog_id'=>'id']);
    }

    public function getTopic()
    {
        return $this->hasOne(Topic::className(),['id'=>'topic_id'])
            ->viaTable(TopicBlog::tableName(),['blog_id'=>'id']);
    }

    public function text()
{
	if (empty($this->topics)) {
		return $this->text;
	}
	$words = '';
	$values = $results = [];
	foreach ($this->topics as $topic) {
		$words = '#' . $topic['name'] . '#';
		$values[] = $words;
		$results[] = Html::a($words,['topic/show','id'=>$topic['id']]);
	}
	return str_replace($values,$results,$this->text);
		
}	

    public function getParent()
    {
        return $this->hasOne(Blog::className(),['id'=>'parent_id']);
    }
    public function getOrigin()
    {
        return $this->hasOne(Blog::className(),['id'=>'origin_id']);
    }
    public function getEvents()
    {
        return $this->hasMany(Event::className(),['target_id'=>'id'])->where(['target_type'=>Blog::className()]);
    }
    public function getLike()
    {
        return $this->hasOne(Like::className(),['blog_id'=>'id'])->where(['user_id'=>Yii::$app->user->id]);
    }
    public function beforeSave($insert)
    {
        parent::beforeSave($insert);

        preg_match_all("/#(.*?)#/",$this->text,$matches);

        if (empty($matches)) {
            return true;
        }

        foreach($matches[1] as $key => $name) {
        
            if (!($topic = Topic::findOne(['name'=>$name]))) {
                $topic = new Topic();
                $topic->name = $name;
                $topic->user_id = Yii::$app->user->id;
            }
            $topic->blog_count++;
            if( !$topic->save()) {
                return false;
            }
            $this->topic_id[] = $topic->id;
        }
        return true;
    }

    public function afterSave($insert,$changedAttributes)
    {
        parent::afterSave($insert,$changedAttributes);

        //话题
        if (!empty($this->topic_id)) {
            foreach($this->topic_id as $topic_id) {
                $tb = new TopicBlog();
                $tb->topic_id = $topic_id;
                $tb->blog_id = $this->id;
                if (!$tb->save()) {
                    return false;
                }
            }
        }
        return true;
    }
    
    public function afterDelete()
    {
        Comment::deleteAll(['blog_id' => $this->id]);
    } 



    public function page($page, $options = []) 
    {
        $query = blog::find()->with([
            'user'=>function($q){$q->select('id,name,avatar');},
            'topics'=>function($q){$q->select('id,name');},
            'origin'=>function($q){$q->with(['user','like','topics']);},
            'like',
            'parent',

        ])->where($options['where'] ?? [])->orderBy($options['sort'] ?? 'id desc');
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $blogs = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        return ['blogs'=>$blogs,'pagination'=>$pagination];

    
    }
}

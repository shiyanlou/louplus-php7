<?php

namespace app\models;

use Yii;
use yii\bootstrap\Html;

/**
 * This is the model class for table "blogs".
 *
 * @property int $id
 * @property int $parent_id
 * @property int $origin_id
 * @property int $user_id
 * @property int $popularity_count
 * @property string $text
 * @property array $img
 * @property string $updated_at
 * @property string $created_at
 */
class Blog extends \yii\db\ActiveRecord
{
 
    public $topic_id = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'blogs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
           ['user_id', 'default', 'value'=>Yii::$app->user->id],
           [['text'], 'string', 'max' => 255],
           [['upload_at,create_at'],'safe'], 
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'origin_id' => 'Origin ID',
            'user_id' => 'User ID',
            'popularity_count' => 'Popularity Count',
            'text' => '说点什么',
            'img' => 'Img',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getParent()
    {
        return $this->hasOne(Blog::className(), ['id' => 'parent_id']);
    }

    public function getOrigin()
    {
        return $this->hasOne(Blog::className(), ['id' => 'origin_id']);
    }

    public function getComments()
    {
        return $this->hasMany(Comment::className(),['blog_id'=>'id']);
    }

    public function getLike()
    {
        return $this->hasOne(Like::className(),['blog_id'=>'id'])->where(['user_id'=>Yii::$app->user->id]);
    }

    public function getEvents()
    {
        return $this->hasMany(Event::className(),['target_id' => 'id']);
    }

    # 模拟外键 
    public function getTopics()
    {
        return $this->hasMany(Topic::className(),['id' => 'topic_id'])->viaTable(TopicBlog::tableName(),['blog_id'=>'id']);
    }

    public function text()
    {
        
        // var_dump($this->topics);die;
        if(empty($this->topics)) {
            return $this->text;
        }
        $replace = $find = [];
        foreach($this->topics as $topic) {
            $name = '#'.$topic['name'].'#';
            $find[] = $name;
            $replace[] = Html::a($name,['topic/show','id'=>$topic['id']]);
        }
        return str_replace($find,$replace,$this->text);


    }

    # 状态保存之前 先保存话题
    public function beforeSave($insert)
    {
        parent::beforeSave($insert);


        # 匹配话题内容里面符合格式的字段 并装载进match
        preg_match_all("/#(.*?)#/",$this->text,$matches);
         // print_r($matches);die;

        if (empty($matches)) {
            return true;
        }

        foreach($matches[1] as $key => $name) {
            # 如果数据库中没有该话题 则新建话题
            if (!($topic = Topic::findOne(['name'=>$name]))) {
                $topic = new Topic();
                $topic->name = $name;
                # 话题对应的用户为登录用户
                $topic->user_id = Yii::$app->user->id;
            }
            # 该话题下的状态数量++
            $topic->blog_count++;

            if( !$topic->save()) {
                return false;
            }
            # 保存新建话题ID
            $this->topic_id[] = $topic->id;
        }
        return true;
    }

    # 话题|状态 保存之后 需要更新两者之间的关联
    public function afterSave($insert,$changedAttributes)
    {
        parent::afterSave($insert,$changedAttributes);

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










}

<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "message".
 *
 * @property int $id
 * @property int $chat_id
 * @property string $username
 * @property string $text
 * @property int $create_time
 *
 * @property Chat $chat
 */
class Message extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'message';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['chat_id', 'username', 'text', 'create_time'], 'required'],
            [['chat_id', 'create_time'], 'integer'],
            [['text'], 'string'],
            [['username'], 'string', 'max' => 255],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::class, 'targetAttribute' => ['chat_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'chat_id' => Yii::t('app', 'Chat ID'),
            'username' => Yii::t('app', 'Username'),
            'text' => Yii::t('app', 'Text'),
            'create_time' => Yii::t('app', 'Create Time'),
        ];
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChat() {
        return $this->hasOne(Chat::class, ['id' => 'chat_id']);
    }
}

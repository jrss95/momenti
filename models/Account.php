<?php

namespace app\models;

class Account extends \yii\db\ActiveRecord
{
    public static function tableName() {
        return 'accounts';
    }
}

<?php

namespace frontend\models;

use yii\base\Model;

class UserFilter extends Model
{
    public $categories = [];
    public $additionally = [];
    public $name;
    public $free;
    public $online;
    public $has_rate;
    public $favourite;

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'categories' => 'Категории',
            'additionally' => 'Дополнительно',
            'name' => 'Поиск по имени',
            'free' => 'Сейчас свободен',
            'online' => 'Сейчас онлайн',
            'has_rate' => 'Есть отзывы',
            'favourite' => 'В избранном',
        ];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['categories', 'additionally', 'name', 'free', 'online', 'has_rate', 'favourite'], 'safe']
        ];
    }
}

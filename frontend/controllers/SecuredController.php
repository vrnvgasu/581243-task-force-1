<?php

namespace frontend\controllers;

use frontend\models\City;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class SecuredController extends Controller
{
    public $cities;
    public $events;
    public $selectedCity;

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        $user = Yii::$app->user->identity;
        if ($user) {
            $user->last_activity_at = date('Y-m-d H:i:s');
            $user->save();

            $this->events = $user->getEvents()->where(['view_feed_at' => null])->all();
            $this->selectedCity = Yii::$app->session->get('city') ?? $user->city_id;
        }

        $this->cities = City::find()->select('city')->indexBy('id')->column();

        return parent::beforeAction($action);
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'signup', 'auth'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['login', 'signup', 'auth'],
                        'allow' => false,
                        'roles' => ['@'],
                        'denyCallback' => function($rule, $action) {
                            return $this->redirect(Url::to(['/task/']));
                        },
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['?'],
                        'denyCallback' => function($rule, $action) {
                            return $this->redirect(Url::to(['/']));
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'login' => ['post'],
                ],
            ],
        ];
    }
}

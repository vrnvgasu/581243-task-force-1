<?php

namespace frontend\modules\api\actions;

use frontend\models\Task;
use frontend\models\User;
use Yii;
use yii\base\Model;
use yii\rest\Action;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class TaskIndexAction extends Action
{
    public $scenario = Model::SCENARIO_DEFAULT;

    public function run()
    {
        $user = Yii::$app->user->identity;
        if (!$user) {
            throw new ServerErrorHttpException('Not authorize.');
        }

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        $tasks = [];
        foreach ($user->executorTasks as $task) {
            $tasks[] = [
                'title' => $task->name,
                'published_at' => $task->created_at,
                'new_messages' => $task->getMessages()
                    ->where(['!=', 'author_id', $user->id])
                    ->andWhere(['read' => false])->count(),
                'author_name' => $task->client->name,
                'id' => $task->id,
            ];
        }

        return $tasks;
    }
}

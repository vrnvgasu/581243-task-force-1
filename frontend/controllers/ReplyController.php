<?php

namespace frontend\controllers;

use frontend\models\Reply;
use frontend\models\Task;
use frontend\models\User;
use TaskForce\classes\actions\RejectAction;
use TaskForce\classes\actions\RespondAction;
use TaskForce\classes\actions\TakeInWorkAction;
use TaskForce\exceptions\ActionException;
use TaskForce\exceptions\StatusException;
use Yii;
use yii\web\Response;

class ReplyController extends SecuredController
{
    /**
     * @return Response
     * @throws ActionException
     * @throws StatusException
     */
    public function actionCreate()
    {
        $user = User::getUser(Yii::$app->user->getId());
        $replyForm = new Reply();

        if (Yii::$app->request->getIsPost()) {
            $replyForm->load(Yii::$app->request->post());
            $replyForm->executor_id = $user->id;
            $task = Task::findOne($replyForm->task_id);

            if ($task &&
                RespondAction::checkRights($user, $task) &&
                $replyForm->validate() &&
                $replyForm->save()) {
                $nextStatus = $task->getNextStatus(RespondAction::getInnerName());
                $task->setCurrentStatus($nextStatus);
            }
        }

        return $this->redirect(Yii::$app->request->referrer ?? '/task/');
    }

    public function actionReject($taskId, $replyId)
    {
        $user = User::getUser(Yii::$app->user->getId());
        $task = Task::findOne($taskId);
        $reply = Reply::findOne($replyId);

        if ($task && $reply && RejectAction::checkRights($user, $task, $reply)) {
            $reply->rejected = true;
            $reply->save();
            $nextStatus = $task->getNextStatus(RejectAction::getInnerName());
            $task->setCurrentStatus($nextStatus);
        }

        return $this->redirect(Yii::$app->request->referrer ?? '/task/');
    }

    /**
     * @param $taskId
     * @param $replyId
     * @return Response
     * @throws ActionException
     * @throws StatusException
     */
    public function actionTakeInWork($taskId, $replyId)
    {
        $user = User::getUser(Yii::$app->user->getId());
        $task = Task::findOne($taskId);
        $reply = Reply::findOne($replyId);

        if ($task && $reply && TakeInWorkAction::checkRights($user, $task)) {
            $nextStatus = $task->getNextStatus(TakeInWorkAction::getInnerName());
            $task->setCurrentStatus($nextStatus);
            $task->executor_id = $reply->executor_id;
            $task->save();
        }

        return $this->redirect(Yii::$app->request->referrer ?? '/task/');
    }
}
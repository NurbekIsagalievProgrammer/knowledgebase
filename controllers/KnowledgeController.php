<?php
namespace app\controllers;

use yii\web\Controller;
use app\models\Knowledge;
use Yii;

class KnowledgeController extends Controller
{
    public function actionIndex()
    {
        $knowledge = new Knowledge();

        return $this->render('index', [
            'knowledge' => $knowledge,
        ]);
    }

    // AJAX экшены для подтем и содержимого

    public function actionSubtopics($topic)
    {
        $knowledge = new Knowledge();
        $knowledge->setCurrentTopic($topic);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'subtopics' => $knowledge->getSubtopics(),
            'content' => $knowledge->getContent(),
        ];
    }

    public function actionContent($topic, $subtopic)
    {
        $knowledge = new Knowledge();
        $knowledge->setCurrentTopic($topic);
        $knowledge->setCurrentSubtopic($subtopic);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'content' => $knowledge->getContent(),
        ];
    }
}

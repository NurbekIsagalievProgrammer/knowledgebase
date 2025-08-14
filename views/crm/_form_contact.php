<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Deal;
use yii\helpers\ArrayHelper;

/** @var $model app\models\Contact */

// подготовка списка сделок: id => "id — title"
$deals = ArrayHelper::map(Deal::loadAll(), 'id', function($d) {
    return ($d['id'] ?? '') . ' — ' . ($d['title'] ?? '(без названия)');
});

// гарантируем, что $model->deals массив
if (!is_array($model->deals)) $model->deals = [];
?>

<div class="contact-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'first_name')->textInput() ?>
    <?= $form->field($model, 'last_name')->textInput() ?>

    <?= $form->field($model, 'deals')->checkboxList($deals) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

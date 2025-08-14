<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Contact;
use yii\helpers\ArrayHelper;

/** @var $model app\models\Deal */

// подготовка списка контактов: id => "id — Имя Фамилия"
$contactsList = ArrayHelper::map(Contact::loadAll(), 'id', function($c) {
    return ($c['id'] ?? '') . ' — ' . ($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '');
});

// гарантируем, что $model->contacts массив
if (!is_array($model->contacts)) $model->contacts = [];
?>

<div class="deal-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput() ?>
    <?= $form->field($model, 'amount')->textInput() ?>
    <?= $form->field($model, 'status')->dropDownList([
       'Новая' => 'Новая',
       'В процессе' => 'В процессе',
       'Закрыта' => 'Закрыта',
    ]) ?>

    <?= $form->field($model, 'contacts')->checkboxList($contactsList) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<h1>Сделка: <?= $model->title ?></h1>
<p>Контакт: <?= $contactName ?></p>
<p>Сумма: <?= $model->amount ?></p>
<p>Статус: <?= $model->status ?></p>
<a href="<?= \yii\helpers\Url::to(['crm/deals']) ?>">Назад</a>

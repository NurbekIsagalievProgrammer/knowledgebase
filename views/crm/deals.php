<h1>Сделки</h1>
<p><a class="btn btn-primary" href="<?= \yii\helpers\Url::to(['crm/create-deal']) ?>">Добавить сделку</a></p>

<table class="table table-bordered">
    <tr>
        <th>ID</th><th>Контакт</th><th>Название</th><th>Сумма</th><th>Статус</th><th>Действия</th>
    </tr>
    <?php foreach ($deals as $deal): ?>
        <tr>
            <td><?= $deal['id'] ?></td>
            <td><?= $contacts[$deal['contact_id']] ?? '—' ?></td>
            <td><?= $deal['title'] ?></td>
            <td><?= $deal['amount'] ?></td>
            <td><?= $deal['status'] ?></td>
            <td>
                <a href="<?= \yii\helpers\Url::to(['crm/view-deal', 'id' => $deal['id']]) ?>">Просмотр</a> |
                <a href="<?= \yii\helpers\Url::to(['crm/update-deal', 'id' => $deal['id']]) ?>">Редактировать</a> |
                <a href="<?= \yii\helpers\Url::to(['crm/delete-deal', 'id' => $deal['id']]) ?>" data-confirm="Удалить?" data-method="post">Удалить</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

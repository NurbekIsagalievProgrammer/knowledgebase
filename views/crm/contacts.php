<h1>Контакты</h1>
<p><a class="btn btn-primary" href="<?= \yii\helpers\Url::to(['crm/create-contact']) ?>">Добавить контакт</a></p>

<table class="table table-bordered">
    <tr>
        <th>ID</th><th>Имя</th><th>Email</th><th>Телефон</th><th>Действия</th>
    </tr>
    <?php foreach ($contacts as $contact): ?>
        <tr>
            <td><?= $contact['id'] ?></td>
            <td><?= $contact['name'] ?></td>
            <td><?= $contact['email'] ?></td>
            <td><?= $contact['phone'] ?></td>
            <td>
                <a href="<?= \yii\helpers\Url::to(['crm/view-contact', 'id' => $contact['id']]) ?>">Просмотр</a> |
                <a href="<?= \yii\helpers\Url::to(['crm/update-contact', 'id' => $contact['id']]) ?>">Редактировать</a> |
                <a href="<?= \yii\helpers\Url::to(['crm/delete-contact', 'id' => $contact['id']]) ?>" data-confirm="Удалить?" data-method="post">Удалить</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php
/** @var $knowledge \app\models\Knowledge */

use yii\helpers\Html;
use yii\helpers\Url;

$topics = $knowledge->getTopics();
$currentTopic = $knowledge->getCurrentTopic();
$subtopics = $knowledge->getSubtopics($currentTopic);
$currentSubtopic = $knowledge->getCurrentSubtopic();
$content = $knowledge->getContent($currentTopic, $currentSubtopic);
?>

<style>
.container {
    display: flex;
    max-width: 900px;
    margin: 20px auto;
    font-family: Arial, sans-serif;
}
.block {
    border: 1px solid #ccc;
    padding: 10px;
    margin-right: 10px;
    min-width: 150px;
    height: 300px;
    overflow-y: auto;
}
.block:last-child {
    margin-right: 0;
    flex-grow: 1;
}
.item {
    padding: 5px;
    cursor: pointer;
}
.item:hover {
    background-color: #eee;
}
.selected {
    background-color: yellow;
    font-weight: bold;
}
</style>

<div class="container">
    <div class="block" id="topics">
        <h3>Темы</h3>
        <?php foreach ($topics as $topic): ?>
            <div class="item topic-item <?= $topic === $currentTopic ? 'selected' : '' ?>" data-topic="<?= Html::encode($topic) ?>">
                <?= Html::encode($topic) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="block" id="subtopics">
        <h3>Подтемы</h3>
        <?php foreach ($subtopics as $subtopic): ?>
            <div class="item subtopic-item <?= $subtopic === $currentSubtopic ? 'selected' : '' ?>" data-subtopic="<?= Html::encode($subtopic) ?>">
                <?= Html::encode($subtopic) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="block" id="content">
        <h3>Содержимое</h3>
        <div id="content-text"><?= Html::encode($content) ?></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    $('.topic-item').click(function(){
        var selectedTopic = $(this).data('topic');

        $('.topic-item').removeClass('selected');
        $(this).addClass('selected');

        $.ajax({
            url: '<?= Url::to(['knowledge/subtopics']) ?>',
            type: 'GET',
            data: {topic: selectedTopic},
            success: function(data){
                var subtopicsHtml = '';
                data.subtopics.forEach(function(sub){
                    subtopicsHtml += '<div class="item subtopic-item">'+sub+'</div>';
                });
                $('#subtopics').find('.item').remove();
                $('#subtopics').append(subtopicsHtml);

                $('#subtopics .subtopic-item').first().addClass('selected');

                $('#content-text').text(data.content);
            }
        });
    });

    $('#subtopics').on('click', '.subtopic-item', function(){
        var selectedSubtopic = $(this).text();

        $('.subtopic-item').removeClass('selected');
        $(this).addClass('selected');

        var currentTopic = $('.topic-item.selected').data('topic');
        $.ajax({
            url: '<?= Url::to(['knowledge/content']) ?>',
            type: 'GET',
            data: {topic: currentTopic, subtopic: selectedSubtopic},
            success: function(data){
                $('#content-text').text(data.content);
            }
        });
    });
});
</script>

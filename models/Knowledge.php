<?php
namespace app\models;

class Knowledge
{
    private $data;
    private $currentTopic;
    private $currentSubtopic;

    public function __construct()
    {
        $this->data = [
            'Тема 1' => [
                'Подтема 1.1' => 'Некий текст, привязанный к Подтеме 1.1',
                'Подтема 1.2' => 'Некий текст, привязанный к Подтеме 1.2',
                'Подтема 1.3' => 'Некий текст, привязанный к Подтеме 1.3',
            ],
            'Тема 2' => [
                'Подтема 2.1' => 'Некий текст, привязанный к Подтеме 2.1',
                'Подтема 2.2' => 'Некий текст, привязанный к Подтеме 2.2',
                'Подтема 2.3' => 'Некий текст, привязанный к Подтеме 2.3',
            ],
        ];
        $this->currentTopic = 'Тема 1';
        $this->currentSubtopic = 'Подтема 1.1';
    }

    public function getTopics()
    {
        return array_keys($this->data);
    }

    public function getSubtopics($topic = null)
    {
        if ($topic === null) $topic = $this->currentTopic;
        return isset($this->data[$topic]) ? array_keys($this->data[$topic]) : [];
    }

    public function getContent($topic = null, $subtopic = null)
    {
        if ($topic === null) $topic = $this->currentTopic;
        if ($subtopic === null) $subtopic = $this->currentSubtopic;
        return $this->data[$topic][$subtopic] ?? '';
    }

    public function setCurrentTopic($topic)
    {
        if (isset($this->data[$topic])) {
            $this->currentTopic = $topic;
            $subs = $this->getSubtopics($topic);
            $this->currentSubtopic = $subs[0] ?? null;
        }
    }

    public function setCurrentSubtopic($subtopic)
    {
        $subs = $this->getSubtopics($this->currentTopic);
        if (in_array($subtopic, $subs)) {
            $this->currentSubtopic = $subtopic;
        }
    }

    public function getCurrentTopic()
    {
        return $this->currentTopic;
    }

    public function getCurrentSubtopic()
    {
        return $this->currentSubtopic;
    }
}

<?php

namespace app\commands;

use app\models\Chat;
use app\models\Message;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;

class ChatController extends Controller {
    public array $connections = [];

    public function actionRun() {
        $worker = new Worker('websocket://' . Yii::$app->params['workerSocketName']);

        $worker->onConnect = [$this, 'onConnect'];

        $worker->onClose = [$this, 'onClose'];

        $worker->onMessage = [$this, 'onMessage'];

        Worker::runAll();
    }

    public function onConnect(TcpConnection $connection) {
        $this->connections[$connection->id] = $connection;
    }

    public function onClose(TcpConnection $connection) {
        unset($this->connections[$connection->id]);
    }

    /**
     * @throws InvalidConfigException
     */
    public function onMessage(TcpConnection $connection, string $data) {
        $payload = json_decode($data, true);

        $data = match ($payload['method']) {
            'getChats' => $this->getChats($connection, $payload),
            'createChat' => $this->createChat($connection, $payload),
            'sendMessage' => $this->sendMessage($connection, $payload),
            'getMessages' => $this->getMessages($connection, $payload),
        };

        $response = [
            'method' => $payload['method'],
            'data' => $data
        ];

        $connection->send(json_encode($response));
    }

    private function getChats(TcpConnection $connection, $payload): array {
        return ['chats' => Chat::find()->asArray()->all()];
    }

    /**
     * @throws InvalidConfigException
     */
    private function createChat(TcpConnection $connection, $payload): array {
        $chat = new Chat();
        $chat->name = $payload['data']['name'];
        $chat->create_time = time();
        $chat->save();

        $message = $this->createMessage($chat->id, $payload['data']['username'], $payload['data']['text']);

        $this->notifyAll($message, [$connection->id]);

        return [
            'chat' => [
                'id' => $chat->id,
                'name' => $chat->name,
                'create_time' => Yii::$app->formatter->asDatetime($chat->create_time)
            ],
            'message' => [
                'id' => $message->id,
                'chat_id' => $message->chat_id,
                'username' => $message->username,
                'text' => $message->text,
                'create_time' => Yii::$app->formatter->asDatetime($message->create_time)
            ]
        ];
    }

    /**
     * @throws InvalidConfigException
     */
    private function sendMessage(TcpConnection $connection, $payload): array {
        $chat_id = $payload['data']['chat_id'];

        $chat = Chat::findOne($chat_id);

        if (is_null($chat)) {
            return ['error' => 'Chat not found'];
        }

        $message = $this->createMessage($chat_id, $payload['data']['username'], $payload['data']['text']);

        $this->notifyAll($message, [$connection->id]);

        return [
            'message' => [
                'id' => $message->id,
                'chat_id' => $message->chat_id,
                'username' => $message->username,
                'text' => $message->text,
                'create_time' => Yii::$app->formatter->asDatetime($message->create_time)
            ]
        ];
    }

    /**
     * @throws InvalidConfigException
     */
    private function getMessages(TcpConnection $connection, $payload): array {
        $chat_id = $payload['data']['chat_id'];

        $chat = Chat::findOne($chat_id);

        if (is_null($chat)) {
            return ['error' => 'Chat not found'];
        }

        return [
            'messages' => array_map(function (Message $message) {
                return [
                    'id' => $message->id,
                    'chat_id' => $message->chat_id,
                    'username' => $message->username,
                    'text' => $message->text,
                    'create_time' => Yii::$app->formatter->asDatetime($message->create_time)
                ];
            }, Message::find()->where(['chat_id' => $chat_id])->all())
        ];
    }

    private function createMessage($chat_id, $username, $text): Message {
        $message = new Message();
        $message->chat_id = $chat_id;
        $message->username = $username;
        $message->text = $text;
        $message->create_time = time();
        $message->save();

        return $message;
    }

    private function notifyAll(Message $message, $except = []) {
        $response = json_encode([
            'method' => 'newMessage',
            'data' => [
                'message' => [
                    'id' => $message->id,
                    'chat_id' => $message->chat_id,
                    'username' => $message->username,
                    'text' => $message->text,
                    'create_time' => Yii::$app->formatter->asDatetime($message->create_time)
                ]
            ]
        ]);

        foreach ($this->connections as $conn) {
            if (in_array($conn->id, $except)) {
                continue;
            }

            $conn->send($response);
        }
    }
}
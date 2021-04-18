<?php

declare(strict_types=1);

namespace Labile\SimpleVKExtend;

use DigitalStars\SimpleVK\LongPoll;
use DigitalStars\SimpleVK\SimpleVK;
use DigitalStars\SimpleVK\SimpleVkException;
use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use RuntimeException;
use Throwable;

/**
 * Личное дополнение к SimpleVK
 * Class SimpleVKExtend
 * @package Manager\Models
 */
class SimpleVKExtend
{
    use FileUploader;

    /**
     * Массив с данными которые пришли от вк
     * есть также ванильный $this->data от SimpleVK
     * @var array
     */
    private static array $vars;

    /**
     * Парсинг всех данных которые пришли от вк в красивый вид
     * @param SimpleVK|LongPoll $vk
     * @return void ($this->vars)
     */
    public static function parse(SimpleVK|LongPoll $vk): void
    {
        $SimpleVKData = $vk->initVars($id, $user_id, $type, $message, $payload, $msg_id, $attachments);

        $chat_id = $id - 2e9;
        $chat_id = $chat_id > 0 ? (int)$chat_id : null;

        [
            $data['group_id'],
            $data['peer_id'],
            $data['chat_id'],
            $data['user_id'],
            $data['type'],
            $data['text'],
            $data['text_lower'],
            $data['payload'],
            $data['action'],
            $data['message_id'],
            $data['conversation_message_id'],
            $data['attachments'],
            $data['fwd_messages'],
            $data['reply_message']
        ] = [
            $SimpleVKData['group_id'] ?? null,
            $id ?? null,
            $chat_id ?? null,
            $user_id ?? null,
            $type ?? null,
            $message ?? null,
            isset($message) ? mb_strtolower($message) : null,
            $payload ?? null,
            $SimpleVKData['object']['message']['action'] ?? null,
            $msg_id,
            $SimpleVKData['object']['message']['conversation_message_id'] ?? null,
            $attachments,
            $SimpleVKData['object']['message']['fwd_messages'] ?? [],
            $SimpleVKData['object']['message']['reply_message'] ?? [],
        ];

        self::$vars = $data;
    }

    /**
     * Получить необходимые\все данные которые прислал вк
     * @param string|null $var
     * @return mixed
     */
    public static function getVars(string $var = null): mixed
    {
        if ($var === null) {
            return self::$vars;
        }

        if (is_string($var) && isset(self::$vars[$var])) {
            return self::$vars[$var];
        }

        return null;
    }

    /**
     * Загрузить видео в вк
     * @param SimpleVK $vk
     * @param string $file
     * @param string $name
     * @param string|null $description
     * @param bool|null $is_private
     * @param int|null $wallpost
     * @param int|null $group_id
     * @param int|null $album_id
     * @param int|null $no_comments
     * @param int|null $repeat
     * @param int|null $compression
     * @return string|false
     * @throws Exception|Throwable
     */
    public static function uploadVideo(SimpleVK $vk, string $file, string $name, string $description = null, bool $is_private = null, int $wallpost = null, int $group_id = null, int $album_id = null, int $no_comments = null, int $repeat = null, int $compression = null): string|false
    {
        $result = self::uploadMultiplyVideo($vk,
            [
                $file =>
                    [
                        'name' => $name,
                        'description' => $description,
                        'is_private' => $is_private,
                        'wallpost' => $wallpost,
                        'group_id' => $group_id,
                        'album_id' => $album_id,
                        'no_comments' => $no_comments,
                        'repeat' => $repeat,
                        'compression' => $compression
                    ]
            ]);

        return is_array($result) ? $result[0] : $result;
    }

    /**
     * Загрузить множество видео асинхронно
     * @param SimpleVK $vk
     * @param array $data
     * @return array|false
     * @throws Exception|Throwable
     */
    public static function uploadMultiplyVideo(SimpleVK $vk, array $data): array|false
    {
        foreach ($data as $url => $params) {
            $response = self::getVkVideoUploadUrl($vk,
                $params['name'] ?? uniqid('', false),
                $params['description'] ?? null,
                $params['is_private'] ?? null,
                $params['wallpost'] ?? null,
                $params['group_id'] ?? null,
                $params['album_id'] ?? null,
                $params['no_comments'] ?? null,
                $params['repeat'] ?? null,
                $params['compression'] ?? null
            );
            $upload_url[] = $response['upload_url'];
            $link[] = $url;
            $attachments[] = 'video' . $response['owner_id'] . '_' . $response['video_id'];
        }

        $promise = self::uploadAsync($upload_url, $link, 'video_file')
            ->then(
                function () use ($attachments) {
                    return $attachments;
                },

                function (RequestException $e) {
                    return false;
                }
            );

        return $promise->wait();
    }

    /**
     * Загрузить множество историй асинхронно
     * @param SimpleVK $vk
     * @param array $data
     * @return false|array
     * @throws SimpleVkException|Throwable
     */
    public static function createMultiplyStories(SimpleVK $vk, array $data): false|array
    {
        foreach ($data as $url => $params) {
            $response = self::getStoriesUploadServer($vk,
                $params['add_to_news'] ?? null,
                $params['user_ids'] ?? null,
                $params['reply_to_story'] ?? null,
                $params['link_text'] ?? null,
                $params['link_url'] ?? null,
                $params['group_id'] ?? null,
                $params['clickable_stickers'] ?? null);
            $upload_url[] = $response['upload_url'];
            $link[] = $url;
        }

        $callback = static function (Response $response) use (&$upload_results) {
            $res = (string)$response->getBody();
            $item = json_decode($res, true, 512, JSON_THROW_ON_ERROR);
            if (isset($item['response'])) {
                $upload_results[] = $item['response']['upload_result'];
            }
        };

        self::uploadAsync($upload_url, $link, 'video_file', $callback)->wait();

        function fetchAttachments(array $items): array
        {
            foreach ($items as $item) {
                if ($item !== null) {
                    $attachments[] = 'story' . $item['owner_id'] . '_' . $item['id'];
                }
            }
            return $attachments;
        }

        $items = self::storiesSave($vk, implode(",", $upload_results));
        return $items['count'] === 0 ? false : fetchAttachments($items['items']);

    }

    /**
     * Создать историю
     * История не должна быть длиннее 15 секунд
     * @param SimpleVK $vk
     * @param string $file
     * @param int $add_to_news
     * @param string|int|null $user_ids
     * @param int|null $reply_to_story
     * @param string|null $link_text
     * @param string|null $link_url
     * @param int|null $group_id
     * @param string|null $clickable_stickers
     * @return string|false
     * @throws SimpleVkException
     * @throws Exception|Throwable
     */
    public static function createStories(SimpleVK $vk, string $file, int $add_to_news, string|int $user_ids = null, int $reply_to_story = null, string $link_text = null, string $link_url = null, int $group_id = null, string $clickable_stickers = null): string|false
    {
        $result = self::createMultiplyStories($vk,
            [
                $file =>
                    [
                        'add_to_news' => $add_to_news,
                        'user_ids' => $user_ids,
                        'reply_to_story' => $reply_to_story,
                        'link_text' => $link_text,
                        'link_url' => $link_url,
                        'group_id' => $group_id,
                        'clickable_stickers' => $clickable_stickers,
                    ]
            ]);

        return is_array($result) ? $result[0] : $result;
    }


    /**
     * Получить всех менеджеров в группе
     * @param SimpleVK $vk
     * @param int $group_id
     * @return array|false
     * @throws Exception
     */
    public static function getManagersGroup(SimpleVK $vk, int $group_id): array|false
    {
        try {
            $response = $vk->request('groups.getMembers',
                [
                    'group_id' => $group_id,
                    'filter' => 'managers'
                ]
            );

            if (isset($response['count']) && $response['count'] > 0) {
                $ids = null;
                foreach ($response['items'] as $item) {
                    $ids[] = $item['id'];
                }
                return $ids;
            }
        } catch (Exception) {
            throw new RuntimeException('Токен не имеет доступа к менеджерам группы');
        }

        return false;
    }

}

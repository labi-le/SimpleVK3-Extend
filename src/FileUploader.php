<?php

declare(strict_types=1);


namespace Labile\SimpleVKExtend;


use DigitalStars\SimpleVK\SimpleVK;
use DigitalStars\SimpleVK\SimpleVkException;
use Exception;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use RuntimeException;
use Throwable;
use function Amp\ParallelFunctions\parallelMap;
use function Amp\Promise\wait;

trait FileUploader
{

    /**
     * Загружаем видео с интернетов во временный файл
     * @param string $external_file
     * @return mixed
     * @throws Exception
     */
    private static function openWebFile(string $external_file): mixed
    {
        $tmp_file = tmpfile();
        $tmp_filename = stream_get_meta_data($tmp_file)['uri'];

        return copy($external_file, $tmp_filename) ? file_get_contents($tmp_filename) : throw new Exception('Не удалось загрузить файл');
    }

    /**
     * Открыть локальный файл
     * @param string $path
     * @return mixed
     * @throws Exception
     */
    private static function openLocalFile(string $path): mixed
    {
        return file_exists($path) ? file_get_contents($path) : throw new RuntimeException('Файл не найден');
    }

    /**
     * Создать форму для загрузки чего-то
     * @param string $file
     * @param string $name
     * @return array
     * @throws Exception
     */
    private static function createMultipart(string $file, string $name): array
    {
        return
            [
                'multipart' => [
                    [
                        'name' => $name,
                        'contents' => filter_var($file, FILTER_VALIDATE_URL)
                            ? self::openWebFile($file)
                            : self::openLocalFile($file)

                    ],
                ]
            ];
    }

    /**
     * Загрузить что-то в вк (асинхронно)
     * @param array $vk_upload_url
     * @param array $data
     * @param string $name
     * @param callable|null $ResponseCallback
     * @param callable|null $RequestExceptionCallback
     * @return PromiseInterface
     * @throws Throwable
     */
    private static function uploadAsync(array $vk_upload_url, array $data, string $name, callable $ResponseCallback = null, callable $RequestExceptionCallback = null): PromiseInterface
    {
        $client = new Client();

        $promise = parallelMap($data, function ($url) use ($name) {
            return SimpleVKExtend::createMultipart($url, $name);
        });

        $multipart = wait($promise);

        $requests = static function (array $vk_upload_url) use ($multipart, $client) {
            for ($i = 0, $iMax = count($vk_upload_url); $i < $iMax; $i++) {
                yield static function () use ($multipart, $vk_upload_url, $client, $i) {
                    return $client->postAsync($vk_upload_url[$i], $multipart[$i]);
                };
            }
        };

        $pool = self::createPool($client, $requests($vk_upload_url), $ResponseCallback, $RequestExceptionCallback);
        return $pool->promise();

    }

    /**
     * Создать пул с запросами
     * @param Client $client
     * @param $request
     * @param callable|null $ResponseCallback
     * @param callable|null $RequestExceptionCallback
     * @param int $concurrency
     * @return Pool
     */
    private static function createPool(Client $client, Generator $request, callable $ResponseCallback = null, callable $RequestExceptionCallback = null, int $concurrency = 10): Pool
    {
        return new Pool($client, $request, [
            'concurrency' => $concurrency,

            'fulfilled' => static function (Response $response) use ($ResponseCallback) {
                $ResponseCallback === null ?: $ResponseCallback($response);
            },

            'rejected' => static function (RequestException $reason) use ($RequestExceptionCallback) {
                $RequestExceptionCallback === null ?: $RequestExceptionCallback($reason);
            },
        ]);
    }


    /**
     * Сохранить историю
     * @param SimpleVK $vk
     * @param string|null $upload_results
     * @return false|array
     * @throws SimpleVkException
     */
    private static function storiesSave(SimpleVK $vk, string|null $upload_results): false|array
    {
        if ($upload_results === null) {
            return false;
        }
        return $vk->request('stories.save', ['upload_results' => $upload_results]);
    }

    /**
     * Получить ссылку для загрузки истории
     * @param SimpleVK $vk
     * @param int $add_to_news
     * @param string|int|null $user_ids
     * @param int|null $reply_to_story
     * @param string|null $link_text
     * @param string|null $link_url
     * @param int|null $group_id
     * @param string|null $clickable_stickers
     * @return array|false
     * @throws SimpleVkException
     */
    private static function getStoriesUploadServer(SimpleVK $vk, int $add_to_news = 1, string|int $user_ids = null, int $reply_to_story = null, string $link_text = null, string $link_url = null, int $group_id = null, string $clickable_stickers = null): array|false
    {
        return $vk->request('stories.getVideoUploadServer',
            [
                'add_to_news' => $add_to_news,
                'user_ids' => $user_ids,
                'reply_to_story' => $reply_to_story,
                'link_text' => $link_text,
                'link_url' => $link_url,
                'group_id' => $group_id,
                'clickable_stickers' => $clickable_stickers,
            ]);
    }

    /**
     * Получить ссылку для загрузки видео
     * @param SimpleVK $vk
     * @param string|int $name
     * @param string|int|null $description
     * @param bool|int|null $is_private
     * @param bool|int|null $wallpost
     * @param int|null $group_id
     * @param int|null $album_id
     * @param bool|int|null $no_comments
     * @param bool|int|null $repeat
     * @param bool|int|null $compression
     * @return false|array
     */
    private static function getVkVideoUploadUrl(SimpleVK $vk, string|int $name, string|int $description = null, bool|int $is_private = null, bool|int $wallpost = null, int $group_id = null, int $album_id = null, bool|int $no_comments = null, bool|int $repeat = null, bool|int $compression = null): false|array
    {
        try {
            return $vk->request('video.save',
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
                ]);
        } catch (Exception) {
            return false;
        }
    }
}
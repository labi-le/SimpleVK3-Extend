# SimpleVK3 Extend

Личное расширение для SimpleVK 3

___

## Содержание

1. [Установка](#1-установка)
2. [Методы](#2-методы)
3. [Примеры](#2-примеры)
   + [Множественная загрузка историй](#31-множественная-загрузка-историй)
   + [Множественная загрузка видео](#32-множественная-загрузка-видео)
   + [Загрузка видео](#33-загрузка-видео)
   + [Загрузка истории](#34-загрузка-истории)

___

## 1. Установка

> composer require labile/simple-vk3-extend

## 2. Методы

```php

 /**
 * Загрузить видео в вк
 */
public static function uploadVideo(SimpleVK $vk, string $file, string $name, string $description = null, bool $is_private = null, int $wallpost = null, int $group_id = null, int $album_id = null, int $no_comments = null, int $repeat = null, int $compression = null): string|false

 /**
 * Загрузить множество видео асинхронно
 */
public static function uploadMultiplyVideo(SimpleVK $vk, array $data): array|false
	
 /**
 * Загрузить историю
 * История не должна быть длиннее 15 секунд
 */
public static function createStories(SimpleVK $vk, string $file, int $add_to_news, string|int $user_ids = null, int $reply_to_story = null, string $link_text = null, string $link_url = null, int $group_id = null, string $clickable_stickers = null): string|false
	
 /**
 * Загрузить множество историй асинхронно
 */
public static function createMultiplyStories(SimpleVK $vk, array $data): false|array
	
 /**
 * Получить всех менеджеров в группе
 */
public static function getManagersGroup(SimpleVK $vk, int $group_id): array|false
```
## 3. Примеры

#### 3.1. Множественная загрузка историй
```php
declare(strict_types=1);

const ACCESS_TOKEN = '';
$vk = SimpleVK::create(ACCESS_TOKEN, '5.130');

//массив с файлами\ссылками на файлы
$links =
    [
        /**
         * Видео из сети
         */
        'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4' => ['add_to_news' => 1],

        /**
         * Локальное
         */
        'sample-short.mp4' => ['add_to_news' => 1]
    ];

/**
 * output
 *
 * array(3) {
 * [0] =>
 * string(25) "story-200599231_456239080"
 * [1] =>
 * string(25) "story-200599231_456239082"
 * }
 */
$data = SimpleVKExtend::createMultiplyStories($vk, $links);
```

#### 3.2. Множественная загрузка видео
```php
declare(strict_types=1);

const ACCESS_TOKEN = '';
$vk = SimpleVK::create(ACCESS_TOKEN, '5.130');

$links =
    [
        /**
         * Видео из сети
         */
        'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4' => ['name' => 'video1'],

        /**
         * Локальное
         */
        'sample-short.mp4' => ['name' => 'video2']
    ];

/**
 * output
 * array(2) {
 * [0] =>
 * string(24) "video259166248_456241850"
 * [1] =>
 * string(24) "video259166248_456241851"
 * }
 */
$data = SimpleVKExtend::uploadMultiplyVideo($vk, $links);
```

#### 3.3. Загрузка видео
```php
declare(strict_types=1);

const ACCESS_TOKEN = '';
$vk = SimpleVK::create(ACCESS_TOKEN, '5.130');

/**
 * output
 * video259166248_456241851
 */
echo SimpleVKExtend::uploadVideo($vk, 'sample-short.mp4', 'testVideo')
```

#### 3.4. Загрузка истории
```php
declare(strict_types=1);

const ACCESS_TOKEN = '';
$vk = SimpleVK::create(ACCESS_TOKEN, '5.130');

/**
 * output
 * story-200599231_456239082
 */
echo SimpleVKExtend::createStories($vk, 'sample-short.mp4', 1)
```
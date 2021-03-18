# PHP-VK-BOT

Личная расширение для SimpleVK 3

___

## Содержание

1. [Установка](#1-установка)
3. [Методы](#2-методы)

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
     *          $data =
     *      [
     *            $direct_link_or_local_file_path1 =>
     *               ['name' => 'cat', 'description' => 'my cat' /* и так далее */ ],
     *           
     *           $direct_link_or_local_file_path2 =>
     *               [ 'name' => 'dog', 'description' => 'my dog'  /* и так далее */ ] 
     *       ];
     */
    public static function uploadMultiplyVideo(SimpleVK $vk, array $data): array|false
	
	/**
     * Загрузить историю
     * История не должна быть длиннее 15 секунд
     */
    public static function createStories(SimpleVK $vk, string $file, int $add_to_news, string|int $user_ids = null, int $reply_to_story = null, string $link_text = null, string $link_url = null, int $group_id = null, string $clickable_stickers = null): string|false
	
	/**
     * Загрузить множество историй асинхронно
     *          $data =
     *      [
     *            $direct_link_or_local_file_path1 =>
     *               ['add_to_news' =>  1 /* и так далее */ ],
     *           
     *           $direct_link_or_local_file_path2 =>
     *               ['add_to_news' =>  1 /* и так далее */ ],
     *       ];
     */
    public static function createMultiplyStories(SimpleVK $vk, array $data): false|array
	
	 /**
     * Получить всех менеджеров в группе
     */
    public static function getManagersGroup(SimpleVK $vk, int $group_id): array|false
```

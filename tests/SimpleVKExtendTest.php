<?php
declare(strict_types=1);

use DigitalStars\SimpleVK\SimpleVK;
use Labile\SimpleVKExtend\SimpleVKExtend;
use PHPUnit\Framework\TestCase;

class SimpleVKExtendTest extends TestCase
{
    private string $local_file = __DIR__ . '/sample-short.mp4';
    private array $web_file =
        [
            'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4',
            'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
            'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
            'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4'
        ];


    private SimpleVK $user_auth;
    private int $group_id;

    protected function setUp(): void
    {
        $user_token = '';
        $this->group_id = 0;
        $this->user_auth = SimpleVK::create($user_token, '5.130');
    }

    public function testUploadVideo(): void
    {
        self::assertIsString(SimpleVKExtend::uploadVideo($this->user_auth, $this->local_file, 'testVideo'));
    }

    public function testCreateMultiplyStories(): void
    {
        foreach ($this->web_file as $direct_link) {
            $stories[$direct_link]['add_to_news'] = 1;
        }
        self::assertIsArray(SimpleVKExtend::createMultiplyStories($this->user_auth, $stories));
    }

    public function testIsManagerGroup(): void
    {
        self::assertIsArray(SimpleVKExtend::getManagersGroup($this->user_auth, $this->group_id));
    }

    public function testCreateStories(): void
    {
        self::assertIsString(SimpleVKExtend::createStories($this->user_auth, $this->local_file, 1));
    }

    public function testUploadMultiplyVideo(): void
    {
        $i = 0;
        foreach ($this->web_file as $direct_link) {
            $video[$direct_link]['name'] = 'video ' . $i++;
        }
        self::assertIsArray(SimpleVKExtend::uploadMultiplyVideo($this->user_auth, $video));
    }

}

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

    private int $group_id = ;
    private const USER_TOKEN = '';

    private SimpleVK $user_auth;

    protected function setUp(): void
    {
        $this->user_auth = SimpleVK::create(self::USER_TOKEN, '5.130');
    }

    public function testOpenLocalFile(): void
    {
        self::assertIsString(SimpleVKExtend::openLocalFile($this->local_file));
    }

    public function testCreateMultipart(): void
    {
        self::assertIsArray(SimpleVKExtend::createMultipart($this->local_file, 'video_file'));
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

    public function testOpenWebFile(): void
    {
        self::assertIsString(SimpleVKExtend::openWebFile($this->web_file[0]));
    }
}

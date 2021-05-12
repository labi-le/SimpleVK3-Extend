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
        $user_token = 'slut';
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

    public function testParse()
    {
        $garbage = '{"type": "message_new", "object": {"message": {"from_id": 418618, "id": 2222222222222, "peer_id": 2000000003, "text": "I LoVe WoMaN", "conversation_message_id": 218, "action": {"type": "chat_create", "text": "test"}, "fwd_messages": [], "attachments": []}}, "group_id": 777}';
        $structure_garbage = json_decode($garbage, true);

        $new_backup_data = $structure_garbage;

        $structure_garbage['object'] = $structure_garbage['object']['message'];
        $new_data = $structure_garbage;

        $SimpleVK = SimpleVK::create('', '');
        $reflectionSimpleVK = new ReflectionClass($SimpleVK);

        $data = $reflectionSimpleVK->getProperty('data');
        $data->setAccessible(true);
        $data->setValue($SimpleVK, $new_data);

        $data_backup = $reflectionSimpleVK->getProperty('data_backup');
        $data_backup->setAccessible(true);
        $data_backup->setValue($SimpleVK, $new_backup_data);

        SimpleVKExtend::parse($SimpleVK);

        self::assertIsInt( SimpleVKExtend::getVars('group_id'));
        self::assertIsInt(SimpleVKExtend::getVars('peer_id'));
        self::assertIsInt(SimpleVKExtend::getVars('chat_id'));
        self::assertIsInt(SimpleVKExtend::getVars('user_id'));
        self::assertIsString(SimpleVKExtend::getVars('type'));
        self::assertIsString(SimpleVKExtend::getVars('text'));
        self::assertIsString(SimpleVKExtend::getVars('text_lower'));
        self::assertNull(SimpleVKExtend::getVars('payload'));
        self::assertIsArray(SimpleVKExtend::getVars('action'));
        self::assertIsInt(SimpleVKExtend::getVars('message_id'));
        self::assertIsInt(SimpleVKExtend::getVars('conversation_message_id'));
        self::assertIsArray(SimpleVKExtend::getVars('attachments'));
        self::assertIsArray(SimpleVKExtend::getVars('fwd_messages'));
        self::assertIsArray(SimpleVKExtend::getVars('reply_message'));
    }

}

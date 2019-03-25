<?php
/**
 * @author Jacob R. Schofield
 * @copyright HelpSocial
 */

namespace Sp4ceb4r\GnipDataGenerator\Testing\Generators;

use Illuminate\Support\Arr;
use Sp4ceb4r\GnipDataGenerator\Generators\PowerTrackGenerator;

/**
 * Class PowerTrackGeneratorTest.
 */
class PowerTrackGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PowerTrackGenerator
     */
    protected $generator;

    public function setUp()
    {
        parent::setUp();

        $this->generator = new PowerTrackGenerator();
    }

    public function test_actor_no_exception()
    {
        $actor = $this->generator->actor();

        $this->verifyNoTemplating($actor);
    }

    public function test_tweet_no_exception()
    {
        $tweet = $this->generator->tweet();

        $this->verifyNoTemplating($tweet);
    }

    public function test_reply_no_exception()
    {
        $parent = $this->generator->tweet();
        $reply = $this->generator->reply(null, $parent);

        $this->verifyNoTemplating($reply);

        $this->assertNotEmpty(Arr::get($reply, 'twitter_entities.user_mentions', []));
        $this->assertNotEquals(
            Arr::get($reply, 'object.link'),
            Arr::get($reply, 'inReplyTo.link')
        );

        $this->assertRegExp('/@[^ ]+ .*/', $reply['body']);
        $this->assertEquals(
            "@{$parent['actor']['preferredUsername']}",
            substr(
                $reply['body'],
                $start = Arr::get($reply, 'twitter_entities.user_mentions.0.indices.0', 0),
                Arr::get($reply, 'twitter_entities.user_mentions.0.indices.1', 0) - $start
            )
        );
    }

    public function test_retweet_no_exception()
    {
        $original = $this->generator->tweet();
        $retweet = $this->generator->retweet(null, $original);
        
        $this->assertEquals("RT @{$original['actor']['preferredUsername']}: {$original['body']}", $retweet['body']);
        $this->assertEquals(
            "@{$original['actor']['preferredUsername']}",
            substr(
                $retweet['body'],
                $start = Arr::get($retweet, 'twitter_entities.user_mentions.0.indices.0', 0),
                Arr::get($retweet, 'twitter_entities.user_mentions.0.indices.1', 0) - $start
            )
        );

        $this->verifyNoTemplating($retweet);
    }

    public function test_quote_no_exception()
    {
        $original = $this->generator->tweet();
        $retweet = $this->generator->quote(null, $original);

        $this->assertEquals($original, $retweet['twitter_quoted_status']);

        $this->verifyNoTemplating($retweet);
    }

    public function verifyNoTemplating(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $this->assertNotRegExp('/{[^}]+}/', $value);
            } elseif (is_array($value)) {
                $this->verifyNoTemplating($value);
            }
        }
    }
}

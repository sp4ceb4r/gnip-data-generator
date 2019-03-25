<?php
/**
 * @author Jacob R. Schofield
 * @copyright HelpSocial
 */

namespace Sp4ceb4r\GnipDataGenerator\Generators;

use Carbon\Carbon;
use Faker\Factory;
use Sp4ceb4r\GnipDataGenerator\Providers\DateTimeProvider;

/**
 * Class PowerTrackGenerator.
 */
class PowerTrackGenerator
{
    protected $generator;

    public function actor()
    {
        $actor = $this->fill($this->readStub('actor'));

        $actor['preferredUsername'] = $username = $this->getGenerator()->userName;
        $actor['displayName'] = $this->getGenerator()->name;

        $id = $this->getGenerator()->twitterUserId();

        $actor['id'] = $this->getGenerator()->gnipTwitterUserId($id);
        $actor['link'] = $this->getGenerator()->twitterProfileUrl($username);
        $actor['image'] = $this->getGenerator()->twitterAvatarUrl($id);

        return $actor;
    }

    public function tweet($actor = null, $location = false)
    {
        $tweet = $this->fill($this->readStub('tweet'));

        $tweet['actor'] = $actor = ($actor ?: $this->actor());

        if (!$location) {
            $tweet['location'] = $tweet['object']['location'] = null;
        }

        $id = $this->getGenerator()->twitterStatusId();

        $tweet['id'] = $this->getGenerator()->gnipTagSearchId($id);
        $tweet['link'] = $this->getGenerator()->twitterStatusUrl($actor['preferredUsername'], $id);

        $tweet['retweetCount'] = 0;

        $tweet['object'] = [
            'objectType' => 'note',
            'id' => $this->getGenerator()->gnipObjectSearchId($id),
            'summary' => $tweet['body'],
            'link' => $tweet['link'],
            'postedTime' => $tweet['postedTime'],
        ];

        if (!isset($tweet['gnip'])) {
            $tweet['gnip'] = [];
        }
        $tweet['gnip']['matching_rules'] = [$this->getGenerator()->matchingRule()];

        return $tweet;
    }

    public function reply($actor = null, $parent = null, $location = false)
    {
        $parent = $parent ?: $this->tweet();
        $parentActor = $parent['actor'];

        $reply = $this->tweet($actor, $location);

        // Update reply posted time to ensure after parent
        $dt = $this->first(explode('.', $parent['postedTime'])).'+0000';
        $postedTime = Carbon::createFromFormat(\DateTime::ATOM, $dt)->addSeconds(mt_rand(1, 60 * 60));
        $reply['postedTime'] = $reply['object']['postedTime'] = DateTimeProvider::format($postedTime);

        // Update reply text to include reply context
        $text = "@{$parentActor['preferredUsername']} ".$this->getGenerator()->uuid;
        $reply['body'] = $text;
        $reply['display_text_range'] = $reply['displayTextRange'] = [
            strlen($parentActor['preferredUsername']) + 2,
            strlen($text),
        ];

        // Set inReplyTo
        $reply['inReplyTo'] = [
            'link' => $parent['link'],
            'type' => null,
        ];

        // Set reply object
        $reply['object'] = [
            'objectType' => 'note',
            'id' => $this->getGenerator()->gnipObjectSearchId($this->last(explode(':', $reply['id']))),
            'summary' => $reply['body'],
            'link' => $reply['link'],
            'postedTime' => $reply['postedTime'],
        ];

        // Set reply context user mentions
        $userMentions = [ $this->userMention($text, $parentActor) ];
        $reply['twitter_entities']['user_mentions'] = $reply['twitter_entities']['userMentions'] = $userMentions;
        $reply['twitterEntities']['user_mentions'] = $reply['twitterEntities']['userMentions'] = $userMentions;

        return $reply;
    }

    public function retweet($actor = null, $original = null)
    {
        $original = $original ?: $this->tweet();
        $retweet = $this->tweet($actor);

        $retweet['retweetCount'] = $this->getGenerator()->shares();

        // Update retweet posted time to ensure after original
        $dt = $this->first(explode('.', $original['postedTime'])).'+0000';
        $postedTime = Carbon::createFromFormat(\DateTime::ATOM, $dt)->addSeconds(mt_rand(1, 60 * 60));
        $retweet['postedTime'] = DateTimeProvider::format($postedTime);

        // Update reply text to include retweet target
        $text = "RT @{$original['actor']['preferredUsername']}: {$original['body']}";
        $retweet['body'] = $text;

        $retweet['object'] = $original;

        // Set the user mention
        $userMentions = [$this->userMention($text, $original['actor'])];
        $retweet['twitter_entities']['user_mentions'] = $retweet['twitter_entities']['userMentions'] = $userMentions;
        $retweet['twitterEntities']['user_mentions'] = $retweet['twitterEntities']['userMentions'] = $userMentions;

        return $retweet;
    }

    public function quote($actor = null, $original = null)
    {
        $original = $original ?: $this->tweet();
        $quote = $this->tweet($actor);

        // Update retweet posted time to ensure after original
        $dt = $this->first(explode('.', $original['postedTime'])).'+0000';
        $postedTime = Carbon::createFromFormat(\DateTime::ATOM, $dt)->addSeconds(mt_rand(1, 60 * 60));
        $retweet['postedTime'] = DateTimeProvider::format($postedTime);

        $quote['display_text_range'] = $quote['displayTextRange'] = [0, strlen($quote['body'])];

        // Update quote text to have an attachment url
        $attachmentUrl = $this->getGenerator()->tcoUrl();

        $quote['body'] = $text = $quote['body'].$attachmentUrl;;

        $quote['twitter_quoted_status'] = $quote['twitterQuotedStatus'] = $original;

        // Set the twitter_entities urls
        $twitterUrls = [
            [
                'expanded_url' => $original['link'],
                'expandedUrl' => $original['link'],
                'display_url' => $displayUrl = substr(explode('/status/', $original['link'])[0].json_decode('"\u3306"'), 8),
                'displayUrl' => $displayUrl,
                'url' => $attachmentUrl,
                'indices' => [$quote['display_text_range'][1] + 1, strlen($quote['body'])]
            ],
        ];
        $quote['twitter_entities']['urls'] = $quote['twitter_entities']['urls'] = $twitterUrls;
        $quote['twitterEntities']['urls'] = $quote['twitterEntities']['urls'] = $twitterUrls;

        if (!isset($quote['gnip'])) {
            $quote['gnip'] = [];
        }
        if (!isset($quote['gnip']['urls'])) {
            $quote['gnip']['urls'] = [];
        }
        $quote['gnip']['urls'][0] = $this->getGenerator()->gnipExpandedUrl($attachmentUrl, $original['link']);

        return $quote;
    }

    public function imageMedia($attachmentUrl, $status, $index = 1)
    {
        $id = $this->getGenerator()->twitterId();
        $start = mb_strpos($status['body'], $attachmentUrl);

        return [
            'id' => $id,
            'id_str' => (string) $id,
            'indices' => [
                $start,
                $start + strlen($attachmentUrl),
            ],
            'media_url' => preg_replace('/^https/', 'http', $link = $this->getGenerator()->twitterImageUrl()),
            'media_url_https' => $link,
            'url' => $attachmentUrl,
            'display_url' => 'pic.twitter.com/'.$this->last(explode('/', $attachmentUrl)),
            'expanded_url' => $status['link'].'/photo/'.(string) $index,
            'type' => 'photo',
            'sizes' => [
                'media' => [
                    'w' => 300,
                    'h' => 300,
                    'resize' => 'fit',
                ],
                'thumb' => [
                    'w' => 150,
                    'h' => 150,
                    'resize' => 'crop',
                ],
                'small' => [
                    'w' => 200,
                    'h' => 200,
                    'resize' => 'fit',
                ],
                'large' => [
                    'w' => 400,
                    'h' => 400,
                    'resize' => 'fit',
                ],
            ]
        ];
    }

    public function userMention($text, $actor)
    {
        $username = $actor['preferredUsername'];
        $userId = (int) $this->last(explode(':', $actor['id']));

        $start = mb_strpos($text, $username) - 1;
        if ($start < 0) {
            $start = 0;
        }

        return [
            'id' => $userId,
            'name' => $actor['displayName'],
            'id_str' => (string) $userId,
            'idStr' => $userId,
            'screen_name' => $username,
            'screenName' => $username,
            'indices' => [$start, $start + 1 + strlen($username)],
        ];
    }

    public function withGenerator(\Faker\Generator $generator)
    {
        $this->generator = $generator;

        return $this;
    }

    protected function fill(array $template)
    {
        foreach ($template as $key => $value) {
            if (is_array($value)) {
                $value = $this->fill($value);
            } elseif (is_string($value) && preg_match('/({[^}]+})/', $value)) {
                $value = preg_replace_callback('/({[^}]+})/', function ($matches) {
                    list($method, $args) = $this->parseFormatter($this->camel(trim($matches[1], '{}')));

                    if (empty($args)) {
                        return $this->getGenerator()->{$method};
                    }

                    return $this->getGenerator()->{$method}(...$args);
                }, $value);
            }

            $template[$key] = $value;
        }

        return $template;
    }

    /**
     *
     * @return \Faker\Generator
     */
    protected function getGenerator()
    {
        if (isset($this->generator)) {
            return $this->generator;
        }

        $this->generator = Factory::create();

        $providersDir = __DIR__.'/../Providers/';
        foreach (scandir($providersDir) as $filename) {
            if (!is_file($providersDir.$filename)) {
                continue;
            }

            $ns = 'Sp4ceb4r\\GnipDataGenerator\\Providers\\';
            $classname = $ns.substr($filename, 0, strlen($filename) - 4);

            if (!class_exists($classname, true)) {
                // warning?
                continue;
            }

            $this->generator->addProvider(new $classname($this->generator));
        }

        return $this->generator;
    }

    /**
     *
     * @param string $name
     * @return array
     */
    protected function readStub($name)
    {
        $path = __DIR__.'/../../stubs/powertrack/'.$name.'.php';
        if (!file_exists($path)) {
            throw new \LogicException('Stub not found.');
        }

        return require $path;
    }

    private function parseFormatter($formatter)
    {
        list($name, $options) = array_pad(explode(':', $formatter), 2, '');

        if (empty($options)) {
            return [$name, null];
        }

        return [$name, explode(',', $options)];
    }

    private function first(array $array = [])
    {
        foreach ($array as $item) {
            return $item;
        }

        return null;
    }

    private function last(array $array = [])
    {
        return $this->first(array_reverse($array));
    }

    private function camel($value)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value))));
    }
}

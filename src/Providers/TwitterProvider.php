<?php
/**
 * @author Jacob R. Schofield
 * @copyright HelpSocial
 */

namespace sp4ceb4r\GnipDataGenerator\Providers;

use Faker\Provider\Base;

/**
 * Class TwitterProvider.
 */
class TwitterProvider extends Base
{
    public function friends($min = 1, $max = 1000)
    {
        return $this->generator->numberBetween($min, $max);
    }

    public function followers()
    {
        return $this->generator->numberBetween(1, 100000);
    }

    public function listed()
    {
        return $this->generator->numberBetween(1, 10);
    }

    public function twitterAvatarUrl($id)
    {
        return "https://pbs.twimg.com/profile_images/$id/".bin2hex(openssl_random_pseudo_bytes(4)).'_normal.jpeg';
    }

    public function twitterProfileUrl($username)
    {
        return 'https://twitter.com/'.$username;
    }

    public function twitterStatusUrl($username, $id)
    {
        return 'https://twitter.com/'.$username.'/statuses/'.$id;
    }

    public function twitterStatusId()
    {
        return $this->twitterId(16);
    }

    public function twitterUserId()
    {
        return $this->twitterId(8);
    }

    public function tcoUrl()
    {
        return "https://t.co/{$this->shortCode()}";
    }

    public function twitterImageUrl()
    {
        return "https://pbs.twimg.com/media/{$this->shortCode()}.jpg";
    }

    protected function shortCode($length = 8)
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $this->generator->randomAscii;
        }

        return $code;
    }

    protected function twitterId($digits = 16)
    {
        $idStr = '';
        for ($i = 0; $i < $digits; $i++) {
            $digit = $this->generator->randomDigitNotNull;
            if ($i === 0) {
                while ($digit === 0) {
                    $digit = $this->generator->randomDigitNotNull;
                }
            }

            $idStr .= (string) $digit;
        }

        return (int) $idStr;
    }
}

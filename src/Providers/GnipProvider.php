<?php
/**
 * @author Jacob R. Schofield
 * @copyright HelpSocial
 */

namespace Sp4ceb4r\GnipDataGenerator\Providers;

use Faker\Provider\Base;

/**
 * Class GnipProvider.
 */
class GnipProvider extends Base
{
    public function gnipTagSearchId($id)
    {
        return $this->gnipSearchId($id, 'tag');
    }

    public function gnipObjectSearchId($id)
    {
        return $this->gnipSearchId($id, 'object');
    }

    public function gnipSearchId($id, $search = 'object')
    {
        return "{$search}:search.twitter.com,2005:{$id}";
    }

    public function gnipTwitterUserId($id)
    {
        return 'id:twitter.com:'.$id;
    }

    public function matchingRule($id = null, $tag = null)
    {
        return [
            'tag' => (string) $tag ?: $this->generator->randomNumber(4),
            'value' => null,
            'id' => $this->longInt(),
        ];
    }

    public function gnipExpandedUrl($url, $expandedUrl)
    {
        return [
            'expanded_url' => $expandedUrl,
            'expanded_status' => 200,
            'expanded_url_title' => $this->generator->words(3, true),
            'expanded_url_description' => $this->generator->sentence,
            'url' => $url,
            'expandedUrl' => $expandedUrl,
            'expandedStatus' => 200,
            'expandedUrlTitle' => $this->generator->words(3, true),
            'expandedUrlDescription' => $this->generator->sentence,
        ];
    }

    protected function longInt($digits = 16)
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

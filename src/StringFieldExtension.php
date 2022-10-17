<?php

namespace Bigfork\SilverStripeSimpleSEO;

use SilverStripe\Core\Extension;


class StringFieldExtension extends Extension
{
    private static $casting = array(
        'LimitCharactersToClosestWordHTML' => 'Text'
    );

    /**
     * Limit characters to the closest word, but include HTML tags
     *
     * @param int $limit Number of characters to limit by
     * @param string $add Ellipsis to add to the end of truncated string
     * @return string
     */
    public function LimitCharactersToClosestWordHTML($limit = 20, $add = '...')
    {
        $value = trim($this->owner->RAW() ?? '');

        if (mb_strlen($value) > $limit) {
            $value = mb_substr($value, 0, $limit);
            $value = rtrim(mb_substr($value, 0, mb_strrpos($value, ' ')), "/[\.,-\/#!$%\^&\*;:{}=\-_`~()]\s") . $add;
        }

        return $value;
    }
}

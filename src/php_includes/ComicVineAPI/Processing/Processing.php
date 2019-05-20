<?php
/**
 * Created by ahahn94
 * on 19.05.19
 */

/**
 * Class Processing
 * Implements functions for the processing of data returned by the ComicVine API.
 */
class Processing
{

    /**
     * Fix the links inside text.
     * If they start with a single "/", they will point to the local server, while they really should point to the
     * ComicVine server. Thus, replacing "/" with "https://comicvine.gamespot.com".
     * Some links start with "//", which would collide with the fix for the single "/" problem, so replacing that with
     * "https://" first ("//" essentially means "use current protocol - so either http or https).
     * The string replacement will also make the links open in a new tab.
     * @param $text string String containing html links obtained from the ComicVine API.
     * @return string Same string as the input, but with fixed link.
     */
    static function fixURLs($text)
    {
        // Fix "//" problem.
        $fixedDoubleSlash = str_replace('href="//', 'href="https://', $text);
        // Fix "/" problem.
        $fixedSingleSlash = str_replace('href="/', 'href="https://comicvine.gamespot.com/',
            $fixedDoubleSlash);
        // Make links open in new tab and return fixed string.
        return str_replace('href="', 'target="_blank" rel="noopener noreferrer" href="', $fixedSingleSlash);
    }
}
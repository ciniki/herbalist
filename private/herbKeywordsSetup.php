<?php
//
// Description
// -----------
// This method will add a new herb for the business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to add the herb to.
//
// Returns
// -------
//
function ciniki_herbalist_herbKeywordsSetup(&$ciniki, $keywords) {
    
    $common_words = array(
        'a', 'i',
        'an', 'on', 'in',
        'and', 'the', 'for', 'any', 'are', 'but', 'not', 'was', 'our', 
        'all', 'has', 'use', 'too', 'put', 'let', 'its', "it's", 
        'they', "they're", 'there', 'their');

    //
    // Setup the keywords_index field
    //
    $keywords = preg_replace('/[^a-zA-Z0-9]/', ' ', $keywords);
    $keywords = preg_replace('/\s\s/', ' ', $keywords);
    $keywords = strtolower($keywords);
    $words = explode(' ', $keywords);

    //
    // Remove 2 letter words, and common words
    //
    foreach($words as $wid => $word) {
        if( strlen($word) < 3 ) {
            unset($words[$wid]);
        }
        if( in_array($word, $common_words) ) {
            unset($words[$wid]);
        }
    }

    //
    // Sort the words
    //
    sort($words);

    //
    // Remove duplicates, and join into single string
    //
    $keywords = implode(' ', array_unique($words));

    return array('stat'=>'ok', 'keywords'=>$keywords);
}
?>

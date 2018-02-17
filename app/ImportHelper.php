<?php
declare(strict_types=1);

namespace App;

class ImportHelper
{
    public static function makeTableOfContents(\DOMDocument $doc)
    {
        // create document fragment
        $frag = $doc->createDocumentFragment();
        // create initial list
        $frag->appendChild($doc->createElement('ol'));
        $head = &$frag->firstChild;
        $xpath = new \DOMXPath($doc);
        $last = 1;

        // get all H1, H2, …, H6 elements
        foreach ($xpath->query('//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]') as $headline) {
            // get level of current headline
            sscanf($headline->tagName, 'h%u', $curr);

            // move head reference if necessary
            if ($curr < $last) {
                // move upwards
                for ($i=$curr; $i<$last; $i++) {
                    $head = &$head->parentNode->parentNode;
                }
            } else if ($curr > $last && $head->lastChild) {
                // move downwards and create new lists
                for ($i=$last; $i<$curr; $i++) {
                    $head->lastChild->appendChild($doc->createElement('ol'));
                    $head = &$head->lastChild->lastChild;
                }
            }
            $last = $curr;

            // add list item
            $li = $doc->createElement('li');
            $head->appendChild($li);
            $a = $doc->createElement('a', htmlspecialchars(self::trimAndClean($headline->textContent)));
            $head->lastChild->appendChild($a);

            // build ID
            $levels = array();
            $tmp = &$head;
            // walk subtree up to fragment root node of this subtree
            while (!is_null($tmp) && $tmp != $frag) {
                $levels[] = $tmp->childNodes->length;
                $tmp = &$tmp->parentNode->parentNode;
            }
            $id = 'sect'.implode('.', array_reverse($levels));
            // set destination
            $a->setAttribute('href', '#'.$id);
            // add anchor to headline
            $a = $doc->createElement('a');
            $a->setAttribute('name', $id);
            $a->setAttribute('id', $id);
            $headline->insertBefore($a, $headline->firstChild);
        }

        $toc_div = $doc->createElement('div');
        $toc_div->setAttribute('id', 'table_of_contents');

        // append fragment to document
        $toc_header = $doc->createElement('h5');
        $toc_header->nodeValue = 'Table of Contents';
        $toc_div->appendChild($toc_header);
        $toc_div->appendChild($frag);

        return $toc_div;
    }

    public static function trimAndClean(string $text) {
        // Remove redundant whitespace
        $text = preg_replace("/\s\s+/", ' ', $text);

        $text = trim($text);

        return $text;
    }
}

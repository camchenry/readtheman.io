<?php
declare(strict_types=1);

namespace App;

use Symfony\Component\Process\Process;
use Symfony\Component\Finder\Finder;
use Parsedown;

class ImportHelper
{
    /*
     * Map lowercased category names to the real category name
     *
     * @var array[string] = string
     */
    const category_synonyms = [
        "linux user's manual"        => "Linux User's Manual",
        "linux user manual"          => "Linux User's Manual",

        "linux programmer's manual"  => "Linux Programmer's Manual",
        "linux programmer'smanual"   => "Linux Programmer's Manual",
        "linuxprogrammer's manual"   => "Linux Programmer's Manual",

        "library functions manual"   => "Library Functions Manual",

        "user commands"              => "User Commands",

        "linux system calls"         => "Linux System Calls",
        "system calls manual"        => "Linux System Calls",

        "linux key management calls" => "Linux Key Management Calls",

        "linux system administration" => "Linux System Administration",

        "miscellaneous information manual" => "Miscellaneous Information Manual",
    ];

    public static function createPage(array $data)
    {
        $required_fields = [
            'name'                   => 'string',
            'source'                 => 'string',
            'section'                => 'string',
            'category'               => 'string',
            'html'                   => 'string',
            'raw_html'               => 'string',
            'table_of_contents_html' => 'string',
        ];

        // Not required, but still validated
        $validated_fields = [
            'short_description' => 'string',
            'description'       => 'string',
            'os'                => 'string',
            'tldr_html'         => 'string',
            'tldr_description'  => 'string',
        ];

        $nullable_fields = [
            'short_description' => true,
            'description'       => true,
            'os'                => true,
            'tldr_html'         => true,
            'tldr_description'  => true,
        ];

        foreach($required_fields as $field => $type) {
            if (!isset($data[$field])) {
                throw new \Exception("Page field not set: '{$field}'");
            }

            if (empty($data[$field])) {
                throw new \Exception("Page field is empty: '{$field}'");
            }

            if (gettype($data[$field]) !== $type) {
                $found_type = gettype($data[$field]);
                throw new \Exception("Page field expected to be '{$type}' but got: '{$found_type}'");
            }
        }

        foreach($validated_fields as $field => $type) {
            if (isset($data[$field])) {
                if (empty($data[$field]) && !$nullable_fields[$field]) {
                    throw new \Exception("Page field is empty: '{$field}'");
                }

                if (gettype($data[$field]) !== $type) {
                    $found_type = gettype($data[$field]);
                    throw new \Exception("Page field expected to be '{$type}' but got: '{$found_type}'");
                }
            }
        }

        $name                   = self::trimAndClean($data['name']);
        $section                = self::trimAndClean($data['section']);
        $category               = self::trimAndClean($data['category']);
        $html                   = self::trimAndClean($data['html']);
        $raw_html               = self::trimAndClean($data['raw_html']);
        $table_of_contents_html = self::trimAndClean($data['table_of_contents_html']);
        if (isset($data['short_description'])) {
            $short_description = self::trimAndClean($data['short_description']);
        }
        if (isset($data['description'])) {
            $description = self::trimAndClean($data['description']);
        }
        if (isset($data['os'])) {
            $os = self::trimAndClean($data['os']);
        }
        if (isset($data['tldr_html'])) {
            $tldr_html = self::trimAndClean($data['tldr_html']);
        }
        if (isset($data['tldr_description'])) {
            $tldr_description = self::trimAndClean($data['tldr_description']);
        }
        $source                 = self::trimAndClean($data['source']);
        $page_updated_date      = $data['page_updated_date'];

        if (gettype($page_updated_date) === 'object') {
            $page_updated_date = $page_updated_date->format('Y-m-d H:i:s');
        }

        $page = \App\Page::firstOrCreate(
            [
                'name' => $name,
                'source' => $source,
                'section' => $section,
            ],
            [
                'name' => $name,
                'source' => $source,
                'section' => $section,
            ]
        );
        $page->category               = $category;
        $page->processed_html         = $html;
        $page->raw_html               = $raw_html;
        $page->table_of_contents_html = $table_of_contents_html;
        $page->page_updated_at        = $page_updated_date;
        if (!empty($description)) {
            $page->description = $description;
        }
        if (!empty($short_description)) {
            $page->short_description = $short_description;
        }
        if (!empty($os)) {
            $page->os = $os;
        }
        if (!empty($tldr_html)) {
            $page->tldr_html = $tldr_html;
        }
        if (!empty($tldr_description)) {
            $page->tldr_description = $tldr_description;
        }
        $page->save();
    }

    public static function makeHtmlForManPage(string $man_page, string $section, string $man_directory): string
    {
        // Generate HTML
        $process = new Process([
            'mman',
            '-T', 'html',
            '-M', $man_directory,
            $section,
            $man_page
        ]);
        $process->run();

        if (!$process->isSuccessful())
        {
            throw new \Exception($process->getErrorOutput());
        }

        $html = $process->getOutput();

        return $html;
    }

    public static function createSectionedDocument(string $html): \DOMDocument
    {
        $doc = new \DOMDocument;
        $doc->loadXML($html);

        // Strip out everything but the HTML in the <body> tag
        // and add sectioning elements
        $doc_body_only = new \DOMDocument;
        $body = $doc->getElementsByTagName('body')->item(0);
        $root_div = null;
        foreach($body->childNodes as $child) {
            if ($child->nodeName === 'div') {
                $root_div = $child;
                continue;
            }
            else {
                $doc_body_only->appendChild($doc_body_only->importNode($child, true));
            }
        }

        // Add sectioning elements and rearrange section IDs
        if ($root_div) {
            $in_section = false;
            $section_number = 0;
            $sections = [];

            foreach($root_div->childNodes as $sub_child) {
                if ($sub_child->nodeName === 'h1') {
                    $in_section = true;
                    $section_number++;
                    $sections[$section_number] = [
                        'children' => [],
                        'id' => trim($sub_child->getAttribute('id')),
                    ];
                    $sub_child->removeAttribute('id');
                }
                if ($in_section) {
                    $sections[$section_number]['children'][] = $doc_body_only->importNode($sub_child, true);
                }
            }

            $root_div = $doc_body_only->importNode($root_div);
            $doc_body_only->appendChild($root_div);

            foreach($sections as $_section) {
                $parent_div = $doc_body_only->createElement('section');
                $parent_div->setAttribute('id', $_section['id']);
                $root_div->appendChild($parent_div);
                foreach($_section['children'] as $child) {
                    $parent_div->appendChild($child);
                }
            }
        }

        return $doc_body_only;
    }

    public static function makeTableOfContents(\DOMDocument $doc): \DOMElement
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

    public static function addLinksToManPages(\DOMDocument &$doc, string $self_page_name)
    {
        $bolded = $doc->getElementsByTagName('b');
        for($i = 0; $i < $bolded->length; $i++) {
            $bold = $bolded->item($i);
            $text = trim($bold->textContent);

            if (empty($text)) {
                continue;
            }

            if ($text === $self_page_name) {
                continue;
            }

            if (Page::where('name', '=', $text)->exists()) {
                $page = Page::where('name', '=', $text)->first();
                $link = $doc->createElement('a');
                $link->textContent = $text;
                $link->setAttribute('href', $page->getUrl());
                $bold->textContent = '';
                $bold->appendChild($link);
            }
        }

        return $doc;
    }

    public static function extractInfo(string $html): array {
        $data = array();

        $dom = new \PHPHtmlParser\Dom();
        $dom->load($html);

        $category = $dom->find('.head-vol')->text;
        if (isset(self::category_synonyms[strtolower($category)]))
        {
            $category = self::category_synonyms[strtolower($category)];
        }
        $data['category'] = $category;

        $short_description = $dom->getElementById('#NAME')->text(true);
        if ($short_description) {
            $short_description = trim(preg_replace('/NAME/', '', $short_description));
            $short_description = trim(preg_replace('/&mdash;/', '-', $short_description));
            preg_match('/^(.*)\s+\-\s+(.*)$/', $short_description, $matches);
            $data['related_pages'] = trim($matches[1]);
            $data['short_description'] = ucfirst(trim($matches[2]));
        }

        $description_section = $dom->getElementById('#DESCRIPTION');
        if ($description_section) {
            $description = $description_section->text(true);
            $description = preg_replace('/DESCRIPTION/', '', $description);
            $data['description'] = $description;
        }


        // Extract the last updated time
        $updated_at = $dom->find('.foot-date')->text;
        $time_zone = new \DateTimeZone('UTC');
        $updated_at_date = \DateTime::createFromFormat('Y-m-d', $updated_at, $time_zone);

        // Prev date format failed, try a different one
        if (!$updated_at_date) {
            $updated_at_date = \DateTime::createFromFormat('F j, Y', $updated_at, $time_zone);
        }

        if (!$updated_at_date) {
            $updated_at_date = \DateTime::createFromFormat('Y', $updated_at, $time_zone);

            if ($updated_at_date) {
                $updated_at_date->setDate((int)$updated_at, 1, 1);
            }
        }

        $data['page_updated_date'] = $updated_at_date;

        $os = $dom->find('.foot-os')->text;
        $data['os'] = $os;

        return $data;
    }

    /*
     * Get the TL;DR text for a given user command. Returns null if text cannot
     * be found in the repository.
     *
     * Throws an exception if the section is not '1'.
     *
     * @return string|null
     */
    public static function getTldr(string $man_page, string $section, bool $fetch_repository = true): ?string
    {
        if ($section !== '1') {
            throw new \Exception("Could not get TL;DR for '{$man_page}': not a user command (section 1).");
        }

        $directory = storage_path() . '/man_pages/tldr';
        if ($fetch_repository) {
            $github_url = 'https://github.com/tldr-pages/tldr';
            if (!file_exists($directory)) {
                echo "Fetching tldr git repository ({$github_url})\n";
                $repository = \Gitonomy\Git\Admin::cloneTo($directory, $github_url, false);
            }
            else {
                $process = new Process("sudo su && cd $directory && git pull");
                $process->run();

                if (!$process->isSuccessful())
                {
                    exit($process->getErrorOutput());
                }
            }
        }

        $finder = new Finder();
        $finder->files()
            ->in($directory . '/pages')
            ->name("/^{$man_page}\.md$/");

        echo "Searching for files in {$directory}/pages\n";

        // @TODO: What about multiple matches?
        foreach($finder as $file) {
            $filename = $file->getFilename();
            $file_contents = $file->getContents();

            $parsedown = new Parsedown();

            $tldr_html = $parsedown->text($file_contents);

            // Remove the <h1> title tag
            $tldr_html = mb_eregi_replace("<\s*h1\s*[^>]*>(.*?)</\s*h1\s*>", '', $tldr_html);

            return $tldr_html;
        }

        return null;
    }

    /*
     * Gets the TL;DR description for a command from the generated HTML
     *
     * @return string|null
     */
    public static function getTldrDescription(string $tldr_html): ?string
    {
        $doc = new \DOMDocument;
        $doc->loadHTML($tldr_html);

        $blockquote = $doc->getElementsByTagName('blockquote')->item(0);
        foreach($blockquote->childNodes as $child) {
            if ($child->nodeName === 'p') {
                return self::trimAndClean($child->textContent);
            }
        }

        return null;
    }

    public static function trimAndClean(string $text): string {
        // Remove redundant whitespace
        $text = preg_replace("/\s\s+/", ' ', $text);

        $text = trim($text);

        return $text;
    }
}

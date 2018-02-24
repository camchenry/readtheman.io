<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class IndexAlgolia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'algolia:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push man page records to Algolia';

    protected $indexName = 'live_man_pages';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new \AlgoliaSearch\Client(env('ALGOLIA_APP_ID'), env('ALGOLIA_ADMIN_KEY'));

        $index = $client->initIndex($this->indexName);

        $index->setSettings([
            'attributesForFaceting' => [
                'section',
                'category',
                'os',
                'source'
            ],
            'attributesToHighlight' => [
                'name',
                'short_description',
                'description',
            ],
            'attributesToSnippet' => [
                'description:50'
            ],
            'snippetEllipsisText' => '…',
        ]);

        $pages = \App\Page::get();
        $records = [];
        foreach($pages as $page) {
            $text = $page->description;

            // @Incomplete @TODO: Combine See Also + Name + Description
            // Get first N words, because most of the important
            // text is contained in the name + description at the
            // beginning of a page
            $excerpt = $this->getSnippet($text, 150);
            $excerpt = str_replace("NAME\n", '', $excerpt);
            $excerpt = str_replace("DESCRIPTION\n", '', $excerpt);
            $excerpt = str_replace("SYNOPSIS\n", '', $excerpt);

            $record = [
                'objectID'          => $page->id,
                'name'              => trim($page->name),
                'section'           => (int)$page->section,
                'category'          => trim($page->category),
                'updated'           => $page->page_updated_at->timestamp,
                'description'       => $excerpt,
                'short_description' => trim($page->short_description),
                'source'            => trim($page->source),
            ];

            if ($page->os !== null) {
                $record['os'] = trim($page->os);
            }

            $records[] = $record;
        }
        foreach($records as $record)
        {
            //print_r($record);
        }
        /* $index->addObjects($records); */
    }

    public function getSnippet($text, $wordCount = 10)
    {
        return implode(
            '',
            array_slice(
                preg_split(
                    '/([\s,\.;\?\!]+)/',
                    $text,
                    $wordCount*2+1,
                    PREG_SPLIT_DELIM_CAPTURE
                ),
                0,
                $wordCount*2-1
            )
        );
    }
}

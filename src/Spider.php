<?php

declare(strict_types=1);

namespace Alejgarciarodriguez\AtomFeed;

use Generator;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use Symfony\Component\DomCrawler\Crawler;

final class Spider extends BasicSpider
{
    public array $startUrls = [
        'https://daily.wordreference.com',
    ];

    public array $itemProcessors = [
        ArticleProcessor::class,
    ];

    public function parse(Response $response): Generator
    {
        $articles = $response->filter('div[id=main] article');

        $articlesData = $articles->each(function (Crawler $article) {
            $title = $article->filter('.post-header')->text();
            $icon = $article->filter('.post-img img')->image()->getUri();
            $link = $article->filter('.post-entry')->filter('a')->link()->getUri();
            $updated = (new \DateTimeImmutable(
                $article->filter('.post-meta')->text()
            ))->format(DATE_ATOM);
            $id = $article->attr('id');
            return compact('id', 'title', 'link', 'updated', 'icon');
        });

        foreach ($articlesData as $articlesDatum) {
            yield $this->item($articlesDatum);
            yield $this->request('GET', $articlesDatum['link'], 'parseArticle');
        }
    }

    public function parseArticle(Response $response): Generator
    {
        $article = $response->filter('div[id=main] article');
        $id = $article->attr('id');
        $summary = $article->filter('.post-entry')->html();
        yield $this->item(compact('id', 'summary'));
    }
}
<?php

declare(strict_types=1);

namespace Alejgarciarodriguez\AtomFeed;

use Doctrine\DBAL\DriverManager;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Support\Configurable;

final class ArticleProcessor implements ItemProcessorInterface
{
    use Configurable;

    public function processItem(ItemInterface $item): ItemInterface
    {
        $conn = DriverManager::getConnection(['url' => DefaultCommand::URL_DB]);

        $conn->executeStatement('INSERT OR IGNORE into articles (id) values (:id)', [
            'id' => $item->get('id'),
        ]);

        if ($item->has('summary')) {
            $conn->executeStatement('UPDATE articles SET summary=:summary WHERE id=:id', [
                'summary' => $item->get('summary'),
                'id' => $item->get('id'),
            ]);
        } else {
            $conn->executeStatement('UPDATE articles 
            SET title=:title, updated=:updated, link=:link, icon=:icon 
                WHERE id=:id', [
                'title' => $item->get('title'),
                'updated' => $item->get('updated'),
                'link' => $item->get('link'),
                'id' => $item->get('id'),
                'icon' => $item->get('icon'),
            ]);
        }

        return $item;
    }
}
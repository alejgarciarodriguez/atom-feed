<?php

declare(strict_types=1);

namespace Alejgarciarodriguez\AtomFeed;

use Doctrine\DBAL\DriverManager;
use RoachPHP\Roach;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class DefaultCommand extends Command
{
    public const URL_DB = 'sqlite:///articles.db';

    protected function configure()
    {
        $this->setName('run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $conn = DriverManager::getConnection(['url' => self::URL_DB]);

        if (!file_exists('articles.db')) {
            $conn->executeStatement(<<<SQL
            CREATE TABLE IF NOT EXISTS articles(
                id text PRIMARY KEY,
                title text,
                updated text,
                summary text,
                link text,
                icon text
            );
            SQL
            );
        }

        Roach::collectSpider(Spider::class);

        $data = $conn->executeQuery('SELECT * from articles')->fetchAllAssociative();

        $loader = new FilesystemLoader( __DIR__ . '/../templates/');
        $twig = new Environment($loader);

        $lastUpdated = (new \DateTime())->format(\DateTime::ATOM);
        $render = $twig->render('atom.twig.xml', ['entries' => $data, 'last_updated' => $lastUpdated]);
        file_put_contents(__DIR__.'/../feed/atom.xml', $render);

        return self::SUCCESS;
    }
}
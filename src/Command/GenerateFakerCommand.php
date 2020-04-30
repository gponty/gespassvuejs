<?php

/*
 * g.ponty@dev-web.io
 */

declare(strict_types=1);

namespace App\Command;

use App\Entity\MotDePasse;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateFakerCommand extends Command
{
    protected static $defaultName = 'generate:faker';

    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Génére des mots de passe aleatoire')
            ->addArgument('nbMotDePasse', InputArgument::OPTIONAL, 'Nombre de mot de passe à générer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nbMotDePasse = $input->getArgument('nbMotDePasse');
        if (!$nbMotDePasse) {
            $nbMotDePasse = 10;
        }

        if (is_numeric($nbMotDePasse)) {
            $faker = Faker\Factory::create('fr_FR');

            for ($i = 0; $i < (int) $nbMotDePasse; ++$i) {
                $motDePasse = new MotDePasse();
                $motDePasse->setUsername($faker->userName);
                $motDePasse->setPassword($faker->password);
                $motDePasse->setNote($faker->realText());
                $motDePasse->setTitre($faker->sentence);
                $motDePasse->setUrl($faker->url);
                $this->manager->persist($motDePasse);
            }

            $this->manager->flush();

            $io->success((int) $nbMotDePasse.' Mot(s) de passe générée');
        } else {
            $io->error('Nombre de mot de passe invalide');
        }

        return 1;
    }
}

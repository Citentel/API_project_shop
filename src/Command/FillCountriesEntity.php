<?php

namespace App\Command;

use App\Entity\Countries;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;

class FillCountriesEntity extends Command
{
    protected static $defaultName = 'entity:country:fill';
    private EntityManagerInterface $entityManager;

    public function __construct(string $name = null, EntityManagerInterface $entityManager)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $arrayCountry = $this->getCountryFromJson();

        if ($arrayCountry == 'false') {
            return Command::FAILURE;
        }

        $arrayCountry = json_decode($arrayCountry, true);

        foreach ($arrayCountry as $item) {
            $this->entityManager->persist((new Countries())->setCode($item['Code'])->setName($item['Name']));
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    /**
     * @return int|mixed
     */
    private function getCountryFromJson(): string
    {
        return file_get_contents(str_replace('src/Command', 'public/json/countryList.json', __DIR__));
    }
}
<?php

namespace App\Command;

use App\Services\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validation;

class Parse extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:parse';
    private $parser;
    private $validator;

    public function __construct(string $name = null, Parser $parser)
    {
        parent::__construct($name);
        $this->parser = $parser;
        $this->validator = Validation::createValidator();
    }

    protected function configure()
    {
        $this->setDescription('This command allows you to parse data from internet');
        $this->addArgument('url', InputArgument::REQUIRED, 'File or web-page url');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');
        $violations = $this->validator->validate($url, new Url());

        if (0 !== count($violations)) {
            foreach ($violations as $violation) {
                $output->writeln("<error>Url argument: " . $violation->getMessage() . "</error>");

                return Command::FAILURE;
            }
        }
        if ($this->parser->getContent()) {
            $output->writeln('Begin to save data');
        } else {
            $output->writeln("<error>There is nothing useful here" . "</error>");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
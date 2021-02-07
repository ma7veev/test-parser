<?php

namespace App\Command;

use App\Parser\CsvParser;
use App\Parser\ParserInterface;
use App\Services\Parser;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class Parse extends Command implements ServiceSubscriberInterface
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:parse';
    private $validator;
    private $locator;

    /**
     * Parse constructor.
     * @param string|null        $name
     * @param ContainerInterface $locator
     */
    public function __construct(string $name = null, ContainerInterface $locator)
    {
        parent::__construct($name);
        $this->validator = Validation::createValidator();
        $this->locator = $locator;
    }

    /**
     * @static
     * @return array|string[]
     */
    public static function getSubscribedServices()
    {
        return [
            'csv' => CsvParser::class,
        ];
    }

    /**
     * @param ContainerInterface $locator
     * @return void
     */
    public function setLocator(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * @param $type
     * @return ParserInterface|null
     */
    private function getParser($type):?ParserInterface
    {
        if ($this->locator->has($type)) {
            return $this->locator->get($type);
        }
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('This command allows you to parse data from internet');
        $this->addArgument('url', InputArgument::REQUIRED, 'File or web-page url');
        $this->addArgument('type', InputArgument::REQUIRED, 'Parser type (html, csv)...');
        $this->addArgument('limit', InputArgument::OPTIONAL, 'Limited number of records to crawl');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
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
        $parser = $this->getParser($input->getArgument('type'));
        if (!$parser instanceof ParserInterface) {
            $output->writeln("<error>Can\'t use this type of parsers</error>");

            return Command::FAILURE;
        }
        $parser->setUrl($url);
        if ($input->getArgument('limit')){
            $parser->setLimit($input->getArgument('limit'));
        }
        if ($parser->getContent()) {
            $output->writeln('Begin to save data');

            foreach ( $parser->saveContent() as $message){
                $output->writeln($message);
            }

        } else {
            $output->writeln("<error>There is nothing useful here" . "</error>");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
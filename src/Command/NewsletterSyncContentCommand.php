<?php

namespace WebEtDesign\NewsletterBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\ContentTranslation;
use WebEtDesign\NewsletterBundle\Entity\NewsletterContentTypeEnum;
use WebEtDesign\NewsletterBundle\Repository\NewsletterRepository;
use WebEtDesign\NewsletterBundle\Services\ModelProvider;
use WebEtDesign\NewsletterBundle\Services\NewsletterContentCreatorService;

class NewsletterSyncContentCommand extends Command
{
    protected static $defaultName = 'newsletter:sync-contents';

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var ModelProvider
     */
    private $templateProvider;
    /**
     * @var string|null
     */
    private $name;
    /**
     * @var NewsletterRepository
     */
    private $repository;
    /**
     * @var NewsletterContentCreatorService
     */
    private $contentCreatorService;

    /**
     * NewsletterSyncContentCommand constructor.
     * @param string|null $name
     * @param EntityManagerInterface $em
     * @param NewsletterRepository $repository
     * @param ModelProvider $templateProvider
     * @param NewsletterContentCreatorService $contentCreatorService
     */
    public function __construct(?string $name, EntityManagerInterface $em, NewsletterRepository $repository, ModelProvider $templateProvider, NewsletterContentCreatorService $contentCreatorService)
    {
        parent::__construct($name);

        $this->em                    = $em;
        $this->templateProvider      = $templateProvider;
        $this->name                  = $name;
        $this->repository            = $repository;
        $this->contentCreatorService = $contentCreatorService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Sync content of newsletter');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $newsletters = $this->repository->findAll();
        $progress    = new ProgressBar($output, count($newsletters));
        foreach ($newsletters as $newsletter) {
            $progress->advance();
            try {
                $config = $this->templateProvider->getConfigurationFor($newsletter->getModel());
                if ($config && isset($config['contents'])) {
                    $newsletter = $this->contentCreatorService->createNewsletterContents($config, $newsletter);
                }
            } catch (\Exception $e) {
            }
        }

        $this->em->flush();

        $progress->finish();
        $io->success('Contenus synchronisés');

        return 0;
    }
}

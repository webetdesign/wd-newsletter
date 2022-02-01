<?php

namespace WebEtDesign\NewsletterBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WebEtDesign\NewsletterBundle\Repository\NewsletterRepository;
use WebEtDesign\NewsletterBundle\Services\ModelProvider;
use WebEtDesign\NewsletterBundle\Services\NewsletterContentCreatorService;

class NewsletterSyncContentCommand extends Command
{
    protected static $defaultName = 'newsletter:sync-contents';


    /**
     * NewsletterSyncContentCommand constructor.
     * @param string|null $name
     * @param EntityManagerInterface $em
     * @param NewsletterRepository $repository
     * @param ModelProvider $templateProvider
     * @param NewsletterContentCreatorService $contentCreatorService
     */
    public function __construct(
        ?string                                 $name,
        private EntityManagerInterface          $em,
        private NewsletterRepository            $repository,
        private ModelProvider                   $templateProvider,
        private NewsletterContentCreatorService $contentCreatorService
    )
    {
        parent::__construct($name);
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
        $progress = new ProgressBar($output, count($newsletters));

        foreach ($newsletters as $newsletter) {
            $progress->advance();
            try {
                $config = $this->templateProvider->getConfigurationFor($newsletter->getModel());
                if ($config && isset($config['contents'])) {
                    $this->contentCreatorService->createNewsletterContents($config, $newsletter);
                }
            } catch (\Exception $e) {
                $io->error($e->getMessage());
            }
        }

        $this->em->flush();

        $progress->finish();
        $io->success('Contenus synchronisés');

        return 0;
    }
}

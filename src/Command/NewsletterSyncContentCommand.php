<?php

namespace WebEtDesign\NewsletterBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WebEtDesign\CmsBundle\Factory\TemplateFactoryInterface;
use WebEtDesign\NewsletterBundle\Repository\NewsletterRepository;

#[AsCommand(

    name: 'newsletter:sync-contents', description: 'Sync content of newsletter'

)]
class NewsletterSyncContentCommand extends Command
{
    public function __construct(
        private EntityManagerInterface          $em,
        private NewsletterRepository            $repository,
        private TemplateFactoryInterface $templateFactory
    )
    {
        parent::__construct();
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

<?php

namespace WebEtDesign\NewsletterBundle\Services;


use Exception;
use Symfony\Component\Templating\EngineInterface;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;

class EmailService
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var EngineInterface
     */
    private $templating;
    /**
     * @var ModelProvider
     */
    private $modelProvider;
    /**
     * @var string
     */
    private $from;

    /**
     * EmailService constructor.
     * @param \Swift_Mailer $mailer
     * @param EngineInterface $templating
     * @param ModelProvider $modelProvider
     * @param string $from
     */
    public function __construct(
        \Swift_Mailer $mailer,
        EngineInterface $templating,
        ModelProvider $modelProvider,
        string $from

    ) {
        $this->mailer       = $mailer;
        $this->templating = $templating;
        $this->modelProvider = $modelProvider;
        $this->from = $from;
    }

    /**
     * @param Newsletter $newsletter
     * @param $email_list
     * @return int
     * @throws Exception
     */
    public function sendNewsletter(Newsletter $newsletter,  $email_list): int
    {
        $res = -1;
        foreach ($email_list as $locale => $emails) {
            $message = (new \Swift_Message($newsletter->getTitle()))
                ->setFrom([$this->from => $newsletter->getSender()])
                ->setTo($emails)
                ->setReplyTo($newsletter->getEmail())
                ->setBody(
                    $this->templating->render($this->modelProvider->getTemplate($newsletter->getModel()), [
                        'object' => $newsletter,
                        'locale' => $locale
                    ]), 'text/html'
                )
                ->addPart(
                    $this->templating->render($this->modelProvider->getTxt($newsletter->getModel()), [
                        'object' => $newsletter,
                        'locale' => $locale
                    ]), 'text/plain'
                );

            $res = $this->mailer->send($message);
        }

        return $res;
    }
}

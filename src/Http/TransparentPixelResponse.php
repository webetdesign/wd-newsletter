<?php

namespace WebEtDesign\NewsletterBundle\Http;

use Symfony\Component\HttpFoundation\Response;

class TransparentPixelResponse extends Response
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $pixel = sprintf('%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c', 71, 73, 70, 56, 57, 97, 1, 0, 1, 0, 128, 255, 0, 192, 192, 192, 0, 0, 0, 33, 249, 4, 1, 0, 0, 0, 0, 44, 0, 0, 0, 0, 1, 0, 1, 0, 0, 2, 2, 68, 1, 0, 59);
        parent::__construct($pixel);
        $this->headers->set('Content-type', 'image/png');
        $this->headers->set('Content-Length', 42);
        $this->headers->set('Cache-Control', 'private, no-cache, no-cache=Set-Cookie, proxy-revalidate');
        $this->headers->set('Pragma', 'no-cache');
    }
}
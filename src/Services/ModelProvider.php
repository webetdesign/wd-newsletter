<?php

namespace WebEtDesign\NewsletterBundle\Services;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class PageProvider
 * @package WebEtDesign\NewsletterBundle\Services
 */
class ModelProvider
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param null $filter
     * @return array
     *
     * List of available templates for choice type
     */
    public function getModelList($filter = null)
    {
        $list = [];
        foreach ($this->config as $key => $template) {
            $list[$template['title']] = $key;

        }

        return $list;
    }

    /**
     * @param $modelName
     * @return mixed
     * @throws \Exception
     *
     * Retrieve a twig path for a template
     */
    public function getTemplate($modelName)
    {
        if (!isset($this->config[$modelName])) {
            throw new \Exception('Template name :'.$modelName.' does not exists. Please add it in newsletter.yaml');
        }

        return $this->config[$modelName]['template'];
    }

    /**
     * @param $modelName
     * @return mixed
     * @throws \Exception
     *
     * Retrieve a twig path for a template
     */
    public function getTxt($modelName)
    {
        if (!isset($this->config[$modelName])) {
            throw new \Exception('Txt name :'.$modelName.' does not exists. Please add it in newsletter.yaml');
        }

        return $this->config[$modelName]['txt'];
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     *
     * Get whole configuration of a template
     */
    public function getConfigurationFor($name)
    {
        if (!isset($this->config[$name])) {
            throw new \Exception('Configuration for :'.$name.' does not exists. Please add it in newsletter.yaml');
        }

        return $this->config[$name];
    }
}

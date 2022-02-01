<?php

namespace WebEtDesign\NewsletterBundle\Services;

use Exception;

/**
 * Class PageProvider
 * @package WebEtDesign\NewsletterBundle\Services
 */
class ModelProvider
{
    public function __construct(private array $config) {}

    public function getModelList(): array
    {
        $list = [];
        foreach ($this->config as $key => $template) {
            $list[$template['name']] = $key;

        }

        return $list;
    }

    /**
     * @param $modelName
     * @return mixed
     * @throws Exception
     *
     * Retrieve a twig path for a template
     */
    public function getTemplate($modelName): mixed
    {
        if (!isset($this->config[$modelName])) {
            throw new Exception('Template name :'.$modelName.' does not exists. Please add it in newsletter.yaml');
        }

        return $this->config[$modelName]['template'];
    }

    /**
     * @param $modelName
     * @return mixed
     * @throws Exception
     *
     * Retrieve a twig path for a template
     */
    public function getTxt($modelName): mixed
    {
        if (!isset($this->config[$modelName])) {
            throw new Exception('Txt name :'.$modelName.' does not exists. Please add it in newsletter.yaml');
        }

        return $this->config[$modelName]['txt'];
    }

    /**
     * @param $name
     * @return mixed
     * @throws Exception
     *
     * Get whole configuration of a template
     */
    public function getConfigurationFor($name): mixed
    {
        if (!isset($this->config[$name])) {
            throw new Exception('Configuration for :'.$name.' does not exists. Please add it in newsletter.yaml');
        }

        return $this->config[$name];
    }
}

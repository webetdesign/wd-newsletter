<?php

namespace WebEtDesign\NewsletterBundle\Services;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class RoleProvider
 * @package WebEtDesign\NewsletterBundle\Services
 */
class RoleProvider
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
     * List of available roles for choice type
     */
    public function getRoleList($filter = null)
    {
        $list = [];
        foreach ($this->config as $key => $roles) {
            $list[$roles['name']] = $roles['value'];
        }

        return $list;
    }

    /**
     * @param $rolesName
     * @return mixed
     * @throws \Exception
     *
     * Retrieve a twig path for a roles
     */
    public function getRole($rolesName)
    {
        if (!isset($this->config[$rolesName])) {
            throw new \Exception('Role name :'.$rolesName.' does not exists. Please add it in newsletter.yaml');
        }

        return $this->config[$rolesName]['roles'];
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     *
     * Get whole configuration of a roles
     */
    public function getConfigurationFor($name)
    {
        if (!isset($this->config[$name])) {
            throw new \Exception('Configuration for :'.$name.' does not exists. Please add it in newsletter.yaml');
        }

        return $this->config[$name];
    }
}

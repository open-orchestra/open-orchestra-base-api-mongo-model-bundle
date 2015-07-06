<?php

namespace OpenOrchestra\BaseApiModelBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OpenOrchestraBaseApiModelExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $factoryService = null;
        $factory = $config['factory'];
        if (count($factory) > 0) {
            $factoryService = $factory[0];
        }

        if (is_null($factoryService) && class_exists('Doctrine\ODM\MongoDB\DocumentManager')) {
            $factoryService = 'doctrine.odm.mongodb.document_manager';
        } elseif (is_null($factoryService) && class_exists('Doctrine\ORM\EntityManager')) {
            $factoryService = 'doctrine.orm.entity_manager';
        }

        foreach ($config['document'] as $class => $content) {
            if (is_array($content)) {
                $container->setParameter('open_orchestra_api.document.' . $class . '.class', $content['class']);
                if (array_key_exists('repository', $content)) {
                    $definition = new Definition($content['repository'], array($content['class']));
                    $definition->setFactory(array(new Reference($factoryService), 'getRepository'));
                    $container->setDefinition('open_orchestra_api.repository.' . $class, $definition);
                }
            }
        }
    }
}
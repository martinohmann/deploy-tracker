<?php

namespace DeployTracker\DependencyInjection\Compiler;

use DeployTracker\Processor\RevisionLogProcessorFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RevisionLogProcessorPass implements CompilerPassInterface
{
    const TAG_NAME = 'deploy_tracker.revision_log_processor';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $factory = $container->findDefinition(RevisionLogProcessorFactory::class);

        $taggedServices = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['type'])) {
                    throw new \RuntimeException(sprintf(
                        'Service "%s" is tagged as "%s", but tag misses the "type" field.',
                        $id,
                        self::TAG_NAME
                    ));
                }

                $factory->addMethodCall(
                    'addProcessor',
                    [$tag['type'], new Reference($id)]
                );
            }
        }
    }
}

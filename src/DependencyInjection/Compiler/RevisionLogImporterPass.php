<?php

namespace DeployTracker\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use DeployTracker\Importer\RevisionLogImporterFactory;
use Symfony\Component\DependencyInjection\Reference;

class RevisionLogImporterPass implements CompilerPassInterface
{
    const TAG_NAME = 'deploy_tracker.revision_log_importer';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $factory = $container->findDefinition(RevisionLogImporterFactory::class);

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
                    'addImporter',
                    [$tag['type'], new Reference($id)]
                );
            }
        }
    }
}

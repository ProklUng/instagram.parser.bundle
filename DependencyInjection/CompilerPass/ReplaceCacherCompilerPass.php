<?php

namespace Prokl\InstagramParserRapidApiBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ReplaceCacherCompilerPass
 *
 * @since 21.07.2021
 */
class ReplaceCacherCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container): void
    {
        $cacherService = $container->getParameter('instagram_parser_rapid_api.cacher');
        
        if (!$cacherService) {
            return;
        }

        $destinationDefinitionParser = $container->getDefinition('instagram_parser_rapid_api.rapid_api');
        $destinationDefinitionUserService = $container->getDefinition('instagram_parser_rapid_api.rapid_api_get_user_id');

        if (!$container->hasDefinition($cacherService)) {
            throw new \RuntimeException(
                sprintf('Cacher service %s from parameter cacher_service not exist.', $cacherService)
            );
        }

        $cacherDefinition = $container->getDefinition($cacherService);

        $destinationDefinitionParser->replaceArgument(0, $cacherDefinition);
        $destinationDefinitionUserService->replaceArgument(0, $cacherDefinition);
    }
}
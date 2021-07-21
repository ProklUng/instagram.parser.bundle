<?php

namespace Prokl\InstagramParserRapidApiBundle;

use Prokl\InstagramParserRapidApiBundle\DependencyInjection\InstagramParserRapidApiExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Prokl\InstagramParserRapidApiBundle\DependencyInjection\CompilerPass\ReplaceCacherCompilerPass;

/**
 * Class InstagramParserRapidApiBundle
 * @package Local\Bundles\InstagramParserRapidApiBundle
 *
 * @since 22.02.2021
 */
class InstagramParserRapidApiBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new InstagramParserRapidApiExtension();
        }

        return $this->extension;
    }
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ReplaceCacherCompilerPass());
    }
}

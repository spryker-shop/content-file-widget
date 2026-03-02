<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ContentFileWidget;

use Spryker\Shared\Twig\TwigFunctionProvider;
use Spryker\Yves\Kernel\AbstractFactory;
use SprykerShop\Yves\ContentFileWidget\Dependency\Client\ContentFileWidgetToContentFileClientInterface;
use SprykerShop\Yves\ContentFileWidget\Dependency\Client\ContentFileWidgetToFileManagerStorageClientInterface;
use SprykerShop\Yves\ContentFileWidget\Expander\FileStorageDataExpanderInterface;
use SprykerShop\Yves\ContentFileWidget\Expander\IconNameFileStorageDataExpander;
use SprykerShop\Yves\ContentFileWidget\Reader\ContentFileReader;
use SprykerShop\Yves\ContentFileWidget\Reader\ContentFileReaderInterface;
use SprykerShop\Yves\ContentFileWidget\Twig\ContentFileListTwigFunctionProvider;
use Twig\Environment;
use Twig\TwigFunction;

/**
 * @method \SprykerShop\Yves\ContentFileWidget\ContentFileWidgetConfig getConfig()
 */
class ContentFileWidgetFactory extends AbstractFactory
{
    public function createContentFileListTwigFunctionProvider(Environment $twig, string $localeName): TwigFunctionProvider
    {
        return new ContentFileListTwigFunctionProvider(
            $twig,
            $localeName,
            $this->createContentFileReader(),
            $this->getContentFileClient(),
        );
    }

    public function createContentFileListTwigFunction(Environment $twig, string $localeName): TwigFunction
    {
        $functionProvider = $this->createContentFileListTwigFunctionProvider($twig, $localeName);

        return new TwigFunction(
            $functionProvider->getFunctionName(),
            $functionProvider->getFunction(),
            $functionProvider->getOptions(),
        );
    }

    public function createContentFileReader(): ContentFileReaderInterface
    {
        return new ContentFileReader(
            $this->getFileManagerStorageClient(),
            $this->getFileStorageDataExpanders(),
        );
    }

    /**
     * @return array<\SprykerShop\Yves\ContentFileWidget\Expander\FileStorageDataExpanderInterface>
     */
    public function getFileStorageDataExpanders(): array
    {
        return [
            $this->createIconNameFileStorageDataExpander(),
        ];
    }

    public function createIconNameFileStorageDataExpander(): FileStorageDataExpanderInterface
    {
        return new IconNameFileStorageDataExpander($this->getConfig());
    }

    public function getContentFileClient(): ContentFileWidgetToContentFileClientInterface
    {
        return $this->getProvidedDependency(ContentFileWidgetDependencyProvider::CLIENT_CONTENT_FILE);
    }

    public function getFileManagerStorageClient(): ContentFileWidgetToFileManagerStorageClientInterface
    {
        return $this->getProvidedDependency(ContentFileWidgetDependencyProvider::CLIENT_FILE_MANAGER_STORAGE);
    }
}

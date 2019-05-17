<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ContentFileWidget\Reader;

use SprykerShop\Yves\ContentFileWidget\ContentFileWidgetConfig;
use SprykerShop\Yves\ContentFileWidget\Dependency\Client\ContentFileWidgetToContentFileClientInterface;
use SprykerShop\Yves\ContentFileWidget\Dependency\Client\ContentFileWidgetToFileManagerStorageClientInterface;

class ContentFileReader implements ContentFileReaderInterface
{
    protected const LABEL_FILE_SIZES = ['B', 'Kb', 'MB', 'GB', 'TB', 'PB'];
    protected const KEY_DEFAULT_ICON_NAME = 'text/plain';

    /**
     * @var \SprykerShop\Yves\ContentFileWidget\Dependency\Client\ContentFileWidgetToContentFileClientInterface
     */
    protected $contentFileClient;

    /**
     * @var \SprykerShop\Yves\ContentFileWidget\Dependency\Client\ContentFileWidgetToFileManagerStorageClientInterface
     */
    protected $fileManagerStorageClient;

    /**
     * @var \SprykerShop\Yves\ContentFileWidget\ContentFileWidgetConfig
     */
    protected $contentFileWidgetConfig;

    /**
     * @param \SprykerShop\Yves\ContentFileWidget\Dependency\Client\ContentFileWidgetToContentFileClientInterface $contentFileClient
     * @param \SprykerShop\Yves\ContentFileWidget\Dependency\Client\ContentFileWidgetToFileManagerStorageClientInterface $fileManagerStorageClient
     * @param \SprykerShop\Yves\ContentFileWidget\ContentFileWidgetConfig $contentFileWidgetConfig
     */
    public function __construct(
        ContentFileWidgetToContentFileClientInterface $contentFileClient,
        ContentFileWidgetToFileManagerStorageClientInterface $fileManagerStorageClient,
        ContentFileWidgetConfig $contentFileWidgetConfig
    ) {
        $this->contentFileClient = $contentFileClient;
        $this->fileManagerStorageClient = $fileManagerStorageClient;
        $this->contentFileWidgetConfig = $contentFileWidgetConfig;
    }

    /**
     * @param int $idContent
     * @param string $localeName
     *
     * @return array|null
     */
    public function findFileCollection(int $idContent, string $localeName): ?array
    {
        $contentFileListTypeTransfer = $this->contentFileClient->executeContentFileListTypeById($idContent, $localeName);

        if ($contentFileListTypeTransfer === null) {
            return null;
        }

        $fileViewCollection = [];

        foreach ($contentFileListTypeTransfer->getFileIds() as $fileId) {
            $fileStorageDataTransfer = $this->fileManagerStorageClient->findFileById($fileId, $localeName);

            if (!$fileStorageDataTransfer) {
                continue;
            }

            $fileDisplaySize = $this->getFileDisplaySize($fileStorageDataTransfer->getSize());
            $fileIconName = $this->getIconName(
                $fileStorageDataTransfer->getType(),
                $fileStorageDataTransfer->getFileName()
            );

            $fileViewCollection[] = $fileStorageDataTransfer->setDisplaySize($fileDisplaySize)
                ->setIconName($fileIconName);
        }

        return $fileViewCollection;
    }

    /**
     * @param int $fileSize
     *
     * @return string
     */
    protected function getFileDisplaySize(int $fileSize): string
    {
        $power = floor(log($fileSize, 1024));
        $calculatedSize = number_format($fileSize / (1024 ** $power), 1, '.', ',');

        return sprintf('%s %s', $calculatedSize, static::LABEL_FILE_SIZES[(int)$power]);
    }

    /**
     * @param string $fileMimeType
     * @param string $fileName
     *
     * @return string
     */
    protected function getIconName(string $fileMimeType, string $fileName): string
    {
        $iconNames = $this->contentFileWidgetConfig->getFileIconNames();
        $fileType = explode('/', $fileMimeType)[0];
        $fileIconName = $this->getFileIconNameByExtension($fileName);

        return $iconNames[$fileMimeType] ?? $iconNames[$fileType] ?? $fileIconName;
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    protected function getFileIconNameByExtension(string $fileName): string
    {
        $iconNames = $this->contentFileWidgetConfig->getFileIconNames();
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        return $iconNames[$fileExtension] ?? $iconNames[static::KEY_DEFAULT_ICON_NAME];
    }
}

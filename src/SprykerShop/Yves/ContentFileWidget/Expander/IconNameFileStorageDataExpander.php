<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ContentFileWidget\Expander;

use Generated\Shared\Transfer\FileStorageDataTransfer;
use SprykerShop\Yves\ContentFileWidget\ContentFileWidgetConfig;

class IconNameFileStorageDataExpander implements FileStorageDataExpanderInterface
{
    /**
     * @var string
     */
    protected const KEY_DEFAULT_ICON_NAME = 'file';

    /**
     * @var \SprykerShop\Yves\ContentFileWidget\ContentFileWidgetConfig
     */
    protected $contentFileWidgetConfig;

    public function __construct(ContentFileWidgetConfig $contentFileWidgetConfig)
    {
        $this->contentFileWidgetConfig = $contentFileWidgetConfig;
    }

    public function expand(FileStorageDataTransfer $fileStorageDataTransfer): FileStorageDataTransfer
    {
        $fileStorageDataTransfer->requireType();
        $iconNames = $this->contentFileWidgetConfig->getFileIconNames();

        if (isset($iconNames[$fileStorageDataTransfer->getType()]) && $iconNames[$fileStorageDataTransfer->getType()] !== static::KEY_DEFAULT_ICON_NAME) {
            return $fileStorageDataTransfer->setIconName($iconNames[$fileStorageDataTransfer->getType()]);
        }

        $fileType = explode('/', $fileStorageDataTransfer->getTypeOrFail())[0];

        if (isset($iconNames[$fileType]) && $iconNames[$fileType] !== static::KEY_DEFAULT_ICON_NAME) {
            return $fileStorageDataTransfer->setIconName($iconNames[$fileType]);
        }

        $fileIconName = $this->getFileIconNameByExtension(
            $fileStorageDataTransfer->getFileNameOrFail(),
        );

        return $fileStorageDataTransfer->setIconName($fileIconName);
    }

    protected function getFileIconNameByExtension(string $fileName): string
    {
        $iconNames = $this->contentFileWidgetConfig->getFileIconNames();
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        if (isset($iconNames[$fileExtension])) {
            return $iconNames[$fileExtension];
        }

        return static::KEY_DEFAULT_ICON_NAME;
    }
}

<?php declare(strict_types=1);
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile
namespace DEG\CustomReports\Block\Adminhtml\Report;

use Magento\Framework\Convert\Excel;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Export extends \Magento\Backend\Block\Widget\Grid\Export
{
    /**
     * @return $this|\DEG\CustomReports\Block\Adminhtml\Report\Export
     */
    public function _prepareLayout(): Export
    {
        return $this;
    }

    /**
     * Prepare export button
     * This had to be implemented as a lazy prepare because if the export block is not added
     * through the layout, there is no way for the _prepareLayout to work since the parent block
     * would not be set yet.
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function lazyPrepareLayout(): Export
    {
        $this->setChild(
            'export_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Export'),
                    'onclick' => $this->getParentBlock()->getJsObjectName().'.doExport()',
                    'class' => 'task',
                ]
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Retrieve a file container array by grid data as MS Excel 2003 XML Document
     * Return array with keys type and value
     *
     * @param string $sheetName
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getExcelFile($sheetName = ''): array
    {
        $collection = $this->_getPreparedCollection();

        $convert = new Excel($collection->getIterator(), [$this, 'getRowRecord']);

        $name = md5(microtime());
        $file = $this->_path.'/'.$name.'.xml';

        $this->_directory->create($this->_path);
        $stream = $this->_directory->openFile($file, 'w+');
        $stream->lock();

        $convert->setDataHeader($this->_getExportHeaders());
        if ($this->getCountTotals()) {
            $convert->setDataFooter($this->_getExportTotals());
        }

        $convert->write($stream, $sheetName);
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }
}

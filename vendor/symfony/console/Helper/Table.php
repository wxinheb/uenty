<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Helper;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;


class Table
{
    
    private $headers = array();

    
    private $rows = array();

    
    private $effectiveColumnWidths = array();

    
    private $numberOfColumns;

    
    private $output;

    
    private $style;

    
    private $columnStyles = array();

    
    private $columnWidths = array();

    private static $styles;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;

        if (!self::$styles) {
            self::$styles = self::initStyles();
        }

        $this->setStyle('default');
    }

    
    public static function setStyleDefinition($name, TableStyle $style)
    {
        if (!self::$styles) {
            self::$styles = self::initStyles();
        }

        self::$styles[$name] = $style;
    }

    
    public static function getStyleDefinition($name)
    {
        if (!self::$styles) {
            self::$styles = self::initStyles();
        }

        if (isset(self::$styles[$name])) {
            return self::$styles[$name];
        }

        throw new InvalidArgumentException(sprintf('Style "%s" is not defined.', $name));
    }

    
    public function setStyle($name)
    {
        $this->style = $this->resolveStyle($name);

        return $this;
    }

    
    public function getStyle()
    {
        return $this->style;
    }

    
    public function setColumnStyle($columnIndex, $name)
    {
        $columnIndex = intval($columnIndex);

        $this->columnStyles[$columnIndex] = $this->resolveStyle($name);

        return $this;
    }

    
    public function getColumnStyle($columnIndex)
    {
        if (isset($this->columnStyles[$columnIndex])) {
            return $this->columnStyles[$columnIndex];
        }

        return $this->getStyle();
    }

    
    public function setColumnWidth($columnIndex, $width)
    {
        $this->columnWidths[intval($columnIndex)] = intval($width);

        return $this;
    }

    
    public function setColumnWidths(array $widths)
    {
        $this->columnWidths = array();
        foreach ($widths as $index => $width) {
            $this->setColumnWidth($index, $width);
        }

        return $this;
    }

    public function setHeaders(array $headers)
    {
        $headers = array_values($headers);
        if (!empty($headers) && !is_array($headers[0])) {
            $headers = array($headers);
        }

        $this->headers = $headers;

        return $this;
    }

    public function setRows(array $rows)
    {
        $this->rows = array();

        return $this->addRows($rows);
    }

    public function addRows(array $rows)
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }

        return $this;
    }

    public function addRow($row)
    {
        if ($row instanceof TableSeparator) {
            $this->rows[] = $row;

            return $this;
        }

        if (!is_array($row)) {
            throw new InvalidArgumentException('A row must be an array or a TableSeparator instance.');
        }

        $this->rows[] = array_values($row);

        return $this;
    }

    public function setRow($column, array $row)
    {
        $this->rows[$column] = $row;

        return $this;
    }

    
    public function render()
    {
        $this->calculateNumberOfColumns();
        $rows = $this->buildTableRows($this->rows);
        $headers = $this->buildTableRows($this->headers);

        $this->calculateColumnsWidth(array_merge($headers, $rows));

        $this->renderRowSeparator();
        if (!empty($headers)) {
            foreach ($headers as $header) {
                $this->renderRow($header, $this->style->getCellHeaderFormat());
                $this->renderRowSeparator();
            }
        }
        foreach ($rows as $row) {
            if ($row instanceof TableSeparator) {
                $this->renderRowSeparator();
            } else {
                $this->renderRow($row, $this->style->getCellRowFormat());
            }
        }
        if (!empty($rows)) {
            $this->renderRowSeparator();
        }

        $this->cleanup();
    }

    
    private function renderRowSeparator()
    {
        if (0 === $count = $this->numberOfColumns) {
            return;
        }

        if (!$this->style->getHorizontalBorderChar() && !$this->style->getCrossingChar()) {
            return;
        }

        $markup = $this->style->getCrossingChar();
        for ($column = 0; $column < $count; ++$column) {
            $markup .= str_repeat($this->style->getHorizontalBorderChar(), $this->effectiveColumnWidths[$column]).$this->style->getCrossingChar();
        }

        $this->output->writeln(sprintf($this->style->getBorderFormat(), $markup));
    }

    
    private function renderColumnSeparator()
    {
        return sprintf($this->style->getBorderFormat(), $this->style->getVerticalBorderChar());
    }

    
    private function renderRow(array $row, $cellFormat)
    {
        if (empty($row)) {
            return;
        }

        $rowContent = $this->renderColumnSeparator();
        foreach ($this->getRowColumns($row) as $column) {
            $rowContent .= $this->renderCell($row, $column, $cellFormat);
            $rowContent .= $this->renderColumnSeparator();
        }
        $this->output->writeln($rowContent);
    }

    
    private function renderCell(array $row, $column, $cellFormat)
    {
        $cell = isset($row[$column]) ? $row[$column] : '';
        $width = $this->effectiveColumnWidths[$column];
        if ($cell instanceof TableCell && $cell->getColspan() > 1) {
            // add the width of the following columns(numbers of colspan).
            foreach (range($column + 1, $column + $cell->getColspan() - 1) as $nextColumn) {
                $width += $this->getColumnSeparatorWidth() + $this->effectiveColumnWidths[$nextColumn];
            }
        }

        // str_pad won't work properly with multi-byte strings, we need to fix the padding
        if (false !== $encoding = mb_detect_encoding($cell, null, true)) {
            $width += strlen($cell) - mb_strwidth($cell, $encoding);
        }

        $style = $this->getColumnStyle($column);

        if ($cell instanceof TableSeparator) {
            return sprintf($style->getBorderFormat(), str_repeat($style->getHorizontalBorderChar(), $width));
        }

        $width += Helper::strlen($cell) - Helper::strlenWithoutDecoration($this->output->getFormatter(), $cell);
        $content = sprintf($style->getCellRowContentFormat(), $cell);

        return sprintf($cellFormat, str_pad($content, $width, $style->getPaddingChar(), $style->getPadType()));
    }

    
    private function calculateNumberOfColumns()
    {
        if (null !== $this->numberOfColumns) {
            return;
        }

        $columns = array(0);
        foreach (array_merge($this->headers, $this->rows) as $row) {
            if ($row instanceof TableSeparator) {
                continue;
            }

            $columns[] = $this->getNumberOfColumns($row);
        }

        $this->numberOfColumns = max($columns);
    }

    private function buildTableRows($rows)
    {
        $unmergedRows = array();
        for ($rowKey = 0; $rowKey < count($rows); ++$rowKey) {
            $rows = $this->fillNextRows($rows, $rowKey);

            // Remove any new line breaks and replace it with a new line
            foreach ($rows[$rowKey] as $column => $cell) {
                if (!strstr($cell, "\n")) {
                    continue;
                }
                $lines = explode("\n", $cell);
                foreach ($lines as $lineKey => $line) {
                    if ($cell instanceof TableCell) {
                        $line = new TableCell($line, array('colspan' => $cell->getColspan()));
                    }
                    if (0 === $lineKey) {
                        $rows[$rowKey][$column] = $line;
                    } else {
                        $unmergedRows[$rowKey][$lineKey][$column] = $line;
                    }
                }
            }
        }

        $tableRows = array();
        foreach ($rows as $rowKey => $row) {
            $tableRows[] = $this->fillCells($row);
            if (isset($unmergedRows[$rowKey])) {
                $tableRows = array_merge($tableRows, $unmergedRows[$rowKey]);
            }
        }

        return $tableRows;
    }

    
    private function fillNextRows($rows, $line)
    {
        $unmergedRows = array();
        foreach ($rows[$line] as $column => $cell) {
            if ($cell instanceof TableCell && $cell->getRowspan() > 1) {
                $nbLines = $cell->getRowspan() - 1;
                $lines = array($cell);
                if (strstr($cell, "\n")) {
                    $lines = explode("\n", $cell);
                    $nbLines = count($lines) > $nbLines ? substr_count($cell, "\n") : $nbLines;

                    $rows[$line][$column] = new TableCell($lines[0], array('colspan' => $cell->getColspan()));
                    unset($lines[0]);
                }

                // create a two dimensional array (rowspan x colspan)
                $unmergedRows = array_replace_recursive(array_fill($line + 1, $nbLines, array()), $unmergedRows);
                foreach ($unmergedRows as $unmergedRowKey => $unmergedRow) {
                    $value = isset($lines[$unmergedRowKey - $line]) ? $lines[$unmergedRowKey - $line] : '';
                    $unmergedRows[$unmergedRowKey][$column] = new TableCell($value, array('colspan' => $cell->getColspan()));
                }
            }
        }

        foreach ($unmergedRows as $unmergedRowKey => $unmergedRow) {
            // we need to know if $unmergedRow will be merged or inserted into $rows
            if (isset($rows[$unmergedRowKey]) && is_array($rows[$unmergedRowKey]) && ($this->getNumberOfColumns($rows[$unmergedRowKey]) + $this->getNumberOfColumns($unmergedRows[$unmergedRowKey]) <= $this->numberOfColumns)) {
                foreach ($unmergedRow as $cellKey => $cell) {
                    // insert cell into row at cellKey position
                    array_splice($rows[$unmergedRowKey], $cellKey, 0, array($cell));
                }
            } else {
                $row = $this->copyRow($rows, $unmergedRowKey - 1);
                foreach ($unmergedRow as $column => $cell) {
                    if (!empty($cell)) {
                        $row[$column] = $unmergedRow[$column];
                    }
                }
                array_splice($rows, $unmergedRowKey, 0, array($row));
            }
        }

        return $rows;
    }

    
    private function fillCells($row)
    {
        $newRow = array();
        foreach ($row as $column => $cell) {
            $newRow[] = $cell;
            if ($cell instanceof TableCell && $cell->getColspan() > 1) {
                foreach (range($column + 1, $column + $cell->getColspan() - 1) as $position) {
                    // insert empty value at column position
                    $newRow[] = '';
                }
            }
        }

        return $newRow ?: $row;
    }

    
    private function copyRow($rows, $line)
    {
        $row = $rows[$line];
        foreach ($row as $cellKey => $cellValue) {
            $row[$cellKey] = '';
            if ($cellValue instanceof TableCell) {
                $row[$cellKey] = new TableCell('', array('colspan' => $cellValue->getColspan()));
            }
        }

        return $row;
    }

    
    private function getNumberOfColumns(array $row)
    {
        $columns = count($row);
        foreach ($row as $column) {
            $columns += $column instanceof TableCell ? ($column->getColspan() - 1) : 0;
        }

        return $columns;
    }

    
    private function getRowColumns($row)
    {
        $columns = range(0, $this->numberOfColumns - 1);
        foreach ($row as $cellKey => $cell) {
            if ($cell instanceof TableCell && $cell->getColspan() > 1) {
                // exclude grouped columns.
                $columns = array_diff($columns, range($cellKey + 1, $cellKey + $cell->getColspan() - 1));
            }
        }

        return $columns;
    }

    
    private function calculateColumnsWidth($rows)
    {
        for ($column = 0; $column < $this->numberOfColumns; ++$column) {
            $lengths = array();
            foreach ($rows as $row) {
                if ($row instanceof TableSeparator) {
                    continue;
                }

                foreach ($row as $i => $cell) {
                    if ($cell instanceof TableCell) {
                        $textLength = strlen($cell);
                        if ($textLength > 0) {
                            $contentColumns = str_split($cell, ceil($textLength / $cell->getColspan()));
                            foreach ($contentColumns as $position => $content) {
                                $row[$i + $position] = $content;
                            }
                        }
                    }
                }

                $lengths[] = $this->getCellWidth($row, $column);
            }

            $this->effectiveColumnWidths[$column] = max($lengths) + strlen($this->style->getCellRowContentFormat()) - 2;
        }
    }

    
    private function getColumnSeparatorWidth()
    {
        return strlen(sprintf($this->style->getBorderFormat(), $this->style->getVerticalBorderChar()));
    }

    
    private function getCellWidth(array $row, $column)
    {
        $cellWidth = 0;

        if (isset($row[$column])) {
            $cell = $row[$column];
            $cellWidth = Helper::strlenWithoutDecoration($this->output->getFormatter(), $cell);
        }

        $columnWidth = isset($this->columnWidths[$column]) ? $this->columnWidths[$column] : 0;

        return max($cellWidth, $columnWidth);
    }

    
    private function cleanup()
    {
        $this->effectiveColumnWidths = array();
        $this->numberOfColumns = null;
    }

    private static function initStyles()
    {
        $borderless = new TableStyle();
        $borderless
            ->setHorizontalBorderChar('=')
            ->setVerticalBorderChar(' ')
            ->setCrossingChar(' ')
        ;

        $compact = new TableStyle();
        $compact
            ->setHorizontalBorderChar('')
            ->setVerticalBorderChar(' ')
            ->setCrossingChar('')
            ->setCellRowContentFormat('%s')
        ;

        $styleGuide = new TableStyle();
        $styleGuide
            ->setHorizontalBorderChar('-')
            ->setVerticalBorderChar(' ')
            ->setCrossingChar(' ')
            ->setCellHeaderFormat('%s')
        ;

        return array(
            'default' => new TableStyle(),
            'borderless' => $borderless,
            'compact' => $compact,
            'symfony-style-guide' => $styleGuide,
        );
    }

    private function resolveStyle($name)
    {
        if ($name instanceof TableStyle) {
            return $name;
        }

        if (isset(self::$styles[$name])) {
            return self::$styles[$name];
        }

        throw new InvalidArgumentException(sprintf('Style "%s" is not defined.', $name));
    }
}
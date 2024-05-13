<?php

namespace DiplomaProject\Models;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;

class ExcelBuilder
{
    public const VERTICAL_TOP = Alignment::VERTICAL_TOP;
    public const VERTICAL_CENTER = Alignment::VERTICAL_CENTER;
    public const VERTICAL_BOTTOM = Alignment::VERTICAL_BOTTOM;

    public const HORIZONTAL_LEFT = Alignment::HORIZONTAL_LEFT;
    public const HORIZONTAL_CENTER = Alignment::HORIZONTAL_CENTER;
    public const HORIZONTAL_RIGHT = Alignment::HORIZONTAL_RIGHT;

    private ?Spreadsheet $spreadsheet;
    private array $header = [];

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
    }

    public function setHeaderData(array $column_names)
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $litera = 'A';

        foreach ($column_names as $column_name) {
            $cell_coords = $litera . '1';

            $sheet->setCellValueExplicit($cell_coords, $column_name, DataType::TYPE_STRING);
            $this->header[$cell_coords] = $column_name;

            $litera++;
        }
    }

    public function setBody(array $rows, ?array $fields_order = null)
    {
        if (empty($fields_order)) {
            $fields_order = array_keys($rows[0]);
        }

        $sheet = $this->spreadsheet->getActiveSheet();
        $row_number = 1;

        foreach ($rows as $row) {
            $row_number += 1;
            $litera = 'A';

            foreach ($row as $cell) {
                $cell_coords = $litera . $row_number;
                $type = '';

                if ('string' === \gettype($cell)) {
                    if (\DateTime::createFromFormat('Y-m-d', trim($cell)) !== false) {
                        $type = DataType::TYPE_ISO_DATE;

                        $sheet->getStyle($cell_coords)
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);
                    } else {
                        $type = DataType::TYPE_STRING;
                    }
                }

                if (empty($type)) {
                    $sheet->setCellValue($cell_coords, (string) $cell);
                } else {
                    $sheet->setCellValueExplicit($cell_coords, $cell, $type);
                }

                $sheet->getStyle($cell_coords)->getAlignment()->setWrapText(true);

                $litera++;
            }

            $sheet->getRowDimension($row_number)
                ->setRowHeight(-1);
        }
    }

    public function setAutoWidth(array $columns)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        foreach ($columns as $litera) {
            $sheet->getColumnDimension($litera)
                ->setAutoSize(true);
        }
    }

    public function setDefaultRowHeight(int $default_row_height)
    {
        $this->spreadsheet->getActiveSheet()
            ->getDefaultRowDimension()
            ->setRowHeight($default_row_height);
    }

    public function setWidths(array $columns)
    {
        foreach ($columns as $litera => $width) {
            $this->spreadsheet->getActiveSheet()
                ->getColumnDimension($litera)
                ->setWidth($width);
        }
    }

    public function setHeaderFont(int $font_size = 15, bool $bold = true)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        foreach (array_keys($this->header) as $cell_coords) {
            $sheet->getStyle($cell_coords)
                ->getFont()
                ->setSize($font_size);
            $sheet->getStyle($cell_coords)
                ->getFont()
                ->setBold($bold);
        }
    }

    public function setHeaderAlignment(
        string $vertical = self::VERTICAL_CENTER,
        string $horizontal = self::HORIZONTAL_CENTER
    ) {
        $sheet = $this->spreadsheet->getActiveSheet();

        foreach (array_keys($this->header) as $cell_coords) {
            $sheet->getStyle($cell_coords)
                ->getAlignment()
                ->setVertical($vertical);
            $sheet->getStyle($cell_coords)
                ->getAlignment()
                ->setHorizontal($horizontal);
        }
    }

    public function setBodyAlignment(
        string $vertical = self::HORIZONTAL_LEFT,
        string $horizontal = self::VERTICAL_TOP
    ) {
        $sheet = $this->spreadsheet->getActiveSheet();

        $maxRow = $sheet->getHighestDataRow();
        $maxColumn = $sheet->getHighestDataColumn();

        $body_range = "A2:{$maxColumn}{$maxRow}";

        $sheet->getStyle($body_range)
            ->getAlignment()
            ->setVertical($vertical);
        $sheet->getStyle($body_range)
            ->getAlignment()
            ->setHorizontal($horizontal);
    }

    public function setBodyFont(int $font_size = 12, bool $bold = false) {
        $sheet = $this->spreadsheet->getActiveSheet();

        $maxRow = $sheet->getHighestDataRow();
        $maxColumn = $sheet->getHighestDataColumn();

        $body_range = "A2:{$maxColumn}{$maxRow}";

        $sheet->getStyle($body_range)
            ->getFont()
            ->setSize($font_size);
        $sheet->getStyle($body_range)
            ->getFont()
            ->setBold($bold);
    }

    public function createFile(string $fullpath)
    {
        $writer = new WriterXlsx($this->spreadsheet);
        $writer->save($fullpath);
    }
}

<?php

namespace DiplomaProject\Models;

class TenderExporter
{
    /**
     * @param string $fullpath
     * @param Tender[] $tenders
     */
    public function buildXlsxFile(string $fullpath, array $tenders): bool
    {
        try {
            $table_rows = [];
            foreach ($tenders as $tender) {
                $table_rows[] = $tender->getFields($without_id = true);
            }

            $xlsx_builder = new ExcelBuilder();
            $this->buildHeader($xlsx_builder);

            if (!empty($table_rows)) {
                $this->buildBody($xlsx_builder, $table_rows);
            }

            $xlsx_builder->setAutoWidth([
                'A', 'B', 'F', 'G', 'H'
            ]);
            $xlsx_builder->setWidths([
                'C' => 40,
                'D' => 60,
                'E' => 30,
                'F' => 30,
            ]);
            $xlsx_builder->setDefaultRowHeight(35);

            $xlsx_builder->createFile($fullpath);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    private function buildHeader(ExcelBuilder $xlsx_builder)
    {
        $header = array_map(function ($value) {
            return ucfirst(preg_replace('/_/', ' ', $value));
        }, array_keys(Tender::getStub()->getFields($without_id = true)));

        $xlsx_builder->setHeaderData($header);

        $xlsx_builder->setHeaderFont(15, true);
        $xlsx_builder->setHeaderAlignment(
            ExcelBuilder::HORIZONTAL_CENTER,
            ExcelBuilder::HORIZONTAL_CENTER
        );
    }

    private function buildBody(ExcelBuilder $xlsx_builder, array $table_rows)
    {
        $xlsx_builder->setBody($table_rows);

        $xlsx_builder->setBodyFont(12, false);
        $xlsx_builder->setBodyAlignment(
            ExcelBuilder::VERTICAL_TOP,
            ExcelBuilder::HORIZONTAL_LEFT
        );
    }
}

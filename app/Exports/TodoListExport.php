<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TodoListExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected $rows;
    protected $columns;

    public function __construct(array $rows, array $headings)
    {
        $this->rows = $rows;
        $this->columns = $headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings();
    }
}

<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Read the Excel file into an associative array (key = column header),
 * without inserting into the DB directly — validation + preview happen
 * manually in the ImportUsers page.
 */
class UsersImportReader implements ToArray, WithHeadingRow
{
    public function array(array $array): array
    {
        return $array;
    }
}

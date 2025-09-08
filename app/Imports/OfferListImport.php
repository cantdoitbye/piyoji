<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class OfferListImport implements ToCollection, WithHeadingRow
{
    protected $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Map Excel columns to database fields
            $this->data[] = [
                'device_id' => $row['deviceid'] ?? null,
                'awr_no' => $row['n_awr_no'] ?? null,
                'date' => $this->parseDate($row['date'] ?? null),
                'for' => $this->sanitizeFor($row['for'] ?? null),
                'garden_name' => $row['garden'] ?? null,
                'grade' => $row['grade'] ?? null,
                'inv_pretx' => $this->sanitizeInvPretx($row['inv_pretx'] ?? null),
                'inv_no' => $this->sanitizeNumber($row['inv_no'] ?? null),
                'party_1' => $row['party_1'] ?? null,
                'party_2' => $row['party_2'] ?? null,
                'party_3' => $row['party_3'] ?? null,
                'party_4' => $row['party_4'] ?? null,
                'party_5' => $row['party_5'] ?? null,
                'party_6' => $row['party_6'] ?? null,
                'party_7' => $row['party_7'] ?? null,
                'party_8' => $row['party_8'] ?? null,
                'party_9' => $row['party_9'] ?? null,
                'party_10' => $row['party_10'] ?? null,
                'pkgs' => $this->sanitizeDecimal($row['pkgs'] ?? null),
                'net1' => $this->sanitizeDecimal($row['net1'] ?? null),
                'ttl_kgs' => $this->sanitizeDecimal($row['ttl_kgs'] ?? null),
                'd_o_packing' => $this->parseDate($row['d_o_packing'] ?? null),
                'type' => $this->sanitizeType($row['type'] ?? null),
                'key' => $row['key'] ?? null,
                'name_of_upload' => $row['nameofupload'] ?? null,
            ];
        }
    }

    public function getData()
    {
        return $this->data;
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Try different date formats
            if (is_numeric($value)) {
                // Excel serial date
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($value - 2)->format('Y-m-d');
            }

            // Try common date formats
            $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd.m.Y', 'm/d/Y'];
            
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $value)->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function sanitizeFor($value)
    {
        if (empty($value)) {
            return 'GTPP'; // Default value
        }

        $value = strtoupper(trim($value));
        return in_array($value, ['GTPP', 'GTFP']) ? $value : 'GTPP';
    }

    private function sanitizeInvPretx($value)
    {
        if (empty($value)) {
            return 'C'; // Default value
        }

        $value = strtoupper(trim($value));
        return in_array($value, ['C', 'EX', 'PR']) ? $value : 'C';
    }

    private function sanitizeType($value)
    {
        if (empty($value)) {
            return null;
        }

        $value = strtoupper(trim($value));
        return in_array($value, ['BROKENS', 'FANNINGS', 'D']) ? $value : null;
    }

    private function sanitizeNumber($value)
    {
        if (empty($value) || !is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function sanitizeDecimal($value)
    {
        if (empty($value) || !is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
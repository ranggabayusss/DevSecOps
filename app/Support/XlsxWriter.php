<?php

namespace App\Support;

class XlsxWriter
{
    private const WORKSHEET_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const RELATIONSHIP_NS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    public function __construct(
        private readonly array $rows,
        private readonly string $sheetName = 'Kehadiran'
    ) {}

    public function toString(): string
    {
        return $this->zip([
            '[Content_Types].xml' => $this->contentTypesXml(),
            '_rels/.rels' => $this->rootRelationshipsXml(),
            'xl/workbook.xml' => $this->workbookXml(),
            'xl/_rels/workbook.xml.rels' => $this->workbookRelationshipsXml(),
            'xl/styles.xml' => $this->stylesXml(),
            'xl/worksheets/sheet1.xml' => $this->worksheetXml(),
        ]);
    }

    private function worksheetXml(): string
    {
        $rows = array_values($this->rows);
        $lastRow = max(count($rows), 1);
        $lastColumn = 'A';

        foreach ($rows as $row) {
            $lastColumn = $this->columnLetter(max(count($row) - 1, 0));
            break;
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="'.self::WORKSHEET_NS.'">';
        $xml .= '<dimension ref="A1:'.$lastColumn.$lastRow.'"/>';
        $xml .= '<sheetViews><sheetView tabSelected="1" workbookViewId="0">';
        $xml .= '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>';
        $xml .= '</sheetView></sheetViews>';
        $xml .= '<sheetFormatPr defaultRowHeight="15"/>';
        $xml .= '<cols>';
        $widths = [8, 18, 28, 14, 24, 18, 18, 22];

        foreach ($widths as $index => $width) {
            $column = $index + 1;
            $xml .= '<col min="'.$column.'" max="'.$column.'" width="'.$width.'" customWidth="1"/>';
        }

        $xml .= '</cols><sheetData>';

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 1;
            $xml .= '<row r="'.$rowNumber.'"'.($rowNumber === 1 ? ' ht="22" customHeight="1"' : '').'>';

            foreach (array_values($row) as $columnIndex => $value) {
                $reference = $this->columnLetter($columnIndex).$rowNumber;
                $style = $rowNumber === 1 ? 1 : 0;

                if (is_int($value) || is_float($value)) {
                    $xml .= '<c r="'.$reference.'" s="'.$style.'"><v>'.$value.'</v></c>';

                    continue;
                }

                $escaped = htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
                $xml .= '<c r="'.$reference.'" s="'.$style.'" t="inlineStr">';
                $xml .= '<is><t xml:space="preserve">'.$escaped.'</t></is></c>';
            }

            $xml .= '</row>';
        }

        $xml .= '</sheetData>';

        if (count($rows) > 0) {
            $xml .= '<autoFilter ref="A1:'.$lastColumn.$lastRow.'"/>';
        }

        $xml .= '</worksheet>';

        return $xml;
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="'.self::WORKSHEET_NS.'">'
            .'<fonts count="2">'
            .'<font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="3">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFEA580C"/><bgColor indexed="64"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    private function workbookXml(): string
    {
        $name = htmlspecialchars($this->safeSheetName(), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="'.self::WORKSHEET_NS.'" xmlns:r="'.self::RELATIONSHIP_NS.'">'
            .'<sheets><sheet name="'.$name.'" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function workbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function rootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private function safeSheetName(): string
    {
        $name = preg_replace('/[\[\]:*?\/\\\\]/', '', $this->sheetName) ?? 'Kehadiran';
        $name = trim(mb_substr($name, 0, 31));

        return $name === '' ? 'Kehadiran' : $name;
    }

    private function columnLetter(int $index): string
    {
        $letter = '';

        do {
            $letter = chr(65 + ($index % 26)).$letter;
            $index = intdiv($index, 26) - 1;
        } while ($index >= 0);

        return $letter;
    }

    private function zip(array $files): string
    {
        $localData = '';
        $centralDirectory = '';
        $offset = 0;
        $timestamp = getdate();
        $dosTime = (($timestamp['hours'] & 0x1F) << 11)
            | (($timestamp['minutes'] & 0x3F) << 5)
            | (($timestamp['seconds'] & 0x3F) >> 1);
        $dosDate = ((($timestamp['year'] - 1980) & 0x7F) << 9)
            | (($timestamp['mon'] & 0x0F) << 5)
            | ($timestamp['mday'] & 0x1F);

        foreach ($files as $name => $contents) {
            $name = str_replace('\\', '/', $name);
            $crc = crc32($contents);
            $size = strlen($contents);
            $nameLength = strlen($name);

            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034B50,
                20,
                0,
                0,
                $dosTime,
                $dosDate,
                $crc,
                $size,
                $size,
                $nameLength,
                0
            );

            $localData .= $localHeader.$name.$contents;

            $centralDirectory .= pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014B50,
                20,
                20,
                0,
                0,
                $dosTime,
                $dosDate,
                $crc,
                $size,
                $size,
                $nameLength,
                0,
                0,
                0,
                0,
                0,
                $offset
            ).$name;

            $offset += strlen($localHeader) + $nameLength + $size;
        }

        $entryCount = count($files);

        $endOfCentralDirectory = pack(
            'VvvvvVVv',
            0x06054B50,
            0,
            0,
            $entryCount,
            $entryCount,
            strlen($centralDirectory),
            strlen($localData),
            0
        );

        return $localData.$centralDirectory.$endOfCentralDirectory;
    }
}

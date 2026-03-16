<?php
// Extrait export stock Excel
use PhpOfficePhpSpreadsheetSpreadsheet;
use PhpOfficePhpSpreadsheetWriterXlsx;
use PhpOfficePhpSpreadsheetStyle{Fill, Alignment, Font, Color};

function exportStockExcel(array $medicaments): void {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Inventaire Stock');

    // En-têtes avec style
    $headers = ['Nom commercial','DCI','Forme','Catégorie','Stock','Seuil','Prix vente','Statut stock'];
    foreach ($headers as $col => $title) {
        $cell = chr(65 + $col) . '1';
        $sheet->setCellValue($cell, $title);
        $sheet->getStyle($cell)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '2E75B6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $sheet->getColumnDimension(chr(65 + $col))->setAutoSize(true);
    }

    // Données
    $row = 2;
    foreach ($medicaments as $med) {
        $statut = $med['stock_total'] <= $med['seuil_minimum'] ? 'ALERTE' : 'OK';
        $data = [
            $med['nom_commercial'], $med['dci'], $med['forme_galenique'],
            $med['categorie_nom'], $med['stock_total'], $med['seuil_minimum'],
            $med['prix_vente'], $statut
        ];
        foreach ($data as $col => $val) {
            $cell = chr(65 + $col) . $row;
            $sheet->setCellValue($cell, $val);
        }
        // Colorer les alertes en rouge
        if ($statut === 'ALERTE') {
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FDECEA']]
            ]);
        }
        $row++;
    }

    // Geler la première ligne
    $sheet->freezePane('A2');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="stock_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');
    (new Xlsx($spreadsheet))->save('php://output');
}

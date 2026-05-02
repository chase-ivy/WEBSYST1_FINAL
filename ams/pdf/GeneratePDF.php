<?php
namespace Classes;

use mikehaertl\pdftk\Pdf;

class GeneratePDF {
    public function generate($data) {
        $outputDir = __DIR__ . '/completed';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', ($data['lrn'] ?? 'unknown') . '_' . ($data['first_name'] ?? 'unknown') . '_' . ($data['last_name'] ?? 'unknown'));
        $filename = 'completed_' . $safeName . '.pdf';
        $outputPath = $outputDir . '/' . $filename;

        $formPath = __DIR__ . '/templates/enrollment_form_compressed.pdf';
        $pdf = new Pdf($formPath);
        $pdf->fillForm($data);
        $pdf->flatten();

        if (!$pdf->saveAs($outputPath)) {
            throw new \RuntimeException('PDF generation failed: ' . $pdf->getError());
        }

        return $outputPath;
    }
}

?>

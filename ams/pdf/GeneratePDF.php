<?php
namespace Classes;

use mikehaertl\pdftk\Pdf;

class GeneratePDF {

    private array $templates = [
        'medical'    => 'medical_form_compressed.pdf',
        'enrollment' => 'enrollment_form_compressed.pdf',
    ];

    /**
     * @param array  $data  Field key-value pairs matching the target PDF's form fields.
     * @param string $type  'medical' | 'enrollment'  (defaults to 'medical')
     */
    public function generate(array $data, string $type = 'medical'): string {
        if (!array_key_exists($type, $this->templates)) {
            throw new \InvalidArgumentException(
                "Unknown form type '{$type}'. Valid types: " . implode(', ', array_keys($this->templates))
            );
        }

        $outputDir = __DIR__ . '/completed';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $safeName = preg_replace(
            '/[^A-Za-z0-9_\-]/', '_',
            ($data['lrn'] ?? 'unknown') . '_' .
            ($data['first_name'] ?? 'unknown') . '_' .
            ($data['last_name'] ?? 'unknown')
        );

        $filename   = $type . '_' . $safeName . '.pdf';
        $outputPath = $outputDir . '/' . $filename;
        $formPath   = __DIR__ . '/templates/' . $this->templates[$type];

        if (!file_exists($formPath)) {
            throw new \RuntimeException("Template not found: {$formPath}");
        }

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
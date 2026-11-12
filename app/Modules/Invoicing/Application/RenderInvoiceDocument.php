<?php

declare(strict_types=1);

namespace App\Modules\Invoicing\Application;

use App\Modules\Invoicing\Domain\InvoiceDocumentLabels;
use App\Modules\Invoicing\Domain\InvoiceType;
use App\Modules\Invoicing\Infrastructure\Models\Invoice;
use App\Modules\Payments\Application\Contracts\PaymentLookupInterface;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Renders an issued invoice or credit note as a PDF (cahier §6.7). The
 * document is generated from the immutable record each time, never stored:
 * since an issued document cannot change, the output is always the same.
 */
class RenderInvoiceDocument
{
    public function __construct(private readonly PaymentLookupInterface $payments) {}

    /** The HTML the PDF is made from - kept separate so it can be inspected and tested. */
    public function html(Invoice $invoice, string $locale = 'fr'): string
    {
        $invoice->loadMissing('lines');
        $locale = InvoiceDocumentLabels::normalize($locale);
        $labels = InvoiceDocumentLabels::for($locale);
        $isCredit = $invoice->type === InvoiceType::CreditNote;

        return view('invoicing::document', [
            'invoice' => $invoice,
            't' => $labels,
            'title' => $isCredit ? $labels['credit_note'] : $labels['invoice'],
            'company' => (array) config('invoicing.company'),
            'methodLabel' => $this->methodLabel($invoice, $labels),
            // A credit note names the invoice it cancels.
            'creditedNumber' => $invoice->credited_invoice_id !== null
                ? Invoice::query()->whereKey($invoice->credited_invoice_id)->value('number')
                : null,
        ])->render();
    }

    /**
     * How the order was settled, when a payment was received ("Mode de règlement").
     *
     * @param  array<string, mixed>  $labels
     */
    private function methodLabel(Invoice $invoice, array $labels): ?string
    {
        $payment = $this->payments->collectedForOrders([$invoice->order_number])[$invoice->order_number] ?? null;

        if ($payment === null) {
            return null;
        }

        $label = $labels[$payment->method] ?? $payment->method;

        return $payment->provider !== null ? $label.' ('.ucwords(str_replace('_', ' ', $payment->provider)).')' : $label;
    }

    public function pdf(Invoice $invoice, string $locale = 'fr'): string
    {
        return Pdf::loadHTML($this->html($invoice, $locale))
            ->setPaper('a4')
            // Untrusted text (customer names, reasons) must never pull in a remote file.
            ->setOption('isRemoteEnabled', false)
            ->output();
    }

    public function filename(Invoice $invoice): string
    {
        return $invoice->number.'.pdf';
    }
}

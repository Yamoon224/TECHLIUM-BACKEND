<?php

declare(strict_types=1);

namespace App\Modules\Invoicing\Domain;

/**
 * The fixed wording of an invoice / credit note PDF, in the customer's
 * language. The legal content (numbers, amounts, issuer) is the same in both;
 * only the labels change. French is the reference.
 */
final class InvoiceDocumentLabels
{
    /**
     * @return array<string, mixed>
     */
    public static function for(string $locale): array
    {
        return $locale === 'en' ? self::english() : self::french();
    }

    /** Anything but "en" is French. */
    public static function normalize(?string $locale): string
    {
        return $locale === 'en' ? 'en' : 'fr';
    }

    /**
     * @return array<string, mixed>
     */
    private static function french(): array
    {
        return [
            'lang' => 'fr',
            'invoice' => 'FACTURE',
            'credit_note' => 'AVOIR',
            'address' => 'Adresse :',
            'phone' => 'Tel :',
            'email' => 'Email :',
            'number' => 'N°',
            'date' => 'Date :',
            'at' => 'à',
            'supplier' => 'Fournisseur',
            'customer' => 'Client',
            'order' => 'Commande :',
            'cancels' => 'Annule la facture :',
            'credit_reason' => "Motif de l'avoir :",
            'description' => 'Désignation',
            'quantity' => 'Quantité',
            'unit' => 'Unité',
            'piece' => 'pièce',
            'unit_price' => 'Prix Unitaire',
            'line_total' => 'Total',
            'discount' => 'Remise',
            'total' => 'TOTAL:',
            'payment_method' => 'Mode de règlement :',
            'cod' => 'Paiement à la livraison',
            'cash' => 'Espèces',
            'mobile_money' => 'Mobile money',
            'conditions_title' => 'CONDITIONS & GARANTIES',
            'conditions' => [
                'Garantie 1 an pieces, hors casse.',
                'IMEI doit correspondre a celui de cette facture.',
                'Choc, fissure, oxydation (eau), ouverture par un tiers : garantie annulee.',
                'Presentation de cette facture obligatoire pour tout SAV.',
            ],
            'legal_title' => 'MENTIONS LÉGALES',
            'legal' => [
                'TVA non applicable, dispense de declaration (micro-entreprise).',
                'Aucun escompte accorde en cas de paiement anticipe.',
                'Reclamation a formuler dans les 48h suivant la livraison.',
            ],
            'signature' => 'Signature & Cachet',
            'thanks' => 'Merci pour votre fidélité',
            'page' => 'Page',
            'of' => 'sur',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function english(): array
    {
        return [
            'lang' => 'en',
            'invoice' => 'INVOICE',
            'credit_note' => 'CREDIT NOTE',
            'address' => 'Address:',
            'phone' => 'Tel:',
            'email' => 'Email:',
            'number' => 'No.',
            'date' => 'Date:',
            'at' => 'at',
            'supplier' => 'Supplier',
            'customer' => 'Customer',
            'order' => 'Order:',
            'cancels' => 'Cancels invoice:',
            'credit_reason' => 'Reason for the credit note:',
            'description' => 'Description',
            'quantity' => 'Quantity',
            'unit' => 'Unit',
            'piece' => 'piece',
            'unit_price' => 'Unit price',
            'line_total' => 'Total',
            'discount' => 'Discount',
            'total' => 'TOTAL:',
            'payment_method' => 'Payment method:',
            'cod' => 'Cash on delivery',
            'cash' => 'Cash',
            'mobile_money' => 'Mobile money',
            'conditions_title' => 'TERMS & WARRANTY',
            'conditions' => [
                '1-year warranty on parts, excluding breakage.',
                'The IMEI must match the one on this invoice.',
                'Impact, cracks, water damage, opening by a third party: warranty void.',
                'This invoice must be presented for any after-sales service.',
            ],
            'legal_title' => 'LEGAL NOTICES',
            'legal' => [
                'VAT not applicable, exempt from filing (micro-enterprise).',
                'No discount is granted for early payment.',
                'Claims must be made within 48 hours of delivery.',
            ],
            'signature' => 'Signature & Stamp',
            'thanks' => 'Thank you for your loyalty',
            'page' => 'Page',
            'of' => 'of',
        ];
    }
}

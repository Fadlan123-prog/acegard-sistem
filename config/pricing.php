<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Minimum transaction amount
    |--------------------------------------------------------------------------
    |
    | There is no server-side product price catalog yet — price/unit_price is
    | still submitted by the client on invoice/quotation/sales-order forms and
    | trusted as-is (see the security audit finding C-5). Until a real price
    | catalog exists, this floor is a blunt but honest safety net that blocks
    | the most obvious price manipulation (submitting 0 or a token amount for
    | a paid job) without guessing at real product prices. Every submission is
    | also written to the audit log (Invoice/InvoiceBuilding/PpfInvoice/
    | SalesOrder all use the Auditable trait) so deviations are traceable.
    |
    */
    'minimum_transaction_amount' => (int) env('PRICING_MIN_TRANSACTION_AMOUNT', 10000),
];

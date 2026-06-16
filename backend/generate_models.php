<?php

$models = [
    'SupplierQuotation' => ['SupplierQuotationItem', 'supplier_quotations', true],
    'SupplierQuotationItem' => [null, 'supplier_quotation_items', false],
    'PurchaseOrderItem' => [null, 'purchase_order_items', false],
    'GoodsReceiptNote' => ['GrnItem', 'goods_receipt_notes', true],
    'GrnItem' => [null, 'grn_items', false],
    'PurchaseReturn' => ['PurchaseReturnItem', 'purchase_returns', true],
    'PurchaseReturnItem' => [null, 'purchase_return_items', false],
    'CustomerQuotation' => ['CustomerQuotationItem', 'customer_quotations', true],
    'CustomerQuotationItem' => [null, 'customer_quotation_items', false],
    'DeliveryNote' => ['DeliveryNoteItem', 'delivery_notes', true],
    'DeliveryNoteItem' => [null, 'delivery_note_items', false],
    'SalesReturn' => ['SalesReturnItem', 'sales_returns', true],
    'SalesReturnItem' => [null, 'sales_return_items', false],
];

foreach ($models as $modelName => $info) {
    $hasBranch = $info[2];
    $tableName = $info[1];
    
    $branchTraitUse = $hasBranch ? "\n    use \\App\\Traits\\BelongsToBranch;\n" : "";
    
    $content = "<?php\n\nnamespace App\\Models;\n\nuse Illuminate\\Database\\Eloquent\\Model;\n\nclass {$modelName} extends Model\n{{$branchTraitUse}\n    protected \$guarded = [];\n}\n";
    
    file_put_contents(__DIR__ . "/app/Models/{$modelName}.php", $content);
}

echo "Models generated.\n";

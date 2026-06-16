<?php

$controllers = [
    'PurchaseQuotationController',
    'PurchaseOrderController',
    'GoodsReceiptNoteController',
    'PurchaseReturnController',
    'SalesQuotationController',
    'SalesOrderController',
    'DeliveryNoteController',
    'SalesReturnController'
];

foreach ($controllers as $controller) {
    $content = "<?php\n\nnamespace App\\Http\\Controllers;\n\nuse Illuminate\\Http\\Request;\nuse Illuminate\\Support\\Facades\\Auth;\n\nclass {$controller} extends Controller\n{\n    //\n}\n";
    file_put_contents(__DIR__ . "/app/Http/Controllers/{$controller}.php", $content);
}

echo "Controllers generated.\n";

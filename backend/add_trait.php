<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$models = ['Attendance', 'Customer', 'Employee', 'Inventory', 'InventoryTransaction', 'Invoice', 'Payment', 'Payroll', 'PurchaseOrder', 'SalesOrder', 'SalesOrderItem', 'Supplier', 'Task'];
foreach ($models as $model) {
    $path = app_path('Models/' . $model . '.php');
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (strpos($content, 'use App\Traits\BelongsToBranch;') === false) {
            $content = str_replace('use Illuminate\Database\Eloquent\Model;', "use Illuminate\Database\Eloquent\Model;\nuse App\Traits\BelongsToBranch;", $content);
            $content = str_replace('use HasFactory;', "use HasFactory, BelongsToBranch;", $content);
            if (strpos($content, 'use HasFactory, BelongsToBranch;') === false && strpos($content, 'use HasFactory;') === false) {
                $content = preg_replace('/class\s+'.$model.'\s+extends\s+Model\s*\{/', "class $model extends Model\n{\n    use BelongsToBranch;", $content);
            }
            file_put_contents($path, $content);
            echo "Updated $model\n";
        }
    }
}
echo "Done.\n";

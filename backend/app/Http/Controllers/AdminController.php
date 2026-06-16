<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    // List of tables we want to manage (hiding system tables)
    protected $allowedTables = [
        'users', 'employees', 'branches', 'products', 'categories', 
        'inventories', 'inventory_transactions', 'invoices', 'sales_orders', 
        'purchase_orders', 'customers', 'suppliers', 'leaves', 'tasks', 'payrolls'
    ];

    public function getTables()
    {
        return response()->json($this->allowedTables);
    }

    public function getTableSchema($table)
    {
        if (!in_array($table, $this->allowedTables)) {
            return response()->json(['error' => 'Table not allowed'], 403);
        }

        $columns = Schema::getColumnListing($table);
        // Map columns to basic types (ignoring complex doctrine parsing for simplicity)
        $schema = [];
        foreach ($columns as $column) {
            $schema[] = [
                'name' => $column,
                // If it ends in _id, we know it's a foreign key, otherwise string
                'type' => str_ends_with($column, '_id') ? 'integer' : 'string',
                'is_primary' => $column === 'id'
            ];
        }

        return response()->json($schema);
    }

    public function getTableData($table)
    {
        if (!in_array($table, $this->allowedTables)) {
            return response()->json(['error' => 'Table not allowed'], 403);
        }

        $data = DB::table($table)->orderBy('id', 'desc')->get();
        return response()->json($data);
    }

    public function createRecord(Request $request, $table)
    {
        if (!in_array($table, $this->allowedTables)) {
            return response()->json(['error' => 'Table not allowed'], 403);
        }

        $data = $request->except(['id']);
        
        // Handle timestamps if table has them
        if (Schema::hasColumn($table, 'created_at')) {
            $data['created_at'] = now();
            $data['updated_at'] = now();
        }

        $id = DB::table($table)->insertGetId($data);

        return response()->json(['message' => 'Created successfully', 'id' => $id]);
    }

    public function updateRecord(Request $request, $table, $id)
    {
        if (!in_array($table, $this->allowedTables)) {
            return response()->json(['error' => 'Table not allowed'], 403);
        }

        $data = $request->except(['id', 'created_at', 'updated_at']);

        if (Schema::hasColumn($table, 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::table($table)->where('id', $id)->update($data);

        return response()->json(['message' => 'Updated successfully']);
    }

    public function deleteRecord($table, $id)
    {
        if (!in_array($table, $this->allowedTables)) {
            return response()->json(['error' => 'Table not allowed'], 403);
        }

        DB::table($table)->where('id', $id)->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}

<?php

namespace App\Support\Reports\Modules;

use App\Support\Reports\Spec;

/**
 * Inventory reports.
 *
 * What the company owns, where it is, and how it got there: the product
 * master, the stock on hand at each location, and every movement in and out
 * of the warehouse.
 */
class Inventory
{
    public static function modules(): array
    {
        return [
            'inventory' => self::inventory(),
            'products' => self::products(),
            'categories' => self::categories(),
            'units' => self::units(),
            'unit-conversions' => self::unitConversions(),
            'stock' => self::stock(),
            'stock-transactions' => self::stockTransactions(),
            'stock-requests' => self::stockRequests(),
            'stock-transfers' => self::stockTransfers(),
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function inventory(): array
    {
        $product = Spec::join('products', 'product_stock.product_id', 'products.id');
        $category = Spec::join('categories', 'products.category_id', 'categories.id', 'left', 'product_category');
        $site = Spec::join('sites', 'product_stock.location_id', 'sites.id', 'left', 'stock_site');

        return [
            'label' => 'Inventory',
            'category' => 'Inventory',
            'permission' => 'inventory.view',
            'reports' => [

                'stock-overview' => [
                    'label' => 'Stock Overview',
                    'description' => 'What is on hand, and what it is worth.',
                    'base' => 'product_stock',
                    'joins' => [$product, $category, $site],
                    'columns' => [
                        Spec::col('product', 'Product', 'products.name', ['primary' => true]),
                        Spec::col('sku', 'SKU', 'products.sku'),
                        Spec::col('category', 'Category', 'product_category.name'),
                        Spec::col('site', 'Location', 'stock_site.name'),
                        Spec::num('quantity', 'On hand', 'product_stock.quantity'),
                        Spec::num('reserved', 'Reserved', 'product_stock.reserved_quantity'),
                        Spec::num('available', 'Available', 'product_stock.available_quantity'),
                        Spec::money('value', 'Stock value', 'product_stock.quantity * products.cost_price'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('category_id', 'Category', 'categories', 'id'),
                        Spec::textFilter('q', 'Search', ['products.name', 'products.sku']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Stock lines', 'count(*)'),
                        Spec::summary('products', 'Products', 'count(distinct product_stock.product_id)'),
                        Spec::summary('quantity', 'Total units', 'sum(product_stock.quantity)'),
                        Spec::summary('value', 'Stock value', 'sum(product_stock.quantity * products.cost_price)', 'money'),
                    ],
                    'order' => [Spec::order('quantity', 'desc')],
                ],

                'stock-by-category' => [
                    'label' => 'Stock by Category',
                    'description' => 'How the warehouse is split across the product catalogue.',
                    'base' => 'product_stock',
                    'joins' => [$product, $category],
                    'group_by' => ['product_category.name'],
                    'columns' => [
                        Spec::col('category', 'Category', 'product_category.name', ['primary' => true]),
                        Spec::num('products', 'Products', 'count(distinct products.id)'),
                        Spec::num('lines', 'Stock lines', 'count(*)'),
                        Spec::num('quantity', 'Units', 'sum(product_stock.quantity)'),
                        Spec::money('value', 'Value', 'sum(product_stock.quantity * products.cost_price)'),
                    ],
                    'summary' => [
                        Spec::summary('categories', 'Categories', 'count(distinct product_category.id)'),
                        Spec::summary('value', 'Stock value', 'sum(product_stock.quantity * products.cost_price)', 'money'),
                    ],
                    'order' => [Spec::order('value', 'desc')],
                    'chart' => Spec::chart('bar', 'category', 'value'),
                ],

                'stock-by-location' => [
                    'label' => 'Stock by Location',
                    'description' => 'How much stock each site is holding.',
                    'base' => 'product_stock',
                    'joins' => [$product, $site],
                    'group_by' => ['stock_site.id', 'stock_site.name'],
                    'columns' => [
                        Spec::col('site', 'Location', 'stock_site.name', ['primary' => true]),
                        Spec::num('products', 'Products', 'count(distinct products.id)'),
                        Spec::num('lines', 'Stock lines', 'count(*)'),
                        Spec::num('quantity', 'Units', 'sum(product_stock.quantity)'),
                        Spec::money('value', 'Value', 'sum(product_stock.quantity * products.cost_price)'),
                    ],
                    'summary' => [
                        Spec::summary('locations', 'Locations', 'count(distinct stock_site.id)'),
                        Spec::summary('quantity', 'Total units', 'sum(product_stock.quantity)'),
                    ],
                    'order' => [Spec::order('quantity', 'desc')],
                    'chart' => Spec::chart('bar', 'site', 'quantity'),
                ],

                'valuation' => [
                    'label' => 'Inventory Valuation',
                    'description' => 'Stock valued at cost, at purchase price and at selling price.',
                    'base' => 'product_stock',
                    'joins' => [$product],
                    'group_by' => ['products.id', 'products.name', 'products.sku'],
                    'columns' => [
                        Spec::col('product', 'Product', 'products.name', ['primary' => true]),
                        Spec::col('sku', 'SKU', 'products.sku'),
                        Spec::num('quantity', 'On hand', 'sum(product_stock.quantity)'),
                        Spec::money('cost_value', 'At cost', 'sum(product_stock.quantity * products.cost_price)'),
                        Spec::money('purchase_value', 'At purchase price', 'sum(product_stock.quantity * products.purchase_price)'),
                        Spec::money('sale_value', 'At selling price', 'sum(product_stock.quantity * products.selling_price)'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['products.name', 'products.sku']),
                    ],
                    'summary' => [
                        Spec::summary('products', 'Products', 'count(distinct products.id)'),
                        Spec::summary('cost_value', 'Value at cost', 'sum(product_stock.quantity * products.cost_price)', 'money'),
                        Spec::summary('sale_value', 'Value at sale', 'sum(product_stock.quantity * products.selling_price)', 'money'),
                    ],
                    'order' => [Spec::order('cost_value', 'desc')],
                    'chart' => Spec::chart('bar', 'product', 'cost_value'),
                ],

                'stock-health' => [
                    'label' => 'Stock Health',
                    'description' => 'Which products are short, healthy or overstocked.',
                    'base' => 'product_stock',
                    'joins' => [$product],
                    'group_by' => ['products.id', 'products.name', 'products.sku', 'products.reorder_level', 'products.min_stock', 'products.max_stock'],
                    'columns' => [
                        Spec::col('product', 'Product', 'products.name', ['primary' => true]),
                        Spec::col('sku', 'SKU', 'products.sku'),
                        Spec::num('on_hand', 'On hand', 'sum(product_stock.quantity)'),
                        Spec::num('reorder_level', 'Reorder level', 'products.reorder_level'),
                        Spec::num('min_stock', 'Min stock', 'products.min_stock'),
                        Spec::num('max_stock', 'Max stock', 'products.max_stock'),
                        Spec::col('health', 'Health', "case when sum(product_stock.quantity) <= products.min_stock then 'Critical' when sum(product_stock.quantity) <= products.reorder_level then 'Reorder' when sum(product_stock.quantity) >= products.max_stock then 'Overstock' else 'Healthy' end"),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['products.name', 'products.sku']),
                    ],
                    'summary' => [
                        Spec::summary('products', 'Products', 'count(*)'),
                        Spec::summary('critical', 'Critical', 'sum(case when on_hand <= min_stock then 1 else 0 end)'),
                        Spec::summary('reorder', 'Reorder', 'sum(case when on_hand > min_stock and on_hand <= reorder_level then 1 else 0 end)'),
                        Spec::summary('overstock', 'Overstock', 'sum(case when on_hand >= max_stock then 1 else 0 end)'),
                    ],
                    'summary_grouped' => true,
                    'order' => [Spec::order('on_hand')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function products(): array
    {
        $category = Spec::join('categories', 'products.category_id', 'categories.id', 'left', 'product_category');
        $supplier = Spec::join('suppliers', 'products.supplier_id', 'suppliers.id', 'left', 'product_supplier');
        $unit = Spec::join('units', 'products.unit_id', 'units.id', 'left', 'product_unit');

        return [
            'label' => 'Products',
            'category' => 'Inventory',
            'permission' => 'products.view',
            'reports' => [

                'master' => [
                    'label' => 'Product Master',
                    'description' => 'Every product, with its codes and prices.',
                    'base' => 'products',
                    'joins' => [$category, $supplier, $unit],
                    'columns' => [
                        Spec::col('name', 'Product', 'products.name', ['primary' => true]),
                        Spec::col('sku', 'SKU', 'products.sku'),
                        Spec::col('product_code', 'Code', 'products.product_code'),
                        Spec::col('hsn_code', 'HSN', 'products.hsn_code'),
                        Spec::col('category', 'Category', 'product_category.name'),
                        Spec::col('unit', 'Unit', 'product_unit.name'),
                        Spec::money('purchase_price', 'Purchase', 'products.purchase_price'),
                        Spec::money('selling_price', 'Selling', 'products.selling_price'),
                        Spec::badge('status', 'Status', 'products.status'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('status', 'Status', 'products', 'status'),
                        Spec::sourceFilter('category_id', 'Category', 'categories', 'id'),
                        Spec::textFilter('q', 'Search', ['products.name', 'products.sku', 'products.hsn_code']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Products', 'count(*)'),
                        Spec::summary('active', 'Active', "sum(case when products.status = 'active' then 1 else 0 end)"),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'by-category' => [
                    'label' => 'Products by Category',
                    'description' => 'How big each part of the catalogue is.',
                    'base' => 'products',
                    'joins' => [$category],
                    'group_by' => ['product_category.name'],
                    'columns' => [
                        Spec::col('category', 'Category', 'product_category.name', ['primary' => true]),
                        Spec::num('products', 'Products', 'count(distinct products.id)'),
                        Spec::money('average_purchase', 'Avg purchase', 'round(avg(products.purchase_price), 2)'),
                        Spec::money('average_selling', 'Avg selling', 'round(avg(products.selling_price), 2)'),
                    ],
                    'summary' => [
                        Spec::summary('categories', 'Categories', 'count(distinct product_category.id)'),
                        Spec::summary('products', 'Products', 'count(distinct products.id)'),
                    ],
                    'order' => [Spec::order('products', 'desc')],
                    'chart' => Spec::chart('bar', 'category', 'products'),
                ],

                'price-list' => [
                    'label' => 'Price List',
                    'description' => 'The buying and selling price of every product.',
                    'base' => 'products',
                    'joins' => [$category],
                    'columns' => [
                        Spec::col('name', 'Product', 'products.name', ['primary' => true]),
                        Spec::col('sku', 'SKU', 'products.sku'),
                        Spec::col('category', 'Category', 'product_category.name'),
                        Spec::money('cost_price', 'Cost', 'products.cost_price'),
                        Spec::money('purchase_price', 'Purchase', 'products.purchase_price'),
                        Spec::money('selling_price', 'Selling', 'products.selling_price'),
                        Spec::money('margin', 'Margin', 'products.selling_price - products.purchase_price'),
                        Spec::percent('margin_percent', 'Margin %', "case when products.purchase_price > 0 then round((products.selling_price - products.purchase_price) * 100 / products.purchase_price, 2) else 0 end"),
                    ],
                    'filters' => [
                        Spec::sourceFilter('category_id', 'Category', 'categories', 'id'),
                        Spec::textFilter('q', 'Search', ['products.name', 'products.sku']),
                    ],
                    'summary' => [
                        Spec::summary('products', 'Products', 'count(*)'),
                        Spec::summary('average_margin', 'Average margin', 'round(avg(products.selling_price - products.purchase_price), 2)', 'money'),
                    ],
                    'order' => [Spec::order('margin', 'desc')],
                ],

                'by-supplier' => [
                    'label' => 'Products by Supplier',
                    'description' => 'Which supplier each product comes from.',
                    'base' => 'products',
                    'joins' => [$supplier],
                    'group_by' => ['product_supplier.id', 'product_supplier.name'],
                    'columns' => [
                        Spec::col('supplier', 'Supplier', 'product_supplier.name', ['primary' => true]),
                        Spec::num('products', 'Products', 'count(distinct products.id)'),
                        Spec::money('average_purchase', 'Avg purchase price', 'round(avg(products.purchase_price), 2)'),
                        Spec::money('average_selling', 'Avg selling price', 'round(avg(products.selling_price), 2)'),
                    ],
                    'summary' => [
                        Spec::summary('suppliers', 'Suppliers', 'count(distinct product_supplier.id)'),
                        Spec::summary('products', 'Products', 'count(distinct products.id)'),
                    ],
                    'order' => [Spec::order('products', 'desc')],
                    'chart' => Spec::chart('bar', 'supplier', 'products'),
                ],

                'reorder-list' => [
                    'label' => 'Reorder List',
                    'description' => 'The products that need buying in again.',
                    'base' => 'products',
                    'joins' => [Spec::join('product_stock', 'product_stock.product_id', 'products.id')],
                    'group_by' => ['products.id', 'products.name', 'products.sku', 'products.reorder_level'],
                    'columns' => [
                        Spec::col('product', 'Product', 'products.name', ['primary' => true]),
                        Spec::col('sku', 'SKU', 'products.sku'),
                        Spec::num('on_hand', 'On hand', 'sum(product_stock.quantity)'),
                        Spec::num('reorder_level', 'Reorder level', 'products.reorder_level'),
                        Spec::num('shortfall', 'Shortfall', "case when sum(product_stock.quantity) < products.reorder_level then products.reorder_level - sum(product_stock.quantity) else 0 end"),
                        Spec::money('purchase_price', 'Purchase price', 'products.purchase_price'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['products.name', 'products.sku']),
                    ],
                    'summary' => [
                        Spec::summary('products', 'Products', 'count(*)'),
                        Spec::summary('shortfall', 'Units short', 'sum(case when on_hand < reorder_level then reorder_level - on_hand else 0 end)'),
                    ],
                    'summary_grouped' => true,
                    'order' => [Spec::order('shortfall', 'desc')],
                ],

                'product-descriptions' => [
                    'label' => 'Product Descriptions',
                    'description' => 'The catalogue details, for reference and reprinting.',
                    'base' => 'products',
                    'joins' => [$category, $unit],
                    'columns' => [
                        Spec::col('name', 'Product', 'products.name', ['primary' => true]),
                        Spec::col('description', 'Description', 'products.description'),
                        Spec::col('dimension', 'Dimensions', 'products.dimension'),
                        Spec::col('category', 'Category', 'product_category.name'),
                        Spec::col('unit', 'Unit', 'product_unit.name'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['products.name', 'products.description']),
                    ],
                    'summary' => [Spec::summary('total', 'Products', 'count(*)')],
                    'order' => [Spec::order('name')],
                ],

                'product-status' => [
                    'label' => 'Product Status Summary',
                    'description' => 'Which products are live and which have been retired.',
                    'base' => 'products',
                    'group_by' => ['products.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'products.status', ['primary' => true]),
                        Spec::num('products', 'Products', 'count(*)'),
                        Spec::money('stock_value', 'Stock value', 'sum(products.opening_stock * products.cost_price)'),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Products', 'count(*)'),
                    ],
                    'order' => [Spec::order('products', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'products'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function categories(): array
    {
        return [
            'label' => 'Categories',
            'category' => 'Inventory',
            'permission' => 'categories.view',
            'reports' => [

                'master' => [
                    'label' => 'Category Master',
                    'description' => 'The product category tree.',
                    'base' => 'categories',
                    'joins' => [
                        Spec::join('categories', 'categories.parent_id', 'parent_categories.id', 'left', 'parent_categories'),
                    ],
                    'columns' => [
                        Spec::col('name', 'Category', 'categories.name', ['primary' => true]),
                        Spec::col('parent', 'Parent', 'parent_categories.name'),
                        Spec::col('description', 'Description', 'categories.description'),
                        Spec::badge('status', 'Status', 'categories.status'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('status', 'Status', 'categories', 'status'),
                        Spec::textFilter('q', 'Search', ['categories.name']),
                    ],
                    'summary' => [Spec::summary('total', 'Categories', 'count(*)')],
                    'order' => [Spec::order('name')],
                ],

                'product-count' => [
                    'label' => 'Products per Category',
                    'description' => 'How many products sit under each category.',
                    'base' => 'categories',
                    'joins' => [Spec::join('products', 'products.category_id', 'categories.id')],
                    'group_by' => ['categories.name'],
                    'columns' => [
                        Spec::col('category', 'Category', 'categories.name', ['primary' => true]),
                        Spec::num('products', 'Products', 'count(distinct products.id)'),
                        Spec::money('stock_value', 'Opening stock value', 'sum(products.opening_stock * products.cost_price)'),
                    ],
                    'summary' => [
                        Spec::summary('categories', 'Categories', 'count(distinct categories.id)'),
                        Spec::summary('products', 'Products', 'count(distinct products.id)'),
                    ],
                    'order' => [Spec::order('products', 'desc')],
                    'chart' => Spec::chart('bar', 'category', 'products'),
                ],

                'category-hierarchy' => [
                    'label' => 'Category Hierarchy',
                    'description' => 'The parent-child structure of the catalogue.',
                    'base' => 'categories',
                    'joins' => [
                        Spec::join('categories', 'categories.parent_id', 'parent_categories.id', 'left', 'parent_categories'),
                    ],
                    'columns' => [
                        Spec::col('category', 'Category', 'categories.name', ['primary' => true]),
                        Spec::col('parent', 'Parent', 'parent_categories.name'),
                        Spec::badge('status', 'Status', 'categories.status'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['categories.name']),
                    ],
                    'summary' => [Spec::summary('total', 'Categories', 'count(*)')],
                    'order' => [Spec::order('category')],
                ],

                'category-value' => [
                    'label' => 'Category Stock Value',
                    'description' => 'What each category is worth on the shelf.',
                    'base' => 'product_stock',
                    'joins' => [
                        Spec::join('products', 'product_stock.product_id', 'products.id'),
                        Spec::join('categories', 'products.category_id', 'categories.id', 'left', 'product_category'),
                    ],
                    'group_by' => ['product_category.name'],
                    'columns' => [
                        Spec::col('category', 'Category', 'product_category.name', ['primary' => true]),
                        Spec::num('products', 'Products', 'count(distinct products.id)'),
                        Spec::num('quantity', 'Units', 'sum(product_stock.quantity)'),
                        Spec::money('value', 'Value', 'sum(product_stock.quantity * products.cost_price)'),
                    ],
                    'summary' => [
                        Spec::summary('value', 'Total value', 'sum(product_stock.quantity * products.cost_price)', 'money'),
                    ],
                    'order' => [Spec::order('value', 'desc')],
                    'chart' => Spec::chart('bar', 'category', 'value'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function units(): array
    {
        return [
            'label' => 'Units',
            'category' => 'Inventory',
            'permission' => 'units.view',
            'reports' => [

                'master' => [
                    'label' => 'Unit Master',
                    'description' => 'Every unit of measure the catalogue uses.',
                    'base' => 'units',
                    'columns' => [
                        Spec::col('name', 'Unit', 'units.name', ['primary' => true]),
                        Spec::col('code', 'Code', 'units.code'),
                        Spec::col('type', 'Type', 'units.type'),
                        Spec::badge('status', 'Status', 'units.status'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('type', 'Type', 'units', 'type'),
                        Spec::textFilter('q', 'Search', ['units.name', 'units.code']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Units', 'count(*)'),
                        Spec::summary('types', 'Types', 'count(distinct units.type)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'by-type' => [
                    'label' => 'Units by Type',
                    'description' => 'How the units are grouped - weight, volume, length and so on.',
                    'base' => 'units',
                    'group_by' => ['units.type'],
                    'columns' => [
                        Spec::col('type', 'Type', 'units.type', ['primary' => true]),
                        Spec::num('units', 'Units', 'count(*)'),
                        Spec::num('active', 'Active', "sum(case when units.status = 'active' then 1 else 0 end)"),
                    ],
                    'summary' => [
                        Spec::summary('types', 'Types', 'count(distinct units.type)'),
                    ],
                    'order' => [Spec::order('units', 'desc')],
                    'chart' => Spec::chart('pie', 'type', 'units'),
                ],

                'product-usage' => [
                    'label' => 'Products per Unit',
                    'description' => 'How much of the catalogue is measured in each unit.',
                    'base' => 'products',
                    'joins' => [Spec::join('units', 'products.unit_id', 'units.id', 'left', 'product_unit')],
                    'group_by' => ['product_unit.name'],
                    'columns' => [
                        Spec::col('unit', 'Unit', 'product_unit.name', ['primary' => true]),
                        Spec::num('products', 'Products', 'count(distinct products.id)'),
                        Spec::money('stock_value', 'Stock value', 'sum(products.opening_stock * products.cost_price)'),
                    ],
                    'summary' => [
                        Spec::summary('units', 'Units in use', 'count(distinct products.unit_id)'),
                        Spec::summary('products', 'Products', 'count(distinct products.id)'),
                    ],
                    'order' => [Spec::order('products', 'desc')],
                    'chart' => Spec::chart('bar', 'unit', 'products'),
                ],

                'unit-status' => [
                    'label' => 'Unit Status Summary',
                    'description' => 'Which units are still in use.',
                    'base' => 'units',
                    'group_by' => ['units.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'units.status', ['primary' => true]),
                        Spec::num('units', 'Units', 'count(*)'),
                    ],
                    'summary' => [Spec::summary('total', 'Units', 'count(*)')],
                    'order' => [Spec::order('units', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'units'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function unitConversions(): array
    {
        $from = Spec::join('units', 'unit_conversions.from_unit_id', 'from_units.id', 'left', 'from_units');
        $to = Spec::join('units', 'unit_conversions.to_unit_id', 'to_units.id', 'left', 'to_units');

        return [
            'label' => 'Unit Conversions',
            'category' => 'Inventory',
            'permission' => 'unit-conversions.view',
            'reports' => [

                'master' => [
                    'label' => 'Unit Conversion Master',
                    'description' => 'Every conversion factor between two units.',
                    'base' => 'unit_conversions',
                    'joins' => [$from, $to],
                    'columns' => [
                        Spec::col('from_unit', 'From', 'from_units.name', ['primary' => true]),
                        Spec::col('to_unit', 'To', 'to_units.name'),
                        Spec::num('factor', 'Factor', 'unit_conversions.conversion_factor'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['from_units.name', 'to_units.name']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Conversions', 'count(*)'),
                        Spec::summary('average_factor', 'Average factor', 'round(avg(unit_conversions.conversion_factor), 4)'),
                    ],
                    'order' => [Spec::order('from_unit')],
                ],

                'conversions-by-unit' => [
                    'label' => 'Conversions by Source Unit',
                    'description' => 'How many destinations each unit converts into.',
                    'base' => 'unit_conversions',
                    'joins' => [$from],
                    'group_by' => ['from_units.name'],
                    'columns' => [
                        Spec::col('from_unit', 'From', 'from_units.name', ['primary' => true]),
                        Spec::num('conversions', 'Conversions', 'count(*)'),
                        Spec::num('average_factor', 'Average factor', 'round(avg(unit_conversions.conversion_factor), 4)'),
                        Spec::num('highest_factor', 'Highest factor', 'max(unit_conversions.conversion_factor)'),
                    ],
                    'summary' => [
                        Spec::summary('units', 'Source units', 'count(distinct unit_conversions.from_unit_id)'),
                    ],
                    'order' => [Spec::order('conversions', 'desc')],
                    'chart' => Spec::chart('bar', 'from_unit', 'conversions'),
                ],

                'conversions-by-type' => [
                    'label' => 'Conversions by Unit Type',
                    'description' => 'Conversions that cross between kinds of measure.',
                    'base' => 'unit_conversions',
                    'joins' => [
                        Spec::join('units', 'unit_conversions.from_unit_id', 'from_units.id', 'left', 'from_units'),
                        Spec::join('units', 'unit_conversions.to_unit_id', 'to_units.id', 'left', 'to_units'),
                    ],
                    'group_by' => ['from_units.type'],
                    'columns' => [
                        Spec::col('type', 'Type', 'from_units.type', ['primary' => true]),
                        Spec::num('conversions', 'Conversions', 'count(*)'),
                        Spec::num('average_factor', 'Average factor', 'round(avg(unit_conversions.conversion_factor), 4)'),
                    ],
                    'summary' => [
                        Spec::summary('types', 'Types', 'count(distinct from_units.type)'),
                    ],
                    'order' => [Spec::order('conversions', 'desc')],
                    'chart' => Spec::chart('bar', 'type', 'conversions'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function stock(): array
    {
        $product = Spec::join('products', 'product_stock.product_id', 'products.id');
        $category = Spec::join('categories', 'products.category_id', 'categories.id', 'left', 'product_category');
        $site = Spec::join('sites', 'product_stock.location_id', 'sites.id', 'left', 'stock_site');

        return [
            'label' => 'Stock',
            'category' => 'Inventory',
            'permission' => 'stock.view',
            'reports' => [

                'register' => [
                    'label' => 'Stock Register',
                    'description' => 'Every stock line, with what is on hand and reserved.',
                    'base' => 'product_stock',
                    'joins' => [$product, $category, $site],
                    'columns' => [
                        Spec::col('product', 'Product', 'products.name', ['primary' => true]),
                        Spec::col('sku', 'SKU', 'products.sku'),
                        Spec::col('category', 'Category', 'product_category.name'),
                        Spec::col('site', 'Location', 'stock_site.name'),
                        Spec::num('quantity', 'On hand', 'product_stock.quantity'),
                        Spec::num('reserved', 'Reserved', 'product_stock.reserved_quantity'),
                        Spec::num('available', 'Available', 'product_stock.available_quantity'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('category_id', 'Category', 'categories', 'id'),
                        Spec::textFilter('q', 'Search', ['products.name', 'products.sku']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Stock lines', 'count(*)'),
                        Spec::summary('quantity', 'On hand', 'sum(product_stock.quantity)'),
                        Spec::summary('reserved', 'Reserved', 'sum(product_stock.reserved_quantity)'),
                    ],
                    'order' => [Spec::order('product')],
                ],

                'available-stock' => [
                    'label' => 'Available Stock',
                    'description' => 'What can actually be sold or issued today.',
                    'base' => 'product_stock',
                    'joins' => [$product],
                    'columns' => [
                        Spec::col('product', 'Product', 'products.name', ['primary' => true]),
                        Spec::col('sku', 'SKU', 'products.sku'),
                        Spec::num('quantity', 'On hand', 'product_stock.quantity'),
                        Spec::num('reserved', 'Reserved', 'product_stock.reserved_quantity'),
                        Spec::num('available', 'Available', 'product_stock.available_quantity'),
                        Spec::num('reorder_level', 'Reorder level', 'products.reorder_level'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['products.name', 'products.sku']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Stock lines', 'count(*)'),
                        Spec::summary('available', 'Available', 'sum(product_stock.available_quantity)'),
                    ],
                    'order' => [Spec::order('available', 'desc')],
                ],

                'reserved-stock' => [
                    'label' => 'Reserved Stock',
                    'description' => 'Stock already committed to an order or a request.',
                    'base' => 'product_stock',
                    'joins' => [$product],
                    'columns' => [
                        Spec::col('product', 'Product', 'products.name', ['primary' => true]),
                        Spec::col('sku', 'SKU', 'products.sku'),
                        Spec::num('quantity', 'On hand', 'product_stock.quantity'),
                        Spec::num('reserved', 'Reserved', 'product_stock.reserved_quantity'),
                        Spec::percent('reserved_percent', 'Reserved %', 'case when product_stock.quantity > 0 then round(product_stock.reserved_quantity * 100 / product_stock.quantity, 2) else 0 end'),
                    ],
                    'constraints' => [
                        ['column' => 'product_stock.reserved_quantity', 'operator' => '>', 'value' => 0],
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Reserved lines', 'count(*)'),
                        Spec::summary('reserved', 'Reserved units', 'sum(product_stock.reserved_quantity)'),
                    ],
                    'order' => [Spec::order('reserved', 'desc')],
                ],

                'stock-value-by-category' => [
                    'label' => 'Stock Value by Category',
                    'description' => 'What each category is worth on the shelf.',
                    'base' => 'product_stock',
                    'joins' => [$product, $category],
                    'group_by' => ['product_category.name'],
                    'columns' => [
                        Spec::col('category', 'Category', 'product_category.name', ['primary' => true]),
                        Spec::num('products', 'Products', 'count(distinct products.id)'),
                        Spec::num('quantity', 'Units', 'sum(product_stock.quantity)'),
                        Spec::money('value', 'Value', 'sum(product_stock.quantity * products.cost_price)'),
                    ],
                    'summary' => [
                        Spec::summary('value', 'Total value', 'sum(product_stock.quantity * products.cost_price)', 'money'),
                    ],
                    'order' => [Spec::order('value', 'desc')],
                    'chart' => Spec::chart('bar', 'category', 'value'),
                ],

                'stock-by-product' => [
                    'label' => 'Stock by Product',
                    'description' => 'Where each product is held, across every location.',
                    'base' => 'product_stock',
                    'joins' => [$product, $site],
                    'group_by' => ['products.id', 'products.name', 'products.sku'],
                    'columns' => [
                        Spec::col('product', 'Product', 'products.name', ['primary' => true]),
                        Spec::col('sku', 'SKU', 'products.sku'),
                        Spec::num('locations', 'Locations', 'count(distinct product_stock.location_id)'),
                        Spec::num('quantity', 'On hand', 'sum(product_stock.quantity)'),
                        Spec::num('available', 'Available', 'sum(product_stock.available_quantity)'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['products.name', 'products.sku']),
                    ],
                    'summary' => [
                        Spec::summary('products', 'Products', 'count(distinct products.id)'),
                        Spec::summary('quantity', 'Total units', 'sum(product_stock.quantity)'),
                    ],
                    'order' => [Spec::order('quantity', 'desc')],
                    'chart' => Spec::chart('bar', 'product', 'quantity'),
                ],

                'stock-by-site' => [
                    'label' => 'Stock by Site',
                    'description' => 'How much stock each site is holding.',
                    'base' => 'product_stock',
                    'joins' => [$product, $site],
                    'group_by' => ['stock_site.id', 'stock_site.name'],
                    'columns' => [
                        Spec::col('site', 'Site', 'stock_site.name', ['primary' => true]),
                        Spec::num('products', 'Products', 'count(distinct products.id)'),
                        Spec::num('quantity', 'On hand', 'sum(product_stock.quantity)'),
                        Spec::num('reserved', 'Reserved', 'sum(product_stock.reserved_quantity)'),
                        Spec::money('value', 'Value', 'sum(product_stock.quantity * products.cost_price)'),
                    ],
                    'summary' => [
                        Spec::summary('sites', 'Sites', 'count(distinct stock_site.id)'),
                        Spec::summary('quantity', 'Total units', 'sum(product_stock.quantity)'),
                    ],
                    'order' => [Spec::order('quantity', 'desc')],
                    'chart' => Spec::chart('bar', 'site', 'quantity'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function stockTransactions(): array
    {
        $product = Spec::join('products', 'transaction_ledger.product_id', 'products.id', 'left', 'txn_product');
        $site = Spec::join('sites', 'transaction_ledger.location_id', 'sites.id', 'left', 'txn_site');
        $toSite = Spec::join('sites', 'transaction_ledger.to_location_id', 'to_sites.id', 'left', 'to_sites');

        return [
            'label' => 'Stock Transactions',
            'category' => 'Inventory',
            'permission' => 'stock-transactions.view',
            'reports' => [

                'register' => [
                    'label' => 'Stock Transaction Register',
                    'description' => 'Every movement in and out of the warehouse.',
                    'base' => 'transaction_ledger',
                    'joins' => [$product, $site, $toSite],
                    'columns' => [
                        Spec::col('transaction_number', 'Transaction', 'transaction_ledger.transaction_number', ['primary' => true]),
                        Spec::datetime('created_at', 'When', 'transaction_ledger.created_at'),
                        Spec::col('product', 'Product', 'txn_product.name'),
                        Spec::col('type', 'Type', 'transaction_ledger.transaction_type'),
                        Spec::col('from_location', 'From', 'txn_site.name'),
                        Spec::col('to_location', 'To', 'to_sites.name'),
                        Spec::num('quantity', 'Qty', 'transaction_ledger.quantity'),
                        Spec::money('total_price', 'Value', 'transaction_ledger.total_price'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'transaction_ledger.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'transaction_ledger.created_at', '<='),
                        Spec::multiFilter('transaction_type', 'Type', 'transaction_ledger.transaction_type'),
                        Spec::textFilter('q', 'Search', ['transaction_ledger.transaction_number', 'txn_product.name']),
                    ],
                    'summary' => [
                        Spec::summary('transactions', 'Transactions', 'count(*)'),
                        Spec::summary('quantity', 'Units moved', 'sum(transaction_ledger.quantity)'),
                        Spec::summary('value', 'Value moved', 'sum(transaction_ledger.total_price)', 'money'),
                    ],
                    'order' => [Spec::order('created_at', 'desc')],
                ],

                'by-type' => [
                    'label' => 'Movements by Type',
                    'description' => 'Purchases, issues, transfers and adjustments, side by side.',
                    'base' => 'transaction_ledger',
                    'group_by' => ['transaction_ledger.transaction_type'],
                    'columns' => [
                        Spec::badge('type', 'Type', 'transaction_ledger.transaction_type', ['primary' => true]),
                        Spec::num('transactions', 'Transactions', 'count(*)'),
                        Spec::num('quantity', 'Units', 'sum(transaction_ledger.quantity)'),
                        Spec::money('value', 'Value', 'sum(transaction_ledger.total_price)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'transaction_ledger.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'transaction_ledger.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('types', 'Types', 'count(distinct transaction_ledger.transaction_type)'),
                        Spec::summary('value', 'Value moved', 'sum(transaction_ledger.total_price)', 'money'),
                    ],
                    'order' => [Spec::order('quantity', 'desc')],
                    'chart' => Spec::chart('bar', 'type', 'quantity'),
                ],

                'by-product' => [
                    'label' => 'Movements by Product',
                    'description' => 'The busiest products in the warehouse.',
                    'base' => 'transaction_ledger',
                    'joins' => [$product],
                    'group_by' => ['txn_product.id', 'txn_product.name'],
                    'columns' => [
                        Spec::col('product', 'Product', 'txn_product.name', ['primary' => true]),
                        Spec::num('transactions', 'Transactions', 'count(*)'),
                        Spec::num('quantity', 'Units', 'sum(transaction_ledger.quantity)'),
                        Spec::money('value', 'Value', 'sum(transaction_ledger.total_price)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'transaction_ledger.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'transaction_ledger.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('products', 'Products', 'count(distinct transaction_ledger.product_id)'),
                        Spec::summary('value', 'Value moved', 'sum(transaction_ledger.total_price)', 'money'),
                    ],
                    'order' => [Spec::order('quantity', 'desc')],
                    'chart' => Spec::chart('bar', 'product', 'quantity'),
                ],

                'by-location' => [
                    'label' => 'Movements by Location',
                    'description' => 'Where the warehouse activity is happening.',
                    'base' => 'transaction_ledger',
                    'joins' => [$site],
                    'group_by' => ['txn_site.id', 'txn_site.name'],
                    'columns' => [
                        Spec::col('site', 'Location', 'txn_site.name', ['primary' => true]),
                        Spec::num('transactions', 'Transactions', 'count(*)'),
                        Spec::num('quantity', 'Units', 'sum(transaction_ledger.quantity)'),
                        Spec::money('value', 'Value', 'sum(transaction_ledger.total_price)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'transaction_ledger.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'transaction_ledger.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('locations', 'Locations', 'count(distinct txn_site.id)'),
                        Spec::summary('value', 'Value moved', 'sum(transaction_ledger.total_price)', 'money'),
                    ],
                    'order' => [Spec::order('quantity', 'desc')],
                    'chart' => Spec::chart('bar', 'site', 'quantity'),
                ],

                'stock-movement-trend' => [
                    'label' => 'Stock Movement Trend',
                    'description' => 'How much stock has moved, month by month.',
                    'base' => 'transaction_ledger',
                    'group_by' => ['year(transaction_ledger.created_at)', 'month(transaction_ledger.created_at)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(transaction_ledger.created_at), '/', year(transaction_ledger.created_at))", ['primary' => true]),
                        Spec::num('transactions', 'Transactions', 'count(*)'),
                        Spec::num('quantity', 'Units moved', 'sum(transaction_ledger.quantity)'),
                        Spec::money('value', 'Value moved', 'sum(transaction_ledger.total_price)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'transaction_ledger.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'transaction_ledger.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('quantity', 'Units moved', 'sum(transaction_ledger.quantity)'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'quantity'),
                ],

                'valuation-movements' => [
                    'label' => 'Movement Valuation',
                    'description' => 'The money value of everything that moved.',
                    'base' => 'transaction_ledger',
                    'joins' => [$product],
                    'group_by' => ['year(transaction_ledger.created_at)', 'month(transaction_ledger.created_at)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(transaction_ledger.created_at), '/', year(transaction_ledger.created_at))", ['primary' => true]),
                        Spec::money('purchase_value', 'Purchases', "sum(case when transaction_ledger.transaction_type = 'purchase' then transaction_ledger.total_price else 0 end)"),
                        Spec::money('issue_value', 'Issues', "sum(case when transaction_ledger.transaction_type = 'issue' then transaction_ledger.total_price else 0 end)"),
                        Spec::money('sales_value', 'Sales', "sum(case when transaction_ledger.transaction_type = 'sales' then transaction_ledger.total_price else 0 end)"),
                        Spec::money('total_value', 'Total', 'sum(transaction_ledger.total_price)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'transaction_ledger.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'transaction_ledger.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('total_value', 'Total value moved', 'sum(transaction_ledger.total_price)', 'money'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('bar', 'period', 'total_value'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function stockRequests(): array
    {
        $product = Spec::join('products', 'stock_requests.product_id', 'products.id', 'left', 'request_product');
        $fromSite = Spec::join('sites', 'stock_requests.from_location_id', 'from_sites.id', 'left', 'from_sites');
        $toSite = Spec::join('sites', 'stock_requests.to_location_id', 'to_sites.id', 'left', 'to_sites');

        return [
            'label' => 'Stock Requests',
            'category' => 'Inventory',
            'permission' => 'stock-requests.view',
            'reports' => [

                'register' => [
                    'label' => 'Stock Request Register',
                    'description' => 'Every request raised for stock movement.',
                    'base' => 'stock_requests',
                    'joins' => [$product, $fromSite, $toSite],
                    'columns' => [
                        Spec::col('request_number', 'Request', 'stock_requests.request_number', ['primary' => true]),
                        Spec::datetime('created_at', 'Raised', 'stock_requests.created_at'),
                        Spec::col('product', 'Product', 'request_product.name'),
                        Spec::col('from_location', 'From', 'from_sites.name'),
                        Spec::col('to_location', 'To', 'to_sites.name'),
                        Spec::num('quantity', 'Requested', 'stock_requests.quantity'),
                        Spec::num('approved', 'Approved', 'stock_requests.approved_quantity'),
                        Spec::num('issued', 'Issued', 'stock_requests.issued_quantity'),
                        Spec::num('received', 'Received', 'stock_requests.received_quantity'),
                        Spec::badge('status', 'Status', 'stock_requests.status'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'stock_requests.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'stock_requests.created_at', '<='),
                        Spec::multiFilter('status', 'Status', 'stock_requests.status'),
                        Spec::textFilter('q', 'Search', ['stock_requests.request_number', 'request_product.name']),
                    ],
                    'summary' => [
                        Spec::summary('requests', 'Requests', 'count(*)'),
                        Spec::summary('requested', 'Units requested', 'sum(stock_requests.quantity)'),
                        Spec::summary('issued', 'Units issued', 'sum(stock_requests.issued_quantity)'),
                    ],
                    'order' => [Spec::order('created_at', 'desc')],
                ],

                'by-status' => [
                    'label' => 'Requests by Status',
                    'description' => 'Where the request queue is sitting.',
                    'base' => 'stock_requests',
                    'group_by' => ['stock_requests.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'stock_requests.status', ['primary' => true]),
                        Spec::num('requests', 'Requests', 'count(*)'),
                        Spec::num('quantity', 'Units requested', 'sum(stock_requests.quantity)'),
                        Spec::num('approved', 'Units approved', 'sum(stock_requests.approved_quantity)'),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Requests', 'count(*)'),
                    ],
                    'order' => [Spec::order('requests', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'requests'),
                ],

                'by-product' => [
                    'label' => 'Requests by Product',
                    'description' => 'Which products are being asked for most.',
                    'base' => 'stock_requests',
                    'joins' => [$product],
                    'group_by' => ['request_product.id', 'request_product.name'],
                    'columns' => [
                        Spec::col('product', 'Product', 'request_product.name', ['primary' => true]),
                        Spec::num('requests', 'Requests', 'count(*)'),
                        Spec::num('quantity', 'Units requested', 'sum(stock_requests.quantity)'),
                        Spec::num('approved', 'Units approved', 'sum(stock_requests.approved_quantity)'),
                        Spec::num('issued', 'Units issued', 'sum(stock_requests.issued_quantity)'),
                    ],
                    'summary' => [
                        Spec::summary('products', 'Products', 'count(distinct stock_requests.product_id)'),
                        Spec::summary('quantity', 'Units requested', 'sum(stock_requests.quantity)'),
                    ],
                    'order' => [Spec::order('quantity', 'desc')],
                    'chart' => Spec::chart('bar', 'product', 'quantity'),
                ],

                'by-destination' => [
                    'label' => 'Requests by Destination',
                    'description' => 'Which site is drawing the most stock.',
                    'base' => 'stock_requests',
                    'joins' => [$toSite],
                    'group_by' => ['to_sites.id', 'to_sites.name'],
                    'columns' => [
                        Spec::col('site', 'Destination', 'to_sites.name', ['primary' => true]),
                        Spec::num('requests', 'Requests', 'count(*)'),
                        Spec::num('quantity', 'Units requested', 'sum(stock_requests.quantity)'),
                        Spec::num('received', 'Units received', 'sum(stock_requests.received_quantity)'),
                    ],
                    'summary' => [
                        Spec::summary('sites', 'Sites', 'count(distinct to_sites.id)'),
                        Spec::summary('quantity', 'Units requested', 'sum(stock_requests.quantity)'),
                    ],
                    'order' => [Spec::order('quantity', 'desc')],
                    'chart' => Spec::chart('bar', 'site', 'quantity'),
                ],

                'fulfilment-rate' => [
                    'label' => 'Request Fulfilment Rate',
                    'description' => 'How much of what was asked for actually went out.',
                    'base' => 'stock_requests',
                    'group_by' => ['stock_requests.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'stock_requests.status', ['primary' => true]),
                        Spec::num('requests', 'Requests', 'count(*)'),
                        Spec::num('requested', 'Requested', 'sum(stock_requests.quantity)'),
                        Spec::num('issued', 'Issued', 'sum(stock_requests.issued_quantity)'),
                        Spec::percent('fulfilment', 'Fulfilled %', 'case when sum(stock_requests.quantity) > 0 then round(sum(stock_requests.issued_quantity) * 100 / sum(stock_requests.quantity), 2) else 0 end'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'stock_requests.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'stock_requests.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('requested', 'Units requested', 'sum(stock_requests.quantity)'),
                        Spec::summary('issued', 'Units issued', 'sum(stock_requests.issued_quantity)'),
                    ],
                    'order' => [Spec::order('requested', 'desc')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function stockTransfers(): array
    {
        $product = Spec::join('products', 'inventory_transfers.product_id', 'products.id', 'left', 'transfer_product');

        return [
            'label' => 'Stock Transfers',
            'category' => 'Inventory',
            'permission' => 'stock-transfers.view',
            'reports' => [

                'register' => [
                    'label' => 'Transfer Register',
                    'description' => 'Every stock transfer raised.',
                    'base' => 'inventory_transfers',
                    'joins' => [$product],
                    'columns' => [
                        Spec::col('product', 'Product', 'transfer_product.name', ['primary' => true]),
                        Spec::num('quantity', 'Quantity', 'inventory_transfers.quantity'),
                        Spec::badge('status', 'Status', 'inventory_transfers.status'),
                        Spec::datetime('created_at', 'Raised', 'inventory_transfers.created_at'),
                        Spec::col('notes', 'Notes', 'inventory_transfers.notes'),
                    ],
                    'filters' => [
                        Spec::multiFilter('status', 'Status', 'inventory_transfers.status'),
                        Spec::textFilter('q', 'Search', ['transfer_product.name', 'inventory_transfers.notes']),
                    ],
                    'summary' => [
                        Spec::summary('transfers', 'Transfers', 'count(*)'),
                        Spec::summary('quantity', 'Units moved', 'sum(inventory_transfers.quantity)'),
                    ],
                    'order' => [Spec::order('created_at', 'desc')],
                ],

                'by-status' => [
                    'label' => 'Transfers by Status',
                    'description' => 'Which transfers are still open.',
                    'base' => 'inventory_transfers',
                    'group_by' => ['inventory_transfers.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'inventory_transfers.status', ['primary' => true]),
                        Spec::num('transfers', 'Transfers', 'count(*)'),
                        Spec::num('quantity', 'Units', 'sum(inventory_transfers.quantity)'),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Transfers', 'count(*)'),
                    ],
                    'order' => [Spec::order('transfers', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'transfers'),
                ],

                'by-product' => [
                    'label' => 'Transfers by Product',
                    'description' => 'Which products move most between locations.',
                    'base' => 'inventory_transfers',
                    'joins' => [$product],
                    'group_by' => ['transfer_product.id', 'transfer_product.name'],
                    'columns' => [
                        Spec::col('product', 'Product', 'transfer_product.name', ['primary' => true]),
                        Spec::num('transfers', 'Transfers', 'count(*)'),
                        Spec::num('quantity', 'Units moved', 'sum(inventory_transfers.quantity)'),
                    ],
                    'summary' => [
                        Spec::summary('products', 'Products', 'count(distinct inventory_transfers.product_id)'),
                        Spec::summary('quantity', 'Units moved', 'sum(inventory_transfers.quantity)'),
                    ],
                    'order' => [Spec::order('quantity', 'desc')],
                    'chart' => Spec::chart('bar', 'product', 'quantity'),
                ],

                'approval-queue' => [
                    'label' => 'Transfers Awaiting Approval',
                    'description' => 'The transfers that have not been cleared yet.',
                    'base' => 'inventory_transfers',
                    'joins' => [$product],
                    'constraints' => [
                        ['column' => 'inventory_transfers.status', 'operator' => '=', 'value' => 'pending'],
                    ],
                    'columns' => [
                        Spec::col('product', 'Product', 'transfer_product.name', ['primary' => true]),
                        Spec::num('quantity', 'Quantity', 'inventory_transfers.quantity'),
                        Spec::datetime('created_at', 'Raised', 'inventory_transfers.created_at'),
                        Spec::col('notes', 'Notes', 'inventory_transfers.notes'),
                    ],
                    'summary' => [
                        Spec::summary('pending', 'Pending transfers', 'count(*)'),
                        Spec::summary('quantity', 'Units', 'sum(inventory_transfers.quantity)'),
                    ],
                    'order' => [Spec::order('created_at', 'desc')],
                ],
            ],
        ];
    }
}

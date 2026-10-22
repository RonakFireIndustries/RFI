<?php

namespace App\Support\Reports\Modules;

use App\Support\Reports\Spec;

/**
 * Sales and survey reports.
 *
 * The front end of the business: the buildings that were surveyed, the
 * visits that paid for them, the opportunities that came out of those visits,
 * the quotations that followed, and the fire systems that were found on the
 * way through.
 */
class SalesSurvey
{
    public static function modules(): array
    {
        return [
            'buildings' => self::buildings(),
            'extinguishers' => self::extinguishers(),
            'quotations' => self::quotations(),
            'site-visits' => self::siteVisits(),
            'opportunities' => self::opportunities(),
            'follow-ups' => self::followUps(),
            'sales-documents' => self::salesDocuments(),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Buildings                                                           */
    /* ------------------------------------------------------------------ */

    private static function buildings(): array
    {
        return [
            'label' => 'Buildings',
            'category' => 'Sales & Survey',
            'permission' => 'buildings.view',
            'reports' => [

                'master' => [
                    'label' => 'Building Master',
                    'description' => 'Every building that has been surveyed.',
                    'base' => 'buildings',
                    'columns' => [
                        Spec::col('name', 'Building', 'buildings.name', ['primary' => true]),
                        Spec::col('society', 'Society', 'buildings.society_name'),
                        Spec::col('building_type', 'Type', 'buildings.building_type'),
                        Spec::col('city', 'City', 'buildings.city'),
                        Spec::col('state', 'State', 'buildings.state'),
                        Spec::badge('status', 'Status', 'buildings.status'),
                        Spec::date('added_on', 'Added on', 'buildings.created_at'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('status', 'Status', 'buildings', 'status'),
                        Spec::sourceFilter('building_type', 'Type', 'buildings', 'building_type'),
                        Spec::textFilter('q', 'Search', ['buildings.name', 'buildings.society_name', 'buildings.city']),
                    ],
                    'summary' => [Spec::summary('total', 'Buildings', 'count(*)')],
                    'order' => [Spec::order('name')],
                ],

                'by-type' => [
                    'label' => 'Buildings by Type',
                    'description' => 'What kind of buildings the survey covers.',
                    'base' => 'buildings',
                    'group_by' => ['buildings.building_type'],
                    'columns' => [
                        Spec::col('building_type', 'Type', 'buildings.building_type', ['primary' => true]),
                        Spec::num('buildings', 'Buildings', 'count(*)'),
                        Spec::num('flats', 'Flats', 'sum(buildings.no_of_flats)'),
                    ],
                    'summary' => [
                        Spec::summary('types', 'Types', 'count(distinct buildings.building_type)'),
                        Spec::summary('buildings', 'Buildings', 'count(*)'),
                    ],
                    'order' => [Spec::order('buildings', 'desc')],
                    'chart' => Spec::chart('pie', 'building_type', 'buildings'),
                ],

                'by-status' => [
                    'label' => 'Buildings by Status',
                    'description' => 'Which buildings are live, and which are not.',
                    'base' => 'buildings',
                    'group_by' => ['buildings.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'buildings.status', ['primary' => true]),
                        Spec::num('buildings', 'Buildings', 'count(*)'),
                        Spec::num('flats', 'Flats', 'sum(buildings.no_of_flats)'),
                    ],
                    'summary' => [Spec::summary('buildings', 'Buildings', 'count(*)')],
                    'order' => [Spec::order('buildings', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'buildings'),
                ],

                'building-profile' => [
                    'label' => 'Building Structure Profile',
                    'description' => 'The physical shape of each building - floors, flats, lifts.',
                    'base' => 'buildings',
                    'columns' => [
                        Spec::col('name', 'Building', 'buildings.name', ['primary' => true]),
                        Spec::num('floors', 'Floors', 'buildings.no_of_floors'),
                        Spec::num('basements', 'Basements', 'buildings.basement_levels'),
                        Spec::num('wings', 'Wings', 'buildings.no_of_wings'),
                        Spec::num('flats', 'Flats', 'buildings.no_of_flats'),
                        Spec::num('lifts', 'Lifts', 'buildings.no_of_lifts'),
                        Spec::num('staircases', 'Staircases', 'buildings.no_of_staircase'),
                        Spec::num('exits', 'Exits', 'buildings.no_of_exits_entry'),
                        Spec::num('refuge_floors', 'Refuge floors', 'buildings.refuge_floors'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['buildings.name']),
                    ],
                    'summary' => [
                        Spec::summary('buildings', 'Buildings', 'count(*)'),
                        Spec::summary('flats', 'Total flats', 'sum(buildings.no_of_flats)'),
                        Spec::summary('floors', 'Total floors', 'sum(buildings.no_of_floors)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'fire-noc-status' => [
                    'label' => 'Fire NOC & Safety Status',
                    'description' => 'The compliance picture across every surveyed building.',
                    'base' => 'buildings',
                    'columns' => [
                        Spec::col('name', 'Building', 'buildings.name', ['primary' => true]),
                        Spec::col('fire_noc_status', 'Fire NOC', 'buildings.fire_noc_status'),
                        Spec::col('fire_safety_type', 'Safety type', 'buildings.fire_safety_type'),
                        Spec::col('equipment_condition', 'Equipment condition', 'buildings.overall_equipment_condition'),
                        Spec::date('last_serviced', 'Last serviced', 'buildings.last_serviced_date'),
                        Spec::col('last_amc_company', 'AMC company', 'buildings.last_amc_company'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('fire_noc_status', 'Fire NOC', 'buildings', 'fire_noc_status'),
                    ],
                    'summary' => [
                        Spec::summary('buildings', 'Buildings', 'count(*)'),
                        Spec::summary('serviced', 'With a service date', 'sum(case when buildings.last_serviced_date is not null then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'by-city' => [
                    'label' => 'Buildings by City',
                    'description' => 'Where the survey work is concentrated.',
                    'base' => 'buildings',
                    'group_by' => ['buildings.city'],
                    'columns' => [
                        Spec::col('city', 'City', 'buildings.city', ['primary' => true]),
                        Spec::num('buildings', 'Buildings', 'count(*)'),
                        Spec::num('flats', 'Flats', 'sum(buildings.no_of_flats)'),
                    ],
                    'summary' => [
                        Spec::summary('cities', 'Cities', 'count(distinct buildings.city)'),
                        Spec::summary('buildings', 'Buildings', 'count(*)'),
                    ],
                    'order' => [Spec::order('buildings', 'desc')],
                    'chart' => Spec::chart('bar', 'city', 'buildings'),
                ],

                'wings-and-flats' => [
                    'label' => 'Wings and Flats',
                    'description' => 'The wing-by-wing breakdown of every building.',
                    'base' => 'building_wings',
                    'joins' => [Spec::join('buildings', 'building_wings.building_id', 'buildings.id', 'left', 'building')],
                    'columns' => [
                        Spec::col('building', 'Building', 'building.name', ['primary' => true]),
                        Spec::col('wing', 'Wing', 'building_wings.name'),
                        Spec::num('floors', 'Floors', 'building_wings.floors'),
                        Spec::num('flats_per_floor', 'Flats / floor', 'building_wings.flats_per_floor'),
                        Spec::num('total_flats', 'Total flats', 'building_wings.total_flats'),
                        Spec::col('configuration', 'Configuration', 'building_wings.flat_configuration'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['building.name', 'building_wings.name']),
                    ],
                    'summary' => [
                        Spec::summary('wings', 'Wings', 'count(*)'),
                        Spec::summary('flats', 'Total flats', 'sum(building_wings.total_flats)'),
                    ],
                    'order' => [Spec::order('building')],
                ],

                'flat-register' => [
                    'label' => 'Flat Register',
                    'description' => 'Every flat recorded during the survey.',
                    'base' => 'building_flats',
                    'joins' => [
                        Spec::join('buildings', 'building_flats.building_id', 'buildings.id', 'left', 'building'),
                        Spec::join('building_wings', 'building_flats.wing_id', 'building_wings.id', 'left', 'wing'),
                        Spec::join('building_floors', 'building_flats.floor_id', 'building_floors.id', 'left', 'floor'),
                    ],
                    'columns' => [
                        Spec::col('building', 'Building', 'building.name', ['primary' => true]),
                        Spec::col('wing', 'Wing', 'wing.name'),
                        Spec::col('floor', 'Floor', 'floor.name'),
                        Spec::col('flat_number', 'Flat', 'building_flats.flat_number'),
                        Spec::col('name', 'Name', 'building_flats.name'),
                        Spec::col('bhk_type', 'BHK', 'building_flats.bhk_type'),
                        Spec::num('area', 'Area', 'building_flats.area'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['building.name', 'building_flats.flat_number', 'building_flats.name']),
                    ],
                    'summary' => [
                        Spec::summary('flats', 'Flats', 'count(*)'),
                        Spec::summary('area', 'Total area', 'sum(building_flats.area)'),
                    ],
                    'order' => [Spec::order('building')],
                ],

                'amc-contracts' => [
                    'label' => 'AMC Contracts',
                    'description' => 'The annual maintenance contracts signed against buildings.',
                    'base' => 'building_amcs',
                    'joins' => [Spec::join('buildings', 'building_amcs.building_id', 'buildings.id', 'left', 'building')],
                    'columns' => [
                        Spec::col('building', 'Building', 'building.name', ['primary' => true]),
                        Spec::col('vendor', 'Vendor', 'building_amcs.vendor_name', ['primary' => true]),
                        Spec::col('contract_number', 'Contract', 'building_amcs.contract_number'),
                        Spec::col('contract_type', 'Type', 'building_amcs.contract_type'),
                        Spec::date('start_date', 'Start', 'building_amcs.start_date'),
                        Spec::date('end_date', 'End', 'building_amcs.end_date'),
                        Spec::money('amount', 'Value', 'building_amcs.amount'),
                        Spec::badge('status', 'Status', 'building_amcs.status'),
                    ],
                    'filters' => [
                        Spec::sourceMultiFilter('status', 'Status', 'building_amcs', 'status', 'building_amcs.status'),
                        Spec::textFilter('q', 'Search', ['building.name', 'building_amcs.vendor_name', 'building_amcs.contract_number']),
                    ],
                    'summary' => [
                        Spec::summary('contracts', 'Contracts', 'count(*)'),
                        Spec::summary('value', 'Contract value', 'sum(building_amcs.amount)', 'money'),
                    ],
                    'order' => [Spec::order('start_date', 'desc')],
                ],

                'amc-service-due' => [
                    'label' => 'AMC Service Due',
                    'description' => 'Which contracts are due for their next service.',
                    'base' => 'building_amcs',
                    'joins' => [Spec::join('buildings', 'building_amcs.building_id', 'buildings.id', 'left', 'building')],
                    'columns' => [
                        Spec::col('building', 'Building', 'building.name', ['primary' => true]),
                        Spec::col('vendor', 'Vendor', 'building_amcs.vendor_name'),
                        Spec::date('last_service', 'Last service', 'building_amcs.last_service_date'),
                        Spec::date('next_service', 'Next service', 'building_amcs.next_service_date'),
                        Spec::col('frequency', 'Frequency', 'building_amcs.frequency'),
                        Spec::badge('status', 'Status', 'building_amcs.status'),
                    ],
                    'filters' => [
                        Spec::dateFilter('due_from', 'Due from', 'building_amcs.next_service_date', '>='),
                        Spec::dateFilter('due_to', 'Due to', 'building_amcs.next_service_date', '<='),
                    ],
                    'summary' => [Spec::summary('contracts', 'Contracts', 'count(*)')],
                    'order' => [Spec::order('next_service')],
                ],

                'contacts' => [
                    'label' => 'Building Contacts',
                    'description' => 'Who to call at each surveyed building.',
                    'base' => 'building_contacts',
                    'joins' => [Spec::join('buildings', 'building_contacts.building_id', 'buildings.id', 'left', 'building')],
                    'columns' => [
                        Spec::col('building', 'Building', 'building.name', ['primary' => true]),
                        Spec::col('full_name', 'Contact', 'building_contacts.full_name'),
                        Spec::col('role', 'Role', 'building_contacts.role'),
                        Spec::col('role_category', 'Category', 'building_contacts.role_category'),
                        Spec::col('mobile', 'Mobile', 'building_contacts.mobile_number'),
                        Spec::col('whatsapp', 'WhatsApp', 'building_contacts.whatsapp_number'),
                        Spec::col('email', 'Email', 'building_contacts.email'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['building.name', 'building_contacts.full_name', 'building_contacts.mobile_number']),
                    ],
                    'summary' => [Spec::summary('contacts', 'Contacts', 'count(*)')],
                    'order' => [Spec::order('building')],
                ],

                'converted' => [
                    'label' => 'Converted Buildings',
                    'description' => 'The surveys that became live sites.',
                    'base' => 'buildings',
                    'columns' => [
                        Spec::col('name', 'Building', 'buildings.name', ['primary' => true]),
                        Spec::col('converted_site_name', 'Site', 'buildings.converted_site_name'),
                        Spec::datetime('converted_at', 'Converted on', 'buildings.converted_at'),
                        Spec::col('city', 'City', 'buildings.city'),
                        Spec::badge('status', 'Status', 'buildings.status'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Converted from', 'buildings.converted_at', '>='),
                        Spec::dateFilter('to', 'Converted to', 'buildings.converted_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('converted', 'Converted', 'sum(case when buildings.converted_at is not null then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('converted_at', 'desc')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Fire extinguishers                                                   */
    /* ------------------------------------------------------------------ */

    private static function extinguishers(): array
    {
        return [
            'label' => 'Fire Extinguishers',
            'category' => 'Sales & Survey',
            'permission' => 'extinguishers.view',
            'reports' => [

                'master' => [
                    'label' => 'Fire System Register',
                    'description' => 'Every fire system found during the survey.',
                    'base' => 'fire_systems',
                    'joins' => [Spec::join('buildings', 'fire_systems.building_id', 'buildings.id', 'left', 'building')],
                    'columns' => [
                        Spec::col('label', 'System', 'fire_systems.label', ['primary' => true]),
                        Spec::col('building', 'Building', 'building.name'),
                        Spec::col('system_type', 'System type', 'fire_systems.system_type'),
                        Spec::col('sub_type', 'Sub type', 'fire_systems.sub_type'),
                        Spec::badge('status', 'Status', 'fire_systems.status'),
                        Spec::num('quantity', 'Quantity', 'fire_systems.quantity'),
                        Spec::col('capacity', 'Capacity', 'fire_systems.capacity'),
                        Spec::col('brand', 'Brand', 'fire_systems.brand'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('status', 'Status', 'fire_systems', 'status'),
                        Spec::sourceFilter('system_type', 'System type', 'fire_systems', 'system_type'),
                        Spec::textFilter('q', 'Search', ['fire_systems.label', 'buildings.name', 'fire_systems.brand']),
                    ],
                    'summary' => [
                        Spec::summary('systems', 'Systems', 'count(*)'),
                        Spec::summary('units', 'Total units', 'sum(fire_systems.quantity)'),
                    ],
                    'order' => [Spec::order('label')],
                ],

                'by-status' => [
                    'label' => 'Fire Systems by Status',
                    'description' => 'What condition the fleet is in.',
                    'base' => 'fire_systems',
                    'group_by' => ['fire_systems.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'fire_systems.status', ['primary' => true]),
                        Spec::num('systems', 'Systems', 'count(*)'),
                        Spec::num('units', 'Units', 'sum(fire_systems.quantity)'),
                    ],
                    'summary' => [Spec::summary('systems', 'Systems', 'count(*)')],
                    'order' => [Spec::order('systems', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'systems'),
                ],

                'by-building' => [
                    'label' => 'Fire Systems by Building',
                    'description' => 'How much fire cover each building carries.',
                    'base' => 'fire_systems',
                    'joins' => [Spec::join('buildings', 'fire_systems.building_id', 'buildings.id', 'left', 'building')],
                    'group_by' => ['building.id', 'building.name'],
                    'columns' => [
                        Spec::col('building', 'Building', 'building.name', ['primary' => true]),
                        Spec::num('systems', 'Systems', 'count(*)'),
                        Spec::num('units', 'Units', 'sum(fire_systems.quantity)'),
                    ],
                    'summary' => [
                        Spec::summary('buildings', 'Buildings', 'count(distinct building.id)'),
                        Spec::summary('units', 'Total units', 'sum(fire_systems.quantity)'),
                    ],
                    'order' => [Spec::order('units', 'desc')],
                    'chart' => Spec::chart('bar', 'building', 'units'),
                ],

                'by-type' => [
                    'label' => 'Fire Systems by Type',
                    'description' => 'The mix of systems across the fleet.',
                    'base' => 'fire_systems',
                    'group_by' => ['fire_systems.system_type'],
                    'columns' => [
                        Spec::col('system_type', 'System type', 'fire_systems.system_type', ['primary' => true]),
                        Spec::num('systems', 'Systems', 'count(*)'),
                        Spec::num('units', 'Units', 'sum(fire_systems.quantity)'),
                    ],
                    'summary' => [Spec::summary('systems', 'Systems', 'count(*)')],
                    'order' => [Spec::order('units', 'desc')],
                    'chart' => Spec::chart('bar', 'system_type', 'units'),
                ],

                'refills-due' => [
                    'label' => 'Refills Due',
                    'description' => 'The systems that are due for a refill.',
                    'base' => 'fire_systems',
                    'joins' => [Spec::join('buildings', 'fire_systems.building_id', 'buildings.id', 'left', 'building')],
                    'columns' => [
                        Spec::col('label', 'System', 'fire_systems.label', ['primary' => true]),
                        Spec::col('building', 'Building', 'building.name'),
                        Spec::badge('status', 'Status', 'fire_systems.status'),
                        Spec::date('next_refill', 'Next refill', 'fire_systems.next_refill_date'),
                        Spec::date('last_testing', 'Last testing', 'fire_systems.last_testing_date'),
                        Spec::num('quantity', 'Quantity', 'fire_systems.quantity'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Due from', 'fire_systems.next_refill_date', '>='),
                        Spec::dateFilter('to', 'Due to', 'fire_systems.next_refill_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('systems', 'Systems', 'count(*)'),
                        Spec::summary('units', 'Units', 'sum(fire_systems.quantity)'),
                    ],
                    'order' => [Spec::order('next_refill')],
                ],

                'movement-log' => [
                    'label' => 'Extinguisher Movement Log',
                    'description' => 'Every refill, return and decommission.',
                    'base' => 'extinguisher_movements',
                    'joins' => [
                        Spec::join('fire_systems', 'extinguisher_movements.fire_system_id', 'fire_systems.id', 'left', 'system'),
                        Spec::join('buildings', 'fire_systems.building_id', 'buildings.id', 'left', 'building'),
                        Spec::join('users', 'extinguisher_movements.user_id', 'users.id', 'left', 'user'),
                    ],
                    'columns' => [
                        Spec::datetime('moved_at', 'Moved on', 'extinguisher_movements.created_at', ['primary' => true]),
                        Spec::col('system', 'System', 'system.label'),
                        Spec::col('building', 'Building', 'building.name'),
                        Spec::col('action', 'Action', 'extinguisher_movements.action'),
                        Spec::date('next_refill', 'Next refill', 'extinguisher_movements.next_refill_date'),
                        Spec::col('by', 'By', 'user.name'),
                        Spec::col('remark', 'Remark', 'extinguisher_movements.remark'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'extinguisher_movements.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'extinguisher_movements.created_at', '<='),
                        Spec::sourceFilter('action', 'Action', 'extinguisher_movements', 'action'),
                        Spec::textFilter('q', 'Search', ['system.label', 'buildings.name']),
                    ],
                    'summary' => [Spec::summary('movements', 'Movements', 'count(*)')],
                    'order' => [Spec::order('moved_at', 'desc')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Quotations / BOQ                                                    */
    /* ------------------------------------------------------------------ */

    private static function quotations(): array
    {
        return [
            'label' => 'Quotations / BOQ',
            'category' => 'Sales & Survey',
            'permission' => 'quotations.view',
            'reports' => [

                'master' => [
                    'label' => 'Quotation Register',
                    'description' => 'Every BOQ that has been raised.',
                    'base' => 'quotations',
                    'joins' => [Spec::join('buildings', 'quotations.building_id', 'buildings.id', 'left', 'building')],
                    'columns' => [
                        Spec::col('quotation_no', 'Quotation', 'quotations.quotation_no', ['primary' => true]),
                        Spec::date('quotation_date', 'Dated', 'quotations.quotation_date'),
                        Spec::col('building', 'Building', 'coalesce(quotations.building_name, building.name)'),
                        Spec::badge('status', 'Status', 'quotations.status'),
                        Spec::percent('discount', 'Discount %', 'quotations.discount'),
                        Spec::percent('gst_percent', 'GST %', 'quotations.gst_percent'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'quotations.quotation_date', '>='),
                        Spec::dateFilter('to', 'To', 'quotations.quotation_date', '<='),
                        Spec::sourceMultiFilter('status', 'Status', 'quotations', 'status', 'quotations.status'),
                        Spec::textFilter('q', 'Search', ['quotations.quotation_no', 'quotations.building_name', 'buildings.name']),
                    ],
                    'summary' => [Spec::summary('quotations', 'Quotations', 'count(*)')],
                    'order' => [Spec::order('quotation_date', 'desc')],
                ],

                'by-status' => [
                    'label' => 'Quotations by Status',
                    'description' => 'How the BOQs are progressing.',
                    'base' => 'quotations',
                    'group_by' => ['quotations.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'quotations.status', ['primary' => true]),
                        Spec::num('quotations', 'Quotations', 'count(*)'),
                    ],
                    'summary' => [Spec::summary('quotations', 'Quotations', 'count(*)')],
                    'order' => [Spec::order('quotations', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'quotations'),
                ],

                'by-building' => [
                    'label' => 'Quotations by Building',
                    'description' => 'Which buildings have been quoted.',
                    'base' => 'quotations',
                    'joins' => [Spec::join('buildings', 'quotations.building_id', 'buildings.id', 'left', 'building')],
                    'group_by' => ['building.id', 'building.name'],
                    'columns' => [
                        Spec::col('building', 'Building', 'building.name', ['primary' => true]),
                        Spec::num('quotations', 'Quotations', 'count(*)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'quotations.quotation_date', '>='),
                        Spec::dateFilter('to', 'To', 'quotations.quotation_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('buildings', 'Buildings', 'count(distinct building.id)'),
                        Spec::summary('quotations', 'Quotations', 'count(*)'),
                    ],
                    'order' => [Spec::order('quotations', 'desc')],
                    'chart' => Spec::chart('bar', 'building', 'quotations'),
                ],

                'section-wise' => [
                    'label' => 'BOQ by Section',
                    'description' => 'The section headings a BOQ is built from.',
                    'base' => 'quotation_sections',
                    'joins' => [Spec::join('quotations', 'quotation_sections.quotation_id', 'quotations.id', 'left', 'quotation')],
                    'columns' => [
                        Spec::col('quotation_no', 'Quotation', 'quotation.quotation_no', ['primary' => true]),
                        Spec::col('section', 'Section', 'quotation_sections.name'),
                        Spec::num('sort_order', 'Order', 'quotation_sections.sort_order'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['quotation.quotation_no', 'quotation_sections.name']),
                    ],
                    'summary' => [
                        Spec::summary('sections', 'Sections', 'count(*)'),
                        Spec::summary('quotations', 'Quotations', 'count(distinct quotation_sections.quotation_id)'),
                    ],
                    'order' => [Spec::order('quotation_no')],
                ],

                'items' => [
                    'label' => 'BOQ Items',
                    'description' => 'The lines inside every BOQ.',
                    'base' => 'quotation_items',
                    'joins' => [
                        Spec::join('quotations', 'quotation_items.quotation_id', 'quotations.id', 'left', 'quotation'),
                        Spec::join('quotation_sections', 'quotation_items.quotation_section_id', 'quotation_sections.id', 'left', 'section'),
                        Spec::join('products', 'quotation_items.product_id', 'products.id', 'left', 'product'),
                    ],
                    'columns' => [
                        Spec::col('quotation_no', 'Quotation', 'quotation.quotation_no', ['primary' => true]),
                        Spec::col('section', 'Section', 'section.name'),
                        Spec::col('product', 'Product', "coalesce(product.name, quotation_items.description)"),
                        Spec::col('unit', 'Unit', 'quotation_items.unit'),
                        Spec::num('qty', 'Qty', 'quotation_items.qty'),
                        Spec::money('rate', 'Rate', 'quotation_items.rate'),
                        Spec::money('amount', 'Amount', 'quotation_items.amount'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('product_id', 'Product', 'products', 'id', 'quotation_items.product_id'),
                        Spec::textFilter('q', 'Search', ['quotation.quotation_no', 'products.name', 'quotation_items.description']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Lines', 'count(*)'),
                        Spec::summary('qty', 'Total qty', 'sum(quotation_items.qty)'),
                        Spec::summary('amount', 'Total value', 'sum(quotation_items.amount)', 'money'),
                    ],
                    'order' => [Spec::order('quotation_no')],
                ],

                'monthly-trend' => [
                    'label' => 'Quotation Monthly Trend',
                    'description' => 'How the BOQ flow moved month by month.',
                    'base' => 'quotations',
                    'group_by' => ['year(quotations.quotation_date)', 'month(quotations.quotation_date)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(quotations.quotation_date), '/', year(quotations.quotation_date))", ['primary' => true]),
                        Spec::num('quotations', 'Quotations', 'count(*)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'quotations.quotation_date', '>='),
                        Spec::dateFilter('to', 'To', 'quotations.quotation_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('quotations', 'Quotations', 'count(*)'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'quotations'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Site visits                                                         */
    /* ------------------------------------------------------------------ */

    private static function siteVisits(): array
    {
        return [
            'label' => 'Site Visits',
            'category' => 'Sales & Survey',
            'permission' => 'site-visits.view',
            'reports' => [

                'master' => [
                    'label' => 'Site Visit Register',
                    'description' => 'Every survey visit that was made.',
                    'base' => 'site_visits',
                    'joins' => [
                        Spec::join('buildings', 'site_visits.building_id', 'buildings.id', 'left', 'building'),
                        Spec::join('users', 'site_visits.user_id', 'users.id', 'left', 'user'),
                    ],
                    'columns' => [
                        Spec::date('visit_date', 'Visit date', 'site_visits.visit_date', ['primary' => true]),
                        Spec::col('building', 'Building', 'building.name'),
                        Spec::col('purpose', 'Purpose', 'site_visits.purpose'),
                        Spec::col('by', 'Surveyed by', 'user.name'),
                        Spec::date('next_followup', 'Next follow-up', 'site_visits.next_followup_date'),
                        Spec::col('notes', 'Notes', 'site_visits.discussion_notes'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'site_visits.visit_date', '>='),
                        Spec::dateFilter('to', 'To', 'site_visits.visit_date', '<='),
                        Spec::sourceFilter('purpose', 'Purpose', 'site_visits', 'purpose', 'site_visits.purpose'),
                        Spec::textFilter('q', 'Search', ['buildings.name', 'user.name', 'site_visits.discussion_notes']),
                    ],
                    'summary' => [Spec::summary('visits', 'Visits', 'count(*)')],
                    'order' => [Spec::order('visit_date', 'desc')],
                ],

                'by-purpose' => [
                    'label' => 'Visits by Purpose',
                    'description' => 'Why the survey team went out.',
                    'base' => 'site_visits',
                    'group_by' => ['site_visits.purpose'],
                    'columns' => [
                        Spec::col('purpose', 'Purpose', 'site_visits.purpose', ['primary' => true]),
                        Spec::num('visits', 'Visits', 'count(*)'),
                        Spec::num('buildings', 'Buildings', 'count(distinct site_visits.building_id)'),
                    ],
                    'summary' => [Spec::summary('visits', 'Visits', 'count(*)')],
                    'order' => [Spec::order('visits', 'desc')],
                    'chart' => Spec::chart('pie', 'purpose', 'visits'),
                ],

                'by-user' => [
                    'label' => 'Visits by Surveyor',
                    'description' => 'How much work each person has done.',
                    'base' => 'site_visits',
                    'joins' => [Spec::join('users', 'site_visits.user_id', 'users.id', 'left', 'user')],
                    'group_by' => ['user.id', 'user.name'],
                    'columns' => [
                        Spec::col('surveyor', 'Surveyor', 'user.name', ['primary' => true]),
                        Spec::num('visits', 'Visits', 'count(*)'),
                        Spec::num('buildings', 'Buildings', 'count(distinct site_visits.building_id)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'site_visits.visit_date', '>='),
                        Spec::dateFilter('to', 'To', 'site_visits.visit_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('surveyors', 'Surveyors', 'count(distinct user.id)'),
                        Spec::summary('visits', 'Visits', 'count(*)'),
                    ],
                    'order' => [Spec::order('visits', 'desc')],
                    'chart' => Spec::chart('bar', 'surveyor', 'visits'),
                ],

                'monthly-trend' => [
                    'label' => 'Site Visit Monthly Trend',
                    'description' => 'How the survey effort moved month by month.',
                    'base' => 'site_visits',
                    'group_by' => ['year(site_visits.visit_date)', 'month(site_visits.visit_date)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(site_visits.visit_date), '/', year(site_visits.visit_date))", ['primary' => true]),
                        Spec::num('visits', 'Visits', 'count(*)'),
                        Spec::num('buildings', 'Buildings', 'count(distinct site_visits.building_id)'),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('visits', 'Visits', 'count(*)'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'visits'),
                ],

                'follow-up-due' => [
                    'label' => 'Visits Awaiting Follow-up',
                    'description' => 'Visits where a follow-up was promised but has not happened.',
                    'base' => 'site_visits',
                    'joins' => [
                        Spec::join('buildings', 'site_visits.building_id', 'buildings.id', 'left', 'building'),
                        Spec::join('users', 'site_visits.user_id', 'users.id', 'left', 'user'),
                    ],
                    'columns' => [
                        Spec::date('visit_date', 'Visit date', 'site_visits.visit_date', ['primary' => true]),
                        Spec::col('building', 'Building', 'building.name'),
                        Spec::col('by', 'Surveyed by', 'user.name'),
                        Spec::date('next_followup', 'Next follow-up', 'site_visits.next_followup_date'),
                    ],
                    'filters' => [
                        Spec::dateFilter('due_from', 'Due from', 'site_visits.next_followup_date', '>='),
                        Spec::dateFilter('due_to', 'Due to', 'site_visits.next_followup_date', '<='),
                    ],
                    'summary' => [Spec::summary('visits', 'Visits', 'count(*)')],
                    'order' => [Spec::order('next_followup')],
                ],

                'photo-capture' => [
                    'label' => 'Site Visit Photo Capture',
                    'description' => 'How much evidence each visit produced.',
                    'base' => 'site_visits',
                    'joins' => [
                        Spec::join('buildings', 'site_visits.building_id', 'buildings.id', 'left', 'building'),
                        Spec::join('site_visit_photos', 'site_visit_photos.site_visit_id', 'site_visits.id', 'left', 'photo'),
                        Spec::join('site_visit_voice_notes', 'site_visit_voice_notes.site_visit_id', 'site_visits.id', 'left', 'voice'),
                    ],
                    'group_by' => ['site_visits.id', 'site_visits.visit_date'],
                    'columns' => [
                        Spec::date('visit_date', 'Visit date', 'site_visits.visit_date', ['primary' => true]),
                        Spec::col('building', 'Building', 'building.name'),
                        Spec::num('photos', 'Photos', 'count(distinct photo.id)'),
                        Spec::num('voice_notes', 'Voice notes', 'count(distinct voice.id)'),
                    ],
                    'summary' => [
                        Spec::summary('visits', 'Visits', 'count(distinct site_visits.id)'),
                        Spec::summary('photos', 'Photos', 'count(distinct photo.id)'),
                        Spec::summary('voice_notes', 'Voice notes', 'count(distinct voice.id)'),
                    ],
                    'order' => [Spec::order('visit_date', 'desc')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Opportunities                                                       */
    /* ------------------------------------------------------------------ */

    private static function opportunities(): array
    {
        return [
            'label' => 'Opportunities',
            'category' => 'Sales & Survey',
            'permission' => 'opportunities.view',
            'reports' => [

                'master' => [
                    'label' => 'Opportunity Register',
                    'description' => 'Every opportunity the survey has produced.',
                    'base' => 'opportunities',
                    'joins' => [
                        Spec::join('buildings', 'opportunities.building_id', 'buildings.id', 'left', 'building'),
                        Spec::join('users', 'opportunities.user_id', 'users.id', 'left', 'user'),
                    ],
                    'columns' => [
                        Spec::col('name', 'Opportunity', 'opportunities.name', ['primary' => true]),
                        Spec::col('building', 'Building', 'building.name'),
                        Spec::col('stage', 'Stage', 'opportunities.stage'),
                        Spec::num('probability', 'Probability %', 'opportunities.probability'),
                        Spec::money('estimated_value', 'Estimated value', 'opportunities.estimated_value'),
                        Spec::date('expected_closing', 'Expected closing', 'opportunities.expected_closing_date'),
                        Spec::col('owner', 'Owner', 'user.name'),
                    ],
                    'filters' => [
                        Spec::sourceMultiFilter('stage', 'Stage', 'opportunities', 'stage', 'opportunities.stage'),
                        Spec::sourceFilter('status', 'Status', 'opportunities', 'status', 'opportunities.status'),
                        Spec::dateFilter('from', 'Closing from', 'opportunities.expected_closing_date', '>='),
                        Spec::dateFilter('to', 'Closing to', 'opportunities.expected_closing_date', '<='),
                        Spec::textFilter('q', 'Search', ['opportunities.name', 'buildings.name']),
                    ],
                    'summary' => [
                        Spec::summary('opportunities', 'Opportunities', 'count(*)'),
                        Spec::summary('value', 'Estimated value', 'sum(opportunities.estimated_value)', 'money'),
                    ],
                    'order' => [Spec::order('expected_closing', 'desc')],
                ],

                'pipeline' => [
                    'label' => 'Sales Pipeline by Stage',
                    'description' => 'The value sitting at each stage of the pipeline.',
                    'base' => 'opportunities',
                    'group_by' => ['opportunities.stage'],
                    'columns' => [
                        Spec::col('stage', 'Stage', 'opportunities.stage', ['primary' => true]),
                        Spec::num('opportunities', 'Opportunities', 'count(*)'),
                        Spec::money('value', 'Estimated value', 'sum(opportunities.estimated_value)'),
                        Spec::num('average_probability', 'Avg probability %', 'round(avg(opportunities.probability), 2)'),
                        Spec::money('weighted', 'Weighted value', 'round(sum(opportunities.estimated_value * coalesce(opportunities.probability, 0) / 100), 2)'),
                    ],
                    'summary' => [
                        Spec::summary('opportunities', 'Opportunities', 'count(*)'),
                        Spec::summary('value', 'Total pipeline', 'sum(opportunities.estimated_value)', 'money'),
                    ],
                    'order' => [Spec::order('value', 'desc')],
                    'chart' => Spec::chart('bar', 'stage', 'value'),
                ],

                'by-owner' => [
                    'label' => 'Opportunities by Owner',
                    'description' => 'Who is carrying which pipeline.',
                    'base' => 'opportunities',
                    'joins' => [Spec::join('users', 'opportunities.user_id', 'users.id', 'left', 'user')],
                    'group_by' => ['user.id', 'user.name'],
                    'columns' => [
                        Spec::col('owner', 'Owner', 'user.name', ['primary' => true]),
                        Spec::num('opportunities', 'Opportunities', 'count(*)'),
                        Spec::money('value', 'Estimated value', 'sum(opportunities.estimated_value)'),
                        Spec::num('won', 'Won', "sum(case when opportunities.stage = 'Won' then 1 else 0 end)"),
                    ],
                    'summary' => [
                        Spec::summary('owners', 'Owners', 'count(distinct user.id)'),
                        Spec::summary('value', 'Total pipeline', 'sum(opportunities.estimated_value)', 'money'),
                    ],
                    'order' => [Spec::order('value', 'desc')],
                    'chart' => Spec::chart('bar', 'owner', 'value'),
                ],

                'won-lost' => [
                    'label' => 'Won and Lost',
                    'description' => 'The close rate and what was lost.',
                    'base' => 'opportunities',
                    'group_by' => ['opportunities.stage'],
                    'columns' => [
                        Spec::col('stage', 'Stage', 'opportunities.stage', ['primary' => true]),
                        Spec::num('opportunities', 'Opportunities', 'count(*)'),
                        Spec::money('estimated', 'Estimated', 'sum(opportunities.estimated_value)'),
                        Spec::money('final_value', 'Final value', 'sum(opportunities.actual_final_value)'),
                    ],
                    'filters' => [
                        Spec::sourceMultiFilter('stage', 'Stage', 'opportunities', 'stage', 'opportunities.stage'),
                    ],
                    'summary' => [
                        Spec::summary('opportunities', 'Opportunities', 'count(*)'),
                        Spec::summary('final_value', 'Final value', 'sum(opportunities.actual_final_value)', 'money'),
                    ],
                    'order' => [Spec::order('opportunities', 'desc')],
                    'chart' => Spec::chart('pie', 'stage', 'opportunities'),
                ],

                'monthly-trend' => [
                    'label' => 'Opportunity Monthly Trend',
                    'description' => 'How the pipeline was created month by month.',
                    'base' => 'opportunities',
                    'group_by' => ['year(opportunities.created_at)', 'month(opportunities.created_at)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(opportunities.created_at), '/', year(opportunities.created_at))", ['primary' => true]),
                        Spec::num('opportunities', 'Opportunities', 'count(*)'),
                        Spec::money('value', 'Estimated value', 'sum(opportunities.estimated_value)'),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('value', 'Total value', 'sum(opportunities.estimated_value)', 'money'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'value'),
                ],

                'work-types' => [
                    'label' => 'Opportunity Work Types',
                    'description' => 'What kind of work each opportunity covers.',
                    'base' => 'opportunity_work_types',
                    'joins' => [Spec::join('opportunities', 'opportunity_work_types.opportunity_id', 'opportunities.id', 'left', 'opportunity')],
                    'columns' => [
                        Spec::col('opportunity', 'Opportunity', 'opportunity.name', ['primary' => true]),
                        Spec::col('work_type', 'Work type', 'opportunity_work_types.work_type'),
                        Spec::money('estimated_value', 'Estimated value', 'opportunity.estimated_value'),
                        Spec::col('stage', 'Stage', 'opportunity.stage'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['opportunity.name', 'opportunity_work_types.work_type']),
                    ],
                    'summary' => [
                        Spec::summary('lines', 'Entries', 'count(*)'),
                        Spec::summary('work_types', 'Work types', 'count(distinct opportunity_work_types.work_type)'),
                    ],
                    'order' => [Spec::order('opportunity')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Follow-ups                                                          */
    /* ------------------------------------------------------------------ */

    private static function followUps(): array
    {
        return [
            'label' => 'Follow-ups',
            'category' => 'Sales & Survey',
            'permission' => 'follow-ups.view',
            'reports' => [

                'master' => [
                    'label' => 'Follow-up Register',
                    'description' => 'Every follow-up that has been scheduled.',
                    'base' => 'follow_ups',
                    'joins' => [
                        Spec::join('buildings', 'follow_ups.building_id', 'buildings.id', 'left', 'building'),
                        Spec::join('users', 'follow_ups.user_id', 'users.id', 'left', 'user'),
                    ],
                    'columns' => [
                        Spec::date('reminder_date', 'Reminder date', 'follow_ups.reminder_date', ['primary' => true]),
                        Spec::col('building', 'Building', 'building.name'),
                        Spec::col('type', 'Type', 'follow_ups.type'),
                        Spec::badge('status', 'Status', 'follow_ups.status'),
                        Spec::col('owner', 'Owner', 'user.name'),
                        Spec::col('notes', 'Notes', 'follow_ups.notes'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'follow_ups.reminder_date', '>='),
                        Spec::dateFilter('to', 'To', 'follow_ups.reminder_date', '<='),
                        Spec::sourceMultiFilter('status', 'Status', 'follow_ups', 'status', 'follow_ups.status'),
                        Spec::sourceFilter('type', 'Type', 'follow_ups', 'type', 'follow_ups.type'),
                        Spec::textFilter('q', 'Search', ['buildings.name', 'follow_ups.notes']),
                    ],
                    'summary' => [Spec::summary('follow_ups', 'Follow-ups', 'count(*)')],
                    'order' => [Spec::order('reminder_date', 'desc')],
                ],

                'by-status' => [
                    'label' => 'Follow-ups by Status',
                    'description' => 'What is done and what is outstanding.',
                    'base' => 'follow_ups',
                    'group_by' => ['follow_ups.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'follow_ups.status', ['primary' => true]),
                        Spec::num('follow_ups', 'Follow-ups', 'count(*)'),
                    ],
                    'summary' => [Spec::summary('follow_ups', 'Follow-ups', 'count(*)')],
                    'order' => [Spec::order('follow_ups', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'follow_ups'),
                ],

                'by-type' => [
                    'label' => 'Follow-ups by Type',
                    'description' => 'The kinds of follow-up being scheduled.',
                    'base' => 'follow_ups',
                    'group_by' => ['follow_ups.type'],
                    'columns' => [
                        Spec::col('type', 'Type', 'follow_ups.type', ['primary' => true]),
                        Spec::num('follow_ups', 'Follow-ups', 'count(*)'),
                    ],
                    'summary' => [Spec::summary('follow_ups', 'Follow-ups', 'count(*)')],
                    'order' => [Spec::order('follow_ups', 'desc')],
                    'chart' => Spec::chart('bar', 'type', 'follow_ups'),
                ],

                'by-owner' => [
                    'label' => 'Follow-ups by Owner',
                    'description' => 'How many reminders each person carries.',
                    'base' => 'follow_ups',
                    'joins' => [Spec::join('users', 'follow_ups.user_id', 'users.id', 'left', 'user')],
                    'group_by' => ['user.id', 'user.name'],
                    'columns' => [
                        Spec::col('owner', 'Owner', 'user.name', ['primary' => true]),
                        Spec::num('follow_ups', 'Follow-ups', 'count(*)'),
                        Spec::num('pending', 'Pending', "sum(case when follow_ups.status = 'Pending' then 1 else 0 end)"),
                    ],
                    'summary' => [
                        Spec::summary('owners', 'Owners', 'count(distinct user.id)'),
                        Spec::summary('follow_ups', 'Follow-ups', 'count(*)'),
                    ],
                    'order' => [Spec::order('follow_ups', 'desc')],
                    'chart' => Spec::chart('bar', 'owner', 'follow_ups'),
                ],

                'upcoming' => [
                    'label' => 'Upcoming Follow-ups',
                    'description' => 'The reminders that are still to come.',
                    'base' => 'follow_ups',
                    'joins' => [
                        Spec::join('buildings', 'follow_ups.building_id', 'buildings.id', 'left', 'building'),
                        Spec::join('users', 'follow_ups.user_id', 'users.id', 'left', 'user'),
                    ],
                    'columns' => [
                        Spec::date('reminder_date', 'Reminder date', 'follow_ups.reminder_date', ['primary' => true]),
                        Spec::col('building', 'Building', 'building.name'),
                        Spec::col('type', 'Type', 'follow_ups.type'),
                        Spec::badge('status', 'Status', 'follow_ups.status'),
                        Spec::col('owner', 'Owner', 'user.name'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'follow_ups.reminder_date', '>='),
                        Spec::dateFilter('to', 'To', 'follow_ups.reminder_date', '<='),
                        Spec::sourceFilter('status', 'Status', 'follow_ups', 'status', 'follow_ups.status'),
                    ],
                    'summary' => [Spec::summary('follow_ups', 'Follow-ups', 'count(*)')],
                    'order' => [Spec::order('reminder_date')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Sales documents                                                     */
    /* ------------------------------------------------------------------ */

    private static function salesDocuments(): array
    {
        return [
            'label' => 'Sales Documents',
            'category' => 'Sales & Survey',
            'permission' => 'sales-documents.view',
            'reports' => [

                'master' => [
                    'label' => 'Sales Document Register',
                    'description' => 'Every file attached to a survey or an opportunity.',
                    'base' => 'sales_documents',
                    'joins' => [
                        Spec::join('buildings', 'sales_documents.building_id', 'buildings.id', 'left', 'building'),
                        Spec::join('opportunities', 'sales_documents.opportunity_id', 'opportunities.id', 'left', 'opportunity'),
                        Spec::join('users', 'sales_documents.uploaded_by', 'users.id', 'left', 'user'),
                    ],
                    'columns' => [
                        Spec::col('name', 'Document', 'sales_documents.name', ['primary' => true]),
                        Spec::col('building', 'Building', 'building.name'),
                        Spec::col('opportunity', 'Opportunity', 'opportunity.name'),
                        Spec::col('category', 'Category', 'sales_documents.category'),
                        Spec::col('file_type', 'File type', 'sales_documents.file_type'),
                        Spec::num('file_size', 'Size (KB)', 'round(sales_documents.file_size / 1024, 2)'),
                        Spec::date('expiry_date', 'Expires on', 'sales_documents.expiry_date'),
                        Spec::col('uploaded_by', 'Uploaded by', 'user.name'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('category', 'Category', 'sales_documents', 'category', 'sales_documents.category'),
                        Spec::sourceFilter('file_type', 'File type', 'sales_documents', 'file_type', 'sales_documents.file_type'),
                        Spec::textFilter('q', 'Search', ['sales_documents.name', 'buildings.name']),
                    ],
                    'summary' => [
                        Spec::summary('documents', 'Documents', 'count(*)'),
                        Spec::summary('size', 'Total size (KB)', 'round(sum(sales_documents.file_size) / 1024, 2)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'by-category' => [
                    'label' => 'Documents by Category',
                    'description' => 'How the paperwork breaks down.',
                    'base' => 'sales_documents',
                    'group_by' => ['sales_documents.category'],
                    'columns' => [
                        Spec::col('category', 'Category', 'sales_documents.category', ['primary' => true]),
                        Spec::num('documents', 'Documents', 'count(*)'),
                        Spec::num('size', 'Size (KB)', 'round(sum(sales_documents.file_size) / 1024, 2)'),
                    ],
                    'summary' => [
                        Spec::summary('categories', 'Categories', 'count(distinct sales_documents.category)'),
                        Spec::summary('documents', 'Documents', 'count(*)'),
                    ],
                    'order' => [Spec::order('documents', 'desc')],
                    'chart' => Spec::chart('pie', 'category', 'documents'),
                ],

                'by-building' => [
                    'label' => 'Documents by Building',
                    'description' => 'Which buildings have the most paperwork.',
                    'base' => 'sales_documents',
                    'joins' => [Spec::join('buildings', 'sales_documents.building_id', 'buildings.id', 'left', 'building')],
                    'group_by' => ['building.id', 'building.name'],
                    'columns' => [
                        Spec::col('building', 'Building', 'building.name', ['primary' => true]),
                        Spec::num('documents', 'Documents', 'count(*)'),
                        Spec::num('size', 'Size (KB)', 'round(sum(sales_documents.file_size) / 1024, 2)'),
                    ],
                    'summary' => [
                        Spec::summary('buildings', 'Buildings', 'count(distinct building.id)'),
                        Spec::summary('documents', 'Documents', 'count(*)'),
                    ],
                    'order' => [Spec::order('documents', 'desc')],
                    'chart' => Spec::chart('bar', 'building', 'documents'),
                ],

                'expiring' => [
                    'label' => 'Expiring Documents',
                    'description' => 'The paperwork that is about to run out.',
                    'base' => 'sales_documents',
                    'joins' => [
                        Spec::join('buildings', 'sales_documents.building_id', 'buildings.id', 'left', 'building'),
                    ],
                    'columns' => [
                        Spec::col('name', 'Document', 'sales_documents.name', ['primary' => true]),
                        Spec::col('building', 'Building', 'building.name'),
                        Spec::col('category', 'Category', 'sales_documents.category'),
                        Spec::date('expiry_date', 'Expires on', 'sales_documents.expiry_date'),
                        Spec::num('version', 'Version', 'sales_documents.version_number'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Expiring from', 'sales_documents.expiry_date', '>='),
                        Spec::dateFilter('to', 'Expiring to', 'sales_documents.expiry_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('documents', 'Documents', 'count(*)'),
                        Spec::summary('expiring', 'With an expiry date', 'sum(case when sales_documents.expiry_date is not null then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('expiry_date')],
                ],
            ],
        ];
    }
}

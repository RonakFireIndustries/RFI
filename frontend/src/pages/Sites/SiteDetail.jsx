import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  ArrowLeft, MapPin, Phone, User, ShieldAlert, Building2, Compass,
  Users, Package, FileText, ClipboardList, ArrowLeftRight, Clock,
  CheckCircle2, XCircle, Info, Layers
} from 'lucide-react';
import { siteService } from '../../services/siteService';
import { buildingService } from '../../services/buildingService';
import { buildingDetailService } from '../../services/buildingDetailService';
import { employeeSiteService } from '../../services/employeeSiteService';
import { productStockService } from '../../services/productStockService';
import { transactionLedgerService } from '../../services/transactionLedgerService';
import { stockRequestService } from '../../services/stockRequestService';
import { dailyReportsService } from '../../services/dailyReportsService';
import { attendancesService } from '../../services/attendancesService';

const SITE_TYPE = 'App\\Models\\Site';

const dash = (v) => (v === null || v === undefined || v === '' ? '-' : v);

const formatDate = (v) => {
  if (!v) return '-';
  const d = new Date(v);
  if (Number.isNaN(d.getTime())) return v;
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatDateTime = (v) => {
  if (!v) return '-';
  const d = new Date(v);
  if (Number.isNaN(d.getTime())) return v;
  return d.toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const yesNo = (v) => (v === null || v === undefined || v === '' ? '-' : v ? 'Yes' : 'No');

function Field({ label, value, full }) {
  return (
    <div className={full ? 'col-span-full' : ''}>
      <p className="text-xs font-semibold text-gray-400 uppercase tracking-wide">{label}</p>
      <p className="text-sm text-gray-900 mt-1 break-words">{dash(value)}</p>
    </div>
  );
}

function Section({ title, icon: Icon, children }) {
  return (
    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
      <div className="flex items-center gap-2 mb-4">
        {Icon && <Icon className="w-5 h-5 text-blue-600" />}
        <h3 className="text-sm font-bold text-gray-800 uppercase tracking-wide">{title}</h3>
      </div>
      <div className="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">{children}</div>
    </div>
  );
}

function EmptyState({ icon: Icon, label }) {
  return (
    <div className="text-center py-12 text-gray-500 bg-white border border-gray-100 rounded-2xl">
      <Icon className="w-12 h-12 mx-auto mb-3 text-gray-300" />
      <p className="font-medium">{label}</p>
    </div>
  );
}

function DataTable({ columns, rows, emptyLabel, emptyIcon }) {
  if (!rows.length) return <EmptyState icon={emptyIcon || Info} label={emptyLabel} />;
  return (
    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div className="overflow-x-auto">
        <table className="w-full text-sm">
          <thead>
            <tr className="bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase">
              {columns.map((c) => (
                <th key={c.key} className={`px-4 py-3 ${c.align === 'right' ? 'text-right' : ''}`}>{c.label}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {rows.map((row, i) => (
              <tr key={row.id ?? i} className="border-t border-gray-50 hover:bg-gray-50/50">
                {columns.map((c) => (
                  <td key={c.key} className={`px-4 py-3 ${c.align === 'right' ? 'text-right' : ''} ${c.className || 'text-gray-700'}`}>
                    {c.render ? c.render(row) : dash(row[c.key])}
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

const TABS = [
  { key: 'overview', label: 'Overview', icon: Info },
  { key: 'structure', label: 'Structure', icon: Layers },
  { key: 'buildings', label: 'Buildings', icon: Building2 },
  { key: 'employees', label: 'Employees', icon: Users },
  { key: 'inventory', label: 'Inventory', icon: Package },
  { key: 'transactions', label: 'Transactions', icon: ArrowLeftRight },
  { key: 'requests', label: 'Stock Requests', icon: ClipboardList },
  { key: 'reports', label: 'Daily Reports', icon: FileText },
  { key: 'attendance', label: 'Attendance', icon: Clock },
];

export default function SiteDetail() {
  const { id } = useParams();
  const navigate = useNavigate();

  const [site, setSite] = useState(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('overview');

  const [buildings, setBuildings] = useState([]);
  const [wings, setWings] = useState([]);
  const [floors, setFloors] = useState([]);
  const [flats, setFlats] = useState([]);
  const [employees, setEmployees] = useState([]);
  const [inventory, setInventory] = useState([]);
  const [transactions, setTransactions] = useState([]);
  const [requests, setRequests] = useState([]);
  const [reports, setReports] = useState([]);
  const [attendance, setAttendance] = useState([]);
  const [relatedLoaded, setRelatedLoaded] = useState(false);

  useEffect(() => {
    let alive = true;

    const load = async () => {
      setLoading(true);
      try {
        const data = await siteService.get(id);
        if (alive) setSite(data);
      } catch (err) {
        console.error(err);
      } finally {
        if (alive) setLoading(false);
      }

      const [b, e, inv, tx, rq, rep, att] = await Promise.allSettled([
        buildingService.list({ site_id: id, per_page: 1000 }),
        employeeSiteService.getSiteEmployees(id),
        productStockService.byLocation('site', id),
        transactionLedgerService.list({ location_type: SITE_TYPE, location_id: id }),
        stockRequestService.list({ location_type: SITE_TYPE, location_id: id }),
        dailyReportsService.list({ site_id: id }),
        attendancesService.list({ site_id: id }),
      ]);

      if (!alive) return;
      const buildingList = b.status === 'fulfilled' ? b.value || [] : [];
      setBuildings(buildingList);
      setEmployees(e.status === 'fulfilled' ? e.value || [] : []);
      setInventory(inv.status === 'fulfilled' ? inv.value || [] : []);
      setTransactions(tx.status === 'fulfilled' ? tx.value || [] : []);
      setRequests(rq.status === 'fulfilled' ? rq.value || [] : []);
      setReports(rep.status === 'fulfilled' ? rep.value || [] : []);
      setAttendance(att.status === 'fulfilled' ? att.value || [] : []);
      setRelatedLoaded(true);

      // Structure detail (wings -> floors -> flats) lives on the source
      // buildings, so pull it once the buildings are known.
      if (buildingList.length) {
        const structure = await Promise.allSettled(
          buildingList.map(async (building) => {
            const [w, f, fl] = await Promise.allSettled([
              buildingDetailService.getWings(building.id),
              buildingDetailService.getFloors(building.id),
              buildingDetailService.getFlats(building.id),
            ]);
            return {
              wings: w.status === 'fulfilled' ? w.value || [] : [],
              floors: f.status === 'fulfilled' ? f.value || [] : [],
              flats: fl.status === 'fulfilled' ? fl.value || [] : [],
            };
          })
        );

        if (!alive) return;
        const allWings = [];
        const allFloors = [];
        const allFlats = [];
        structure.forEach((result) => {
          if (result.status !== 'fulfilled') return;
          allWings.push(...result.value.wings);
          allFloors.push(...result.value.floors);
          allFlats.push(...result.value.flats);
        });
        setWings(allWings);
        setFloors(allFloors);
        setFlats(allFlats);
      }
    };

    load();
    return () => { alive = false; };
  }, [id]);

  if (loading) {
    return <div className="flex items-center justify-center h-64"><div className="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600" /></div>;
  }

  if (!site) {
    return (
      <div className="text-center py-12 text-gray-500">
        <p>Site not found.</p>
        <button onClick={() => navigate('/dashboard/sites')} className="mt-4 px-4 py-2 bg-primary text-white rounded-lg text-sm">Back to Sites</button>
      </div>
    );
  }

  const getScheduleStatus = (endDate) => (endDate && new Date() > new Date(endDate) ? 'Delayed' : 'On Time');

  // The structural survey values live on the source building for a converted
  // site, so fall back to it whenever the site's own copy is empty.
  const primaryBuilding = buildings[0];
  const structureValue = (key) => {
    const own = site[key];
    if (own !== null && own !== undefined && own !== '') return own;
    return primaryBuilding ? primaryBuilding[key] : null;
  };
  const structureFromChildren = (key, rows) => {
    const own = structureValue(key);
    if (own !== null && own !== undefined && own !== '') return own;
    return rows.length ? rows.length : null;
  };

  const floorsByWing = floors.reduce((acc, f) => {
    (acc[f.wing_id || 'none'] ||= []).push(f);
    return acc;
  }, {});
  const flatsByFloor = flats.reduce((acc, f) => {
    (acc[f.floor_id || 'none'] ||= []).push(f);
    return acc;
  }, {});

  const statCards = [
    { label: 'Source Buildings', value: site.source_buildings_count ?? buildings.length, icon: Building2, color: 'bg-blue-50 text-blue-600' },
    { label: 'Assigned Employees', value: site.employees_count ?? employees.length, icon: Users, color: 'bg-indigo-50 text-indigo-600' },
    { label: 'Stock Line Items', value: site.stock_count ?? inventory.length, icon: Package, color: 'bg-emerald-50 text-emerald-600' },
    { label: 'Daily Reports', value: site.daily_reports_count ?? reports.length, icon: FileText, color: 'bg-amber-50 text-amber-600' },
  ];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <button onClick={() => navigate('/dashboard/sites')} className="p-2 hover:bg-gray-100 rounded-lg" title="Back">
            <ArrowLeft className="w-5 h-5" />
          </button>
          <div>
            <div className="flex items-center gap-2">
              <h1 className="text-2xl font-bold text-gray-900">{site.name}</h1>
              <span className={`px-2.5 py-1 rounded-full text-xs font-bold ${site.status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600'}`}>
                {site.status}
              </span>
            </div>
            <p className="text-sm text-gray-500 mt-0.5">
              {site.code ? `${site.code} • ` : ''}{[site.address, site.city, site.state].filter(Boolean).join(', ') || 'No address'}
            </p>
          </div>
        </div>
      </div>

      {/* Stat cards */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        {statCards.map((s) => (
          <div key={s.label} className="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-3">
            <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${s.color}`}>
              <s.icon className="w-5 h-5" />
            </div>
            <div>
              <div className="text-xl font-bold text-gray-900">{s.value}</div>
              <div className="text-xs text-gray-500 font-medium">{s.label}</div>
            </div>
          </div>
        ))}
      </div>

      {/* Tabs */}
      <div className="flex gap-1 border-b overflow-x-auto">
        {TABS.map((tab) => (
          <button
            key={tab.key}
            onClick={() => setActiveTab(tab.key)}
            className={`flex items-center gap-2 px-4 py-2.5 text-sm font-medium border-b-2 whitespace-nowrap transition-colors ${
              activeTab === tab.key ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'
            }`}
          >
            <tab.icon className="w-4 h-4" /> {tab.label}
          </button>
        ))}
      </div>

      {/* Overview */}
      {activeTab === 'overview' && (
        <div className="space-y-6">
          <Section title="Basic Information" icon={Info}>
            <Field label="Site Name" value={site.name} />
            <Field label="Site Code" value={site.code} />
            <Field label="Status" value={site.status} />
            <Field label="Client Details" value={site.client_details} />
            <Field label="Site Manager" value={site.site_manager?.name} />
            <Field label="Start Date" value={formatDate(site.start_date)} />
            <Field label="End Date" value={formatDate(site.end_date)} />
            <Field label="Schedule Status" value={site.end_date ? getScheduleStatus(site.end_date) : '-'} />
            <Field label="Created At" value={formatDateTime(site.created_at)} />
            <Field label="Updated At" value={formatDateTime(site.updated_at)} />
          </Section>

          <Section title="Location" icon={MapPin}>
            <Field label="Address" value={site.address} full />
            <Field label="City" value={site.city} />
            <Field label="State" value={site.state} />
            <Field label="Country" value={site.country} />
            <Field label="Pincode" value={site.pincode} />
            <Field label="Area" value={site.area} />
            <Field label="Latitude" value={site.latitude} />
            <Field label="Longitude" value={site.longitude} />
            <Field label="Google Place ID" value={site.google_place_id} />
            {site.latitude && site.longitude && (
              <div className="col-span-full">
                <a
                  href={`https://www.google.com/maps?q=${site.latitude},${site.longitude}`}
                  target="_blank"
                  rel="noreferrer"
                  className="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline"
                >
                  <Compass className="w-4 h-4" /> View on Google Maps
                </a>
              </div>
            )}
          </Section>

          <Section title="Contact" icon={Phone}>
            <Field label="Contact Person" value={site.contact_person} />
            <Field label="Phone" value={site.phone} />
            <Field label="Email" value={site.email} />
            <Field label="Contact Name" value={site.contact_name} />
            <Field label="Contact Number" value={site.contact_number} />
          </Section>

          <Section title="Geo-Fencing" icon={ShieldAlert}>
            <Field label="Geo-Fencing Enabled" value={yesNo(site.geo_fencing_enabled)} />
            <Field label="Allowed Radius" value={site.allowed_radius != null ? `${site.allowed_radius} m` : '-'} />
          </Section>

          <Section title="Building Profile" icon={Building2}>
            <Field label="Society Name" value={site.society_name} />
            <Field label="Building Type" value={site.building_type} />
            <Field label="Construction Year" value={site.construction_year} />
            <Field label="Occupancy Certificate Year" value={site.occupancy_certificate_year} />
            <Field label="Fire NOC Status" value={site.fire_noc_status} />
            <Field label="Under Construction" value={yesNo(site.under_construction)} />
            <Field label="Property Owner" value={site.property_owner} />
            <Field label="Plot No" value={site.plot_no} />
          </Section>

          <Section title="Structure" icon={Building2}>
            <Field label="No. of Floors" value={structureValue('no_of_floors')} />
            <Field label="Basement Levels" value={structureValue('basement_levels')} />
            <Field label="No. of Wings" value={structureFromChildren('no_of_wings', wings)} />
            <Field label="No. of Flats" value={structureFromChildren('no_of_flats', flats)} />
            <Field label="Terrace Available" value={yesNo(structureValue('terrace_available'))} />
            <Field label="Refuge Floors" value={structureValue('refuge_floors')} />
            <Field label="Commercial Shops Available" value={yesNo(structureValue('commercial_shops_available'))} />
            <Field label="No. of Staircases" value={structureValue('no_of_staircase')} />
            <Field label="No. of Lifts" value={structureValue('no_of_lifts')} />
            <Field label="No. of Exits / Entries" value={structureValue('no_of_exits_entry')} />
          </Section>

          <Section title="Fire Safety & Equipment" icon={ShieldAlert}>
            <Field label="Fire Safety Available" value={yesNo(site.fire_safety_available)} />
            <Field label="Fire Safety Type" value={site.fire_safety_type} />
            <Field label="Last Serviced Date" value={formatDate(site.last_serviced_date)} />
            <Field label="Last AMC Company" value={site.last_amc_company} />
            <Field label="Overall Equipment Condition" value={site.overall_equipment_condition} />
            <Field label="Equipment Condition Notes" value={site.equipment_condition_notes} full />
          </Section>

          <Section title="Stakeholders" icon={User}>
            <Field label="Developer Name" value={site.developer_name} />
            <Field label="Developer Contact" value={site.developer_contact} />
            <Field label="Architect Name" value={site.architect_name} />
            <Field label="Architect Contact" value={site.architect_contact} />
            <Field label="PMC Consultant Name" value={site.pmc_consultant_name} />
            <Field label="PMC Consultant Contact" value={site.pmc_consultant_contact} />
          </Section>

          <Section title="Approvals & NOC" icon={FileText}>
            <Field label="Provisional NOC" value={yesNo(site.provisional_noc)} />
            <Field label="Provisional NOC Details" value={site.provisional_noc_details} full />
          </Section>
        </div>
      )}

      {/* Structure */}
      {activeTab === 'structure' && (
        <div className="space-y-6">
          <div className="grid grid-cols-3 gap-4">
            {[
              { label: 'Wings', value: wings.length, icon: Layers },
              { label: 'Floors', value: floors.length, icon: Building2 },
              { label: 'Flats', value: flats.length, icon: Building2 },
            ].map((s) => (
              <div key={s.label} className="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-3">
                <div className="w-10 h-10 rounded-xl flex items-center justify-center bg-blue-50 text-blue-600">
                  <s.icon className="w-5 h-5" />
                </div>
                <div>
                  <div className="text-xl font-bold text-gray-900">{s.value}</div>
                  <div className="text-xs text-gray-500 font-medium">{s.label}</div>
                </div>
              </div>
            ))}
          </div>

          {!relatedLoaded ? (
            <div className="flex justify-center py-12"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600" /></div>
          ) : wings.length === 0 ? (
            <EmptyState icon={Layers} label="No wing / floor / flat structure recorded for this site" />
          ) : (
            wings.map((wing) => {
              const wingFloors = floorsByWing[wing.id] || [];
              return (
                <div key={wing.id} className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                  <div className="flex items-center justify-between px-5 py-4 bg-gray-50 border-b border-gray-100">
                    <div className="flex items-center gap-3">
                      <div className="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                        {(wing.name || '?').substring(0, 1).toUpperCase()}
                      </div>
                      <div>
                        <p className="font-semibold text-gray-900">{wing.name || 'Unnamed Wing'}</p>
                        <p className="text-xs text-gray-500">
                          {[wing.floors != null && `${wing.floors} floors`, wing.flats_per_floor != null && `${wing.flats_per_floor} flats/floor`, wing.flat_configuration].filter(Boolean).join(' • ') || 'No details'}
                        </p>
                      </div>
                    </div>
                    <div className="text-right text-xs text-gray-500">
                      <p><span className="font-semibold text-gray-700">{wing.total_flats ?? wingFloors.length}</span> total flats</p>
                      <p>{wingFloors.length} floors</p>
                    </div>
                  </div>

                  <div className="divide-y divide-gray-50">
                    {wingFloors.length === 0 ? (
                      <p className="px-5 py-4 text-sm text-gray-400">No floors recorded for this wing</p>
                    ) : wingFloors.map((floor) => {
                      const floorFlats = flatsByFloor[floor.id] || [];
                      return (
                        <div key={floor.id} className="px-5 py-4">
                          <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                              <Building2 className="w-4 h-4 text-gray-400" />
                              <span className="text-sm font-medium text-gray-800">{floor.name || 'Unnamed Floor'}</span>
                              {floor.floor_number != null && <span className="text-xs text-gray-400">#{floor.floor_number}</span>}
                              {floor.type && <span className="px-2 py-0.5 bg-gray-100 rounded text-[11px] text-gray-600">{floor.type}</span>}
                            </div>
                            <div className="text-xs text-gray-500 flex items-center gap-3">
                              {floor.area != null && <span>Area {floor.area}</span>}
                              <span className="font-semibold text-gray-700">{floorFlats.length || floor.count || 0} flats</span>
                            </div>
                          </div>

                          {floorFlats.length > 0 && (
                            <div className="mt-3 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                              {floorFlats.map((flat) => (
                                <div key={flat.id} className="px-3 py-2 bg-gray-50 rounded-lg border border-gray-100">
                                  <p className="text-sm font-medium text-gray-800">{flat.name || flat.flat_number || 'Flat'}</p>
                                  <p className="text-xs text-gray-500">
                                    {[flat.flat_number && `#${flat.flat_number}`, flat.bhk_type, flat.area != null && `${flat.area} area`].filter(Boolean).join(' • ') || 'No details'}
                                  </p>
                                </div>
                              ))}
                            </div>
                          )}
                        </div>
                      );
                    })}
                  </div>
                </div>
              );
            })
          )}
        </div>
      )}

      {/* Buildings */}
      {activeTab === 'buildings' && (
        <DataTable
          emptyLabel={relatedLoaded ? 'No buildings linked to this site' : 'Loading buildings...'}
          emptyIcon={Building2}
          columns={[
            { key: 'name', label: 'Building', render: (r) => <span className="font-medium text-gray-800">{r.name}</span> },
            { key: 'society_name', label: 'Society' },
            { key: 'city', label: 'City' },
            { key: 'no_of_floors', label: 'Floors', align: 'right' },
            { key: 'status', label: 'Status', render: (r) => (
              <span className={`px-2 py-0.5 rounded-full text-xs font-semibold ${r.status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600'}`}>{r.status}</span>
            ) },
            { key: 'action', label: '', render: (r) => (
              <button onClick={() => navigate(`/dashboard/buildings/${r.id}`)} className="text-blue-600 hover:underline text-xs font-semibold">View</button>
            ) },
          ]}
          rows={buildings}
        />
      )}

      {/* Employees */}
      {activeTab === 'employees' && (
        <DataTable
          emptyLabel={relatedLoaded ? 'No employees assigned to this site' : 'Loading employees...'}
          emptyIcon={Users}
          columns={[
            { key: 'name', label: 'Employee', render: (r) => (
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center text-xs font-bold">
                  {(r.full_name || r.name || r.employee?.full_name || r.employee?.name || '?').substring(0, 2).toUpperCase()}
                </div>
                <span className="font-medium text-gray-800">{r.full_name || r.name || r.employee?.full_name || r.employee?.name || '-'}</span>
              </div>
            ) },
            { key: 'emp_id', label: 'Emp ID', render: (r) => dash(r.emp_id || r.employee?.emp_id) },
            { key: 'designation', label: 'Designation', render: (r) => dash(r.designation?.name || r.designation) },
            { key: 'department', label: 'Department', render: (r) => dash(r.department?.name || r.department) },
            { key: 'contact', label: 'Contact', render: (r) => dash(r.contact_number || r.phone || r.employee?.contact_number) },
          ]}
          rows={employees}
        />
      )}

      {/* Inventory */}
      {activeTab === 'inventory' && (
        <DataTable
          emptyLabel={relatedLoaded ? 'No stock found at this site' : 'Loading inventory...'}
          emptyIcon={Package}
          columns={[
            { key: 'product', label: 'Product', render: (r) => <span className="font-medium text-gray-800">{r.product?.name || `Product #${r.product_id}`}</span> },
            { key: 'sku', label: 'SKU', render: (r) => dash(r.product?.sku) },
            { key: 'quantity', label: 'Quantity', align: 'right', render: (r) => r.quantity },
            { key: 'reserved_quantity', label: 'Reserved', align: 'right', className: 'text-amber-600', render: (r) => r.reserved_quantity },
            { key: 'available_quantity', label: 'Available', align: 'right', className: 'text-emerald-600 font-semibold', render: (r) => r.available_quantity },
          ]}
          rows={inventory}
        />
      )}

      {/* Transactions */}
      {activeTab === 'transactions' && (
        <DataTable
          emptyLabel={relatedLoaded ? 'No stock transactions for this site' : 'Loading transactions...'}
          emptyIcon={ArrowLeftRight}
          columns={[
            { key: 'transaction_number', label: 'Transaction #', render: (r) => <span className="font-medium text-gray-800">{dash(r.transaction_number)}</span> },
            { key: 'product', label: 'Product', render: (r) => dash(r.product?.name) },
            { key: 'transaction_type', label: 'Type', render: (r) => <span className="capitalize">{dash(r.transaction_type)}</span> },
            { key: 'quantity', label: 'Qty', align: 'right', render: (r) => r.quantity },
            { key: 'creator', label: 'By', render: (r) => dash(r.creator) },
            { key: 'created_at', label: 'Date', render: (r) => formatDateTime(r.created_at) },
          ]}
          rows={transactions}
        />
      )}

      {/* Stock Requests */}
      {activeTab === 'requests' && (
        <DataTable
          emptyLabel={relatedLoaded ? 'No stock requests for this site' : 'Loading stock requests...'}
          emptyIcon={ClipboardList}
          columns={[
            { key: 'request_number', label: 'Request #', render: (r) => <span className="font-medium text-gray-800">{dash(r.request_number)}</span> },
            { key: 'product', label: 'Product', render: (r) => dash(r.product?.name) },
            { key: 'quantity', label: 'Qty', align: 'right', render: (r) => r.quantity },
            { key: 'direction', label: 'Direction', render: (r) => (Number(r.from_location_id) === Number(id) ? 'Outgoing' : 'Incoming') },
            { key: 'status', label: 'Status', render: (r) => <span className="capitalize">{dash(r.status)}</span> },
            { key: 'created_at', label: 'Date', render: (r) => formatDateTime(r.created_at) },
          ]}
          rows={requests}
        />
      )}

      {/* Daily Reports */}
      {activeTab === 'reports' && (
        <DataTable
          emptyLabel={relatedLoaded ? 'No daily reports for this site' : 'Loading daily reports...'}
          emptyIcon={FileText}
          columns={[
            { key: 'date', label: 'Date', render: (r) => formatDate(r.date) },
            { key: 'employee', label: 'Employee', render: (r) => dash(r.employee?.full_name || r.employee?.name) },
            { key: 'work_description', label: 'Work Description', className: 'text-gray-600 max-w-md truncate', render: (r) => dash(r.work_description) },
            { key: 'hours_worked', label: 'Hours', align: 'right', render: (r) => dash(r.hours_worked) },
            { key: 'status', label: 'Status', render: (r) => <span className="capitalize">{dash(r.status)}</span> },
          ]}
          rows={reports}
        />
      )}

      {/* Attendance */}
      {activeTab === 'attendance' && (
        <DataTable
          emptyLabel={relatedLoaded ? 'No attendance records for this site' : 'Loading attendance...'}
          emptyIcon={Clock}
          columns={[
            { key: 'date', label: 'Date', render: (r) => formatDate(r.date) },
            { key: 'employee', label: 'Employee', render: (r) => dash(r.employee?.full_name || r.employee?.name) },
            { key: 'check_in', label: 'Check In', render: (r) => dash(r.check_in) },
            { key: 'check_out', label: 'Check Out', render: (r) => dash(r.check_out) },
            { key: 'working_hours', label: 'Hours', align: 'right', render: (r) => dash(r.working_hours) },
            { key: 'status', label: 'Status', render: (r) => {
              const ok = r.status === 'present' || r.status === 'Present';
              return (
                <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold ${ok ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600'}`}>
                  {ok ? <CheckCircle2 className="w-3 h-3" /> : <XCircle className="w-3 h-3" />}{dash(r.status)}
                </span>
              );
            } },
          ]}
          rows={attendance}
        />
      )}
    </div>
  );
}

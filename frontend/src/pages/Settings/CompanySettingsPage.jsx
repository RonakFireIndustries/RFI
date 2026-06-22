import { useState, useEffect } from 'react';
import api, { STORAGE_URL } from '../../services/api';
import { Loader } from 'lucide-react';

export default function CompanySettingsPage() {
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({
    company_name: '',
    address: '',
    gst_number: '',
    vat_number: '',
    tax_registration_number: '',
    contact_email: '',
    contact_phone: '',
    website: '',
    bank_name: '',
    bank_account_name: '',
    bank_account_number: '',
    bank_ifsc_code: '',
    bank_swift_code: '',
    bank_branch: '',
    signatory_name: '',
  });
  const [signatureFile, setSignatureFile] = useState(null);
  const [sealFile, setSealFile] = useState(null);
  const [signaturePreview, setSignaturePreview] = useState(null);
  const [sealPreview, setSealPreview] = useState(null);
  const [existingSignature, setExistingSignature] = useState(null);
  const [existingSeal, setExistingSeal] = useState(null);

  useEffect(() => {
    loadSettings();
  }, []);

  const loadSettings = async () => {
    try {
      setLoading(true);
      const { data } = await api.get('/company-settings');
      if (data) {
        setForm({
          company_name: data.company_name || '',
          address: data.address || '',
          gst_number: data.gst_number || '',
          vat_number: data.vat_number || '',
          tax_registration_number: data.tax_registration_number || '',
          contact_email: data.contact_email || '',
          contact_phone: data.contact_phone || '',
          website: data.website || '',
          bank_name: data.bank_name || '',
          bank_account_name: data.bank_account_name || '',
          bank_account_number: data.bank_account_number || '',
          bank_ifsc_code: data.bank_ifsc_code || '',
          bank_swift_code: data.bank_swift_code || '',
          bank_branch: data.bank_branch || '',
          signatory_name: data.signatory_name || '',
        });
        if (data.signature_image) {
          setExistingSignature(STORAGE_URL + '/' + data.signature_image);
        }
        if (data.company_seal) {
          setExistingSeal(STORAGE_URL + '/' + data.company_seal);
        }
      }
    } catch (err) {
      alert('Failed to load settings');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    setForm(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      setSaving(true);
      const fd = new FormData();
      Object.entries(form).forEach(([key, value]) => {
        if (value) fd.append(key, value);
      });
      if (signatureFile) fd.append('signature_image', signatureFile);
      if (sealFile) fd.append('company_seal', sealFile);

      const { meta } = await api.post('/company-settings', fd);
      alert(meta?.message || 'Settings updated');
      setSignatureFile(null);
      setSealFile(null);
      loadSettings();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to update settings');
    } finally {
      setSaving(false);
    }
  };

  const clearField = (field) => {
    setForm(prev => ({ ...prev, [field]: '' }));
  };

  const inputClass = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none';
  const labelClass = 'block text-xs font-medium text-gray-600 mb-1';

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <Loader className="w-6 h-6 animate-spin text-gray-400" />
      </div>
    );
  }

  return (
    <div className="p-6 max-w-4xl mx-auto">
      <h1 className="text-xl font-bold text-gray-800 mb-6">Company Settings</h1>
      <p className="text-sm text-gray-500 mb-6">
        Configure your company information, bank details, and signature — these appear on invoices, quotations, and other documents.
      </p>

      <form onSubmit={handleSubmit} className="space-y-8">
        <section className="bg-white rounded-xl border border-gray-200 p-6">
          <h2 className="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">Company Information</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="md:col-span-2">
              <label className={labelClass}>Company Name</label>
              <input name="company_name" value={form.company_name} onChange={handleChange} className={inputClass} />
            </div>
            <div className="md:col-span-2">
              <label className={labelClass}>Address</label>
              <textarea name="address" value={form.address} onChange={handleChange} rows={3} className={inputClass} />
            </div>
            <div>
              <label className={labelClass}>GST Number</label>
              <input name="gst_number" value={form.gst_number} onChange={handleChange} className={inputClass} placeholder="22AAAAA0000A1Z5" />
            </div>
            <div>
              <label className={labelClass}>VAT Number</label>
              <input name="vat_number" value={form.vat_number} onChange={handleChange} className={inputClass} />
            </div>
            <div>
              <label className={labelClass}>Tax Registration Number</label>
              <input name="tax_registration_number" value={form.tax_registration_number} onChange={handleChange} className={inputClass} />
            </div>
            <div>
              <label className={labelClass}>Website</label>
              <input name="website" value={form.website} onChange={handleChange} className={inputClass} placeholder="www.example.com" />
            </div>
            <div>
              <label className={labelClass}>Contact Email</label>
              <input name="contact_email" type="email" value={form.contact_email} onChange={handleChange} className={inputClass} />
            </div>
            <div>
              <label className={labelClass}>Contact Phone</label>
              <input name="contact_phone" value={form.contact_phone} onChange={handleChange} className={inputClass} />
            </div>
          </div>
        </section>

        <section className="bg-white rounded-xl border border-gray-200 p-6">
          <h2 className="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">Bank Details</h2>
          <p className="text-xs text-gray-400 mb-4">Leave all fields empty to omit bank details from invoices.</p>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className={labelClass}>Bank Name</label>
              <div className="flex gap-2">
                <input name="bank_name" value={form.bank_name} onChange={handleChange} className={inputClass} placeholder="State Bank of India" />
                {form.bank_name && <button type="button" onClick={() => clearField('bank_name')} className="text-xs text-red-500 hover:text-red-700 whitespace-nowrap">Clear</button>}
              </div>
            </div>
            <div>
              <label className={labelClass}>Account Name</label>
              <input name="bank_account_name" value={form.bank_account_name} onChange={handleChange} className={inputClass} />
            </div>
            <div>
              <label className={labelClass}>Account Number</label>
              <input name="bank_account_number" value={form.bank_account_number} onChange={handleChange} className={inputClass} />
            </div>
            <div>
              <label className={labelClass}>IFSC Code</label>
              <input name="bank_ifsc_code" value={form.bank_ifsc_code} onChange={handleChange} className={inputClass} placeholder="SBIN0001234" />
            </div>
            <div>
              <label className={labelClass}>SWIFT Code</label>
              <input name="bank_swift_code" value={form.bank_swift_code} onChange={handleChange} className={inputClass} placeholder="SBININBB123" />
            </div>
            <div>
              <label className={labelClass}>Branch</label>
              <div className="flex gap-2">
                <input name="bank_branch" value={form.bank_branch} onChange={handleChange} className={inputClass} placeholder="Mumbai Main Branch" />
                {form.bank_branch && <button type="button" onClick={() => clearField('bank_branch')} className="text-xs text-red-500 hover:text-red-700 whitespace-nowrap">Clear</button>}
              </div>
            </div>
          </div>
        </section>

        <section className="bg-white rounded-xl border border-gray-200 p-6">
          <h2 className="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">Signature & Seal</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className={labelClass}>Signatory Name</label>
              <input name="signatory_name" value={form.signatory_name} onChange={handleChange} className={inputClass} />
            </div>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <div>
              <label className={labelClass}>Signature Image</label>
              <input type="file" accept="image/*" onChange={e => { setSignatureFile(e.target.files[0]); setSignaturePreview(URL.createObjectURL(e.target.files[0])); }} className="text-sm" />
              {existingSignature && !signatureFile && (
                <img src={existingSignature} alt="Signature" className="mt-2 h-12 object-contain" />
              )}
              {signaturePreview && (
                <img src={signaturePreview} alt="New Signature" className="mt-2 h-12 object-contain" />
              )}
            </div>
            <div>
              <label className={labelClass}>Company Seal</label>
              <input type="file" accept="image/*" onChange={e => { setSealFile(e.target.files[0]); setSealPreview(URL.createObjectURL(e.target.files[0])); }} className="text-sm" />
              {existingSeal && !sealFile && (
                <img src={existingSeal} alt="Seal" className="mt-2 h-16 object-contain" />
              )}
              {sealPreview && (
                <img src={sealPreview} alt="New Seal" className="mt-2 h-16 object-contain" />
              )}
            </div>
          </div>
        </section>

        <div className="flex justify-end gap-3">
          <button type="submit" disabled={saving} className="px-6 py-2.5 bg-[#2563eb] text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
            {saving ? 'Saving...' : 'Save Settings'}
          </button>
        </div>
      </form>
    </div>
  );
}

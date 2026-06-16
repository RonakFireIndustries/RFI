import React, { useState } from 'react';
import { 
  ChevronRight, Mail, Phone, MapPin, Edit, Share2, 
  Download, CheckCircle, FileText, User
} from 'lucide-react';
import { useParams, Link } from 'react-router-dom';

const tabs = ['Personal Info', 'Employment', 'Salary', 'Documents', 'Attendance History', 'Leave', 'Appraisal'];

export default function EmployeeProfile() {
  const { id } = useParams();
  const [activeTab, setActiveTab] = useState('Personal Info');

  return (
    <div className="w-full">
      {/* Breadcrumb */}
      <div className="flex items-center text-sm font-semibold text-[#718096] mb-6">
        <Link to="/employees" className="hover:text-[#0B1B36] transition-colors">Employee Directory</Link>
        <ChevronRight className="w-4 h-4 mx-2" />
        <span className="text-[#0B1B36]">Rajesh Kumar</span>
      </div>

      {/* Profile Header Card */}
      <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div className="flex items-center gap-6">
          <img 
            src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&auto=format&fit=crop&w=256&q=80" 
            alt="Rajesh Kumar" 
            className="w-24 h-24 rounded-2xl object-cover border-4 border-[#F7FAFC]"
          />
          <div>
            <h1 className="text-2xl font-extrabold text-[#0B1B36] mb-2">Rajesh Kumar</h1>
            <div className="flex items-center gap-3 mb-3">
              <span className="px-2.5 py-1 bg-[#EBF8FF] text-[#3182CE] text-xs font-bold rounded">EMP-2024-042</span>
              <span className="px-2.5 py-1 bg-[#EDF2F7] text-[#4A5568] text-xs font-bold rounded">Site Manager</span>
            </div>
            <div className="flex items-center gap-4 text-sm font-semibold text-[#718096]">
              <div className="flex items-center"><Mail className="w-4 h-4 mr-1.5" /> rajesh.kumar@buildforce.in</div>
              <div className="flex items-center"><Phone className="w-4 h-4 mr-1.5" /> +91 98765 43210</div>
              <div className="flex items-center"><MapPin className="w-4 h-4 mr-1.5" /> Project Alpha, Site B</div>
            </div>
          </div>
        </div>
        <div className="flex gap-3 w-full md:w-auto">
          <button className="flex-1 md:flex-none flex items-center justify-center px-6 py-2.5 bg-[#0B1B36] text-white rounded-xl font-bold hover:bg-[#081428] transition-colors shadow-sm">
            <Edit className="w-4 h-4 mr-2" /> Edit Profile
          </button>
          <button className="flex items-center justify-center px-3 py-2.5 bg-white border border-[#CBD5E0] text-[#4A5568] rounded-xl hover:bg-gray-50 transition-colors">
            <Share2 className="w-5 h-5" />
          </button>
        </div>
      </div>

      {/* Tabs */}
      <div className="border-b border-gray-200 mb-8 flex overflow-x-auto hide-scrollbar">
        {tabs.map(tab => (
          <button
            key={tab}
            onClick={() => setActiveTab(tab)}
            className={`whitespace-nowrap pb-4 px-1 mr-8 text-sm font-bold border-b-2 transition-colors ${
              activeTab === tab 
                ? 'border-[#0B1B36] text-[#0B1B36]' 
                : 'border-transparent text-[#718096] hover:text-[#4A5568]'
            }`}
          >
            {tab}
          </button>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Main Content Area */}
        <div className="lg:col-span-2 space-y-6">
          {activeTab === 'Personal Info' && (
            <>
              {/* Basic Information */}
              <div className="bg-white border border-gray-200 rounded-2xl p-8 shadow-sm">
                <div className="flex justify-between items-center mb-6">
                  <h2 className="text-lg font-bold text-[#0B1B36]">Basic Information</h2>
                  <button className="text-sm font-semibold text-[#3182CE] hover:underline">View History</button>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-12">
                  <div>
                    <div className="text-xs font-bold text-[#718096] mb-1">Full Name</div>
                    <div className="text-[15px] font-semibold text-[#1A202C]">Rajesh Kumar</div>
                  </div>
                  <div>
                    <div className="text-xs font-bold text-[#718096] mb-1">Date of Birth</div>
                    <div className="text-[15px] font-semibold text-[#1A202C]">15 May 1985</div>
                  </div>
                  <div>
                    <div className="text-xs font-bold text-[#718096] mb-1">Gender</div>
                    <div className="text-[15px] font-semibold text-[#1A202C]">Male</div>
                  </div>
                  <div>
                    <div className="text-xs font-bold text-[#718096] mb-1">Blood Group</div>
                    <div className="text-[15px] font-semibold text-[#1A202C]">O+ Positive</div>
                  </div>
                  <div>
                    <div className="text-xs font-bold text-[#718096] mb-1">Marital Status</div>
                    <div className="text-[15px] font-semibold text-[#1A202C]">Married</div>
                  </div>
                  <div>
                    <div className="text-xs font-bold text-[#718096] mb-1">Nationality</div>
                    <div className="text-[15px] font-semibold text-[#1A202C]">Indian</div>
                  </div>
                </div>
              </div>

              {/* Contact & Address */}
              <div className="bg-white border border-gray-200 rounded-2xl p-8 shadow-sm">
                <h2 className="text-lg font-bold text-[#0B1B36] mb-6">Contact & Address</h2>
                <div className="space-y-6">
                  <div>
                    <div className="text-xs font-bold text-[#718096] mb-1">Permanent Address</div>
                    <div className="text-[15px] font-semibold text-[#1A202C]">42, Green Avenue, Sector 15, Gurgaon, Haryana - 122001</div>
                  </div>
                  <div>
                    <div className="text-xs font-bold text-[#718096] mb-1">Current Address</div>
                    <div className="text-[15px] font-semibold text-[#1A202C]">Building A2, She Housing Complex, Navi Mumbai - 400706</div>
                  </div>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <div className="text-xs font-bold text-[#718096] mb-1">Secondary Phone</div>
                      <div className="text-[15px] font-semibold text-[#1A202C]">+91 99887 76655</div>
                    </div>
                    <div>
                      <div className="text-xs font-bold text-[#718096] mb-1">Personal Email</div>
                      <div className="text-[15px] font-semibold text-[#1A202C]">rajesh_k_personal@gmail.com</div>
                    </div>
                  </div>
                </div>
              </div>
            </>
          )}

          {activeTab !== 'Personal Info' && (
            <div className="bg-white border border-gray-200 rounded-2xl p-16 shadow-sm flex flex-col items-center justify-center text-center">
              <FileText className="w-16 h-16 text-[#CBD5E0] mb-4" />
              <h2 className="text-xl font-bold text-[#0B1B36] mb-2">{activeTab} Details</h2>
              <p className="text-[#718096] max-w-md">The {activeTab.toLowerCase()} records for this employee are being loaded or have not been updated recently.</p>
            </div>
          )}
        </div>

        {/* Right Sidebar */}
        <div className="space-y-6">
          {/* Emergency Contact */}
          <div className="bg-[#1A263E] rounded-2xl p-6 text-white shadow-sm border border-[#2D3748]">
            <h3 className="text-[10px] font-bold text-[#A0AEC0] tracking-widest uppercase mb-4">EMERGENCY CONTACT</h3>
            <div className="flex items-center gap-4 mb-5">
              <div className="w-12 h-12 bg-[#2D3748] rounded-full flex items-center justify-center">
                <User className="w-6 h-6 text-[#A0AEC0]" />
              </div>
              <div>
                <div className="font-bold text-white text-lg">Sunita Kumar</div>
                <div className="text-sm font-medium text-[#A0AEC0]">Spouse</div>
              </div>
            </div>
            <div className="flex items-center text-sm font-bold text-[#E2E8F0] bg-[#2D3748] p-3 rounded-lg">
              <Phone className="w-4 h-4 mr-3 text-[#A0AEC0]" />
              +91 98776 55544
            </div>
          </div>

          {/* Quick Access */}
          <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
            <h3 className="text-lg font-bold text-[#0B1B36] mb-4">Quick Access</h3>
            <div className="space-y-3">
              <div className="flex justify-between items-center p-3 border border-gray-100 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer">
                <div className="flex items-center text-sm font-semibold text-[#1A202C]">
                  <FileText className="w-4 h-4 text-[#ED8936] mr-3" /> Aadhar Card
                </div>
                <Download className="w-4 h-4 text-[#718096]" />
              </div>
              <div className="flex justify-between items-center p-3 border border-gray-100 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer">
                <div className="flex items-center text-sm font-semibold text-[#1A202C]">
                  <FileText className="w-4 h-4 text-[#ED8936] mr-3" /> Joining Letter
                </div>
                <Download className="w-4 h-4 text-[#718096]" />
              </div>
              <div className="flex justify-between items-center p-3 border border-gray-100 rounded-xl bg-[#F0FFF4] border-[#C6F6D5]">
                <div className="flex items-center text-sm font-semibold text-[#22543D]">
                  <FileText className="w-4 h-4 text-[#38A169] mr-3" /> NDA Signed
                </div>
                <CheckCircle className="w-4 h-4 text-[#38A169]" />
              </div>
            </div>
          </div>

          {/* Credentials */}
          <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
            <h3 className="text-lg font-bold text-[#0B1B36] mb-4">Credentials</h3>
            <div className="flex flex-wrap gap-2">
              <span className="px-3 py-1.5 bg-[#EDF2F7] text-[#4A5568] text-xs font-bold rounded-lg">Civil Engineering B.Tech</span>
              <span className="px-3 py-1.5 bg-[#EDF2F7] text-[#4A5568] text-xs font-bold rounded-lg">OSHA 30-Hour</span>
              <span className="px-3 py-1.5 bg-[#EDF2F7] text-[#4A5568] text-xs font-bold rounded-lg">PMP Certified</span>
              <span className="px-3 py-1.5 bg-[#EDF2F7] text-[#4A5568] text-xs font-bold rounded-lg">Safety Officer Level 2</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

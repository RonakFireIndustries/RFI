import { useEffect, useState } from 'react';
import ModuleListPage from '../ERP/ModuleListPage';
import api from '../../services/api';
import { unwrapList } from '../../services/resourceHelpers';
import { useEmployeesStore } from '../../store/employeesStore';

export default function EmployeesPage() {
  const [lookups, setLookups] = useState({ branches: [], departments: [], designations: [], employees: [] });

  useEffect(() => {
    Promise.all([api.get('/branches'), api.get('/departments'), api.get('/designations'), api.get('/employees')])
      .then(([branches, departments, designations, employees]) => {
        setLookups({
          branches: unwrapList(branches.data),
          departments: unwrapList(departments.data),
          designations: unwrapList(designations.data),
          employees: unwrapList(employees.data).map(emp => ({ ...emp, name: emp.full_name }))
        });
      })
      .catch((error) => console.error('Failed to load employee lookups', error));
  }, []);

  return (
    <ModuleListPage
      title="Employees"
      description="Manage employee records with department, designation, site, attendance, leave, and payroll relationships."
      store={useEmployeesStore}
      detailBasePath="/dashboard/employees"
      searchPlaceholder="Search employees..."
      lookups={lookups}
      columns={[
        { header: 'Emp ID', accessor: 'emp_id' },
        { header: 'Name', accessor: 'full_name' },
        { header: 'Email', accessor: 'user.email' },
        { header: 'Department', accessor: 'department.name' },
        { header: 'Designation', accessor: 'designation.name' },
        { header: 'Type', accessor: 'employment_type' },
      ]}
      fields={[
        { type: 'heading', label: 'Basic Information', fullWidth: true },
        { name: 'emp_id', label: 'Employee ID', required: true },
        { name: 'full_name', label: 'Full Name', required: true },
        { name: 'dob', label: 'Date of Birth', type: 'date' },
        { name: 'gender', label: 'Gender', type: 'select', emptyAsNull: true, options: [{ value: 'Male', label: 'Male' }, { value: 'Female', label: 'Female' }, { value: 'Other', label: 'Other' }] },
        { name: 'marital_status', label: 'Marital Status', type: 'select', emptyAsNull: true, options: [{ value: 'Single', label: 'Single' }, { value: 'Married', label: 'Married' }, { value: 'Divorced', label: 'Divorced' }, { value: 'Widowed', label: 'Widowed' }] },
        { name: 'contact_number', label: 'Contact Number' },
        { name: 'personal_number', label: 'Alternative Number' },
        { name: 'emergency_contact', label: 'Emergency Contact' },
        { name: 'address', label: 'Address', fullWidth: true },

        { type: 'heading', label: 'Employment Details', fullWidth: true },
        { name: 'department_id', label: 'Department', type: 'select', optionsKey: 'departments', emptyAsNull: true },
        { name: 'designation_id', label: 'Designation', type: 'select', optionsKey: 'designations', emptyAsNull: true },
        { name: 'employment_type', label: 'Employment Type', type: 'select', required: true, defaultValue: 'Full-Time', options: [{ value: 'Full-Time', label: 'Full-Time' }, { value: 'Part-Time', label: 'Part-Time' }, { value: 'Contract', label: 'Contract' }] },
        { name: 'reporting_manager_id', label: 'Reporting Manager', type: 'select', optionsKey: 'employees', emptyAsNull: true },
        { name: 'joining_date', label: 'Joining Date', type: 'date', required: true },
        { name: 'interview_date', label: 'Interview Date', type: 'date' },
        { name: 'probation_end_date', label: 'Probation End Date', type: 'date' },

        { type: 'heading', label: 'Status & Compliance', fullWidth: true },
        { name: 'government_id_type', label: 'Government ID Type', type: 'select', emptyAsNull: true, options: [{ value: 'Aadhaar', label: 'Aadhaar' }, { value: 'PAN', label: 'PAN' }, { value: 'Passport', label: 'Passport' }, { value: 'Voter ID', label: 'Voter ID' }] },
        { name: 'qualification', label: 'Highest Qualification' },
        { name: 'employment_bond_status', label: 'Employment Bond?', type: 'select', emptyAsNull: true, options: [{ value: 1, label: 'Yes' }, { value: 0, label: 'No' }] },
        { name: 'previous_termination_status', label: 'Previously Terminated?', type: 'select', emptyAsNull: true, options: [{ value: 1, label: 'Yes' }, { value: 0, label: 'No' }] },
        { name: 'legal_proceedings_status', label: 'Legal Proceedings?', type: 'select', emptyAsNull: true, options: [{ value: 1, label: 'Yes' }, { value: 0, label: 'No' }] },
        { name: 'create_user_account', label: 'Create User Account?', type: 'select', emptyAsNull: true, options: [{ value: 1, label: 'Yes' }, { value: 0, label: 'No' }] },

        { type: 'heading', label: 'Documents (Uploads)', fullWidth: true },
        { name: 'photo', label: 'Profile Photo', type: 'file', accept: 'image/*' },
        { name: 'resume', label: 'Resume', type: 'file', accept: '.pdf,.doc,.docx' },
        { name: 'aadhaar', label: 'Aadhaar Card', type: 'file', accept: '.pdf,image/*' },
        { name: 'pan', label: 'PAN Card', type: 'file', accept: '.pdf,image/*' },
        { name: 'offer_letter', label: 'Offer Letter', type: 'file', accept: '.pdf' },
      ]}
    />
  );
}

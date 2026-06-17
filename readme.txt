# Daily Progress Reports (DPR) Module Setup

The Daily Progress Reports module is complete, seamlessly integrating with your existing Employee and Sites architectures. Let's walk through the implementation.

## Backend Implementation

I overhauled the backend architecture to support strict workflow policies and robust service layer segregation for the Daily Progress Reports.

1. **Policies and Permissions**
   - Created `DailyReportPolicy` and mapped it in `AuthServiceProvider`.
   - Populated the `RolesAndPermissionsSeeder` with new permissions: `daily-report.view`, `daily-report.create`, `daily-report.edit`, `daily-report.delete`, `daily-report.submit`, `daily-report.approve`, `daily-report.reject`, `daily-report.rework`. 
   - Seeded the `HR` role to have full access permissions over Daily Reports.
2. **REST APIs & Form Requests**
   - Wrote `StoreDailyReportRequest` and `UpdateDailyReportRequest` to sanitize and validate input payloads using Laravel Form Requests.
   - Refactored `DailyReportController.php` to handle standard CRUD, as well as dedicated Workflow actions (`approve`, `reject`, `rework`).
3. **Service Layer**
   - Developed `DailyReportService.php` to encapsulate complex validation logic, such as preventing duplicate- Designed user interfaces for documents (`EmployeeDocumentsPage`), ensuring a clean layout and easy navigation.
- Fixed frontend bugs related to map iteration on undefined properties.
- **Leave Management Module**: 
  - Integrated Leave Types, Leave Balances, and Leave Requests components.
  - Implemented multi-tier approval workflow (Draft -> Submitted -> Manager/HR Approval).
  - Integrated Attendance automatically syncing when leave is approved (Marked as "On Leave").
  - Created an overview Leave Dashboard with real-time metrics.
   - Managed `DailyReportHistory` logging natively within the service when state transitions occur.

## Frontend Implementation

1. **State Management**
   - Generated `dailyReportsService.js` to manage Axios API integrations securely.
   - Created `dailyReportStore.js` leveraging Zustand, exposing standard CRUD fetchers as well as Workflow state mutations.
2. **Views & Pages**
   - **`DailyReportsPage.jsx`**: An interactive List view to see all DPRs across the enterprise, equipped with client-side site and status filtering.
   - **`DailyReportDetail.jsx`**: A comprehensive review page for Managers and HR, displaying all logged work details, hours, resources, and materials used. It includes an interactive **Manager Review** block enabling approvals, rejections, or rework requests, accompanied by a dynamic `History Log` timeline.
   - **`DailyReportForm.jsx`**: The submission form optimized for employee end-users to quickly draft and submit their daily metrics.
3. **Integrations**
   - Merged the new routes into `App.jsx` and `DashboardLayout.jsx`.
   - Included **Daily Reports** in the sidebar navigation menu.
   - Inserted a **Daily Reports Tab** inside the `EmployeeDetail.jsx` profile page. Now, when viewing an employee's profile, you can view all historical reports submitted by that distinct employee.

## Next Steps

> [!TIP]
> Ensure you reload your frontend so Vite can compile the new routes!

Log in as a user with the `HR` role to access the Daily Reports menu item. Try creating a Draft report, submitting it, and running it through the Manager Review workflow to observe the historical logging.

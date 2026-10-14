/**
 * Leave types can be restricted to a gender via the `gender_restriction`
 * column (values: 'Male', 'Female' or null for "all employees").
 *
 * Employees with a blank/Other gender only ever see unrestricted leave types,
 * so Maternity/Paternity stay hidden for them.
 */
export const filterLeaveTypesForGender = (leaveTypes, gender) =>
  (leaveTypes || []).filter((type) => {
    const restriction = type?.gender_restriction || null;
    if (!restriction) return true;
    if (!gender || gender === 'Other') return false;
    return restriction === gender;
  });

/**
 * Status badge utilities for invoices
 */
export function useStatusBadge() {
  const getStatusBadgeClass = (status) => {
    const classes = {
      draft: 'bg-gray-100 text-gray-800',
      sent: 'bg-blue-100 text-blue-800',
      paid: 'bg-green-100 text-green-800',
      overdue: 'bg-red-100 text-red-800',
      cancelled: 'bg-gray-100 text-gray-600'
    }
    return classes[status] || classes.draft
  }

  const getStatusBadgeClasses = getStatusBadgeClass // Alias for consistency

  return {
    getStatusBadgeClass,
    getStatusBadgeClasses
  }
}


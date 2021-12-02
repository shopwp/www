function TableHeader({ children }) {
  return (
    <thead className='wpshopify-table-header'>
      <tr>{children}</tr>
    </thead>
  );
}

export default TableHeader;

function buildUpdateQuery ( updateData, tableName, id ) {
  const columns = Object.keys( updateData );
  const values = Object.values( updateData );

  // Build the SET part of the query
  const setClause = columns.map( ( column ) => `${ column } = ?` ).join( ', ' );

  // Create the final update query
  const updateQuery = `UPDATE ${ tableName } SET ${ setClause } WHERE car_id = ${ id }`;

  return {
    query: updateQuery,
    values,
  };
}
module.exports = buildUpdateQuery

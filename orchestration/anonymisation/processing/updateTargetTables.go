package processing

import (
	"anonymisation/common"
	"database/sql"
	"fmt"
	"sync"
)

func UpdateSelectedColumnsFromPublic(
	db *sql.DB,
	tableName string,
	keyColumn string,
	updateColumn string,
	whereColumn string,
	whereTerm string) error {
	// Base query without the optional WHERE clause
	query := fmt.Sprintf(`
		UPDATE anon.%s AS anon
		SET %s = pub.%s
		FROM public.%s AS pub, processing.%s AS proc
		WHERE proc.%s = pub.%s
		AND proc.ppk_id = anon.ppk_id
	`, tableName, updateColumn, updateColumn, tableName, tableName, keyColumn, keyColumn)

	// Append the WHERE clause if whereTerm is not empty
	if whereTerm != "" {
		query += fmt.Sprintf(" AND pub.%s LIKE '%%%s%%'", whereColumn, whereTerm)
	}
	fmt.Print(query)
	// Execute the update query
	_, err := db.Exec(query)
	if err != nil {
		return err
	}

	return nil
}

func getSqlUpdateStatement(table common.Table, thisTablesDetails []common.LeftJoinsDetails, chunkSize int, offset int, leftJoinSqlLinesField []string) string {
	sqlQuery := fmt.Sprintf("UPDATE public.%s pub1 SET", table.TableName)
	for _, field := range table.FieldNames {
		consistentFieldFromLeftJoin := false
		var consistentDetail common.LeftJoinsDetails
		for _, thisTablesDetail := range thisTablesDetails {
			if field.Column == thisTablesDetail.SourceField {
				consistentDetail = thisTablesDetail
				consistentFieldFromLeftJoin = true
			}
		}
		if consistentFieldFromLeftJoin {
			sqlQuery += fmt.Sprintf(" %s = CASE WHEN NULLIF(pub2.%s, '') IS NULL THEN pub2.%s ELSE COALESCE(%s.%s, anon.%s) END,", field.Column, field.Column, field.Column, consistentDetail.TableName, consistentDetail.FieldName, field.Column)
		} else {
			sqlQuery += fmt.Sprintf(" %s = CASE WHEN NULLIF(pub2.%s, '') IS NULL THEN pub2.%s ELSE anon.%s END,", field.Column, field.Column, field.Column, field.Column)
		}
	}
	sqlQuery = sqlQuery[:len(sqlQuery)-1] // Remove the trailing comma

	sqlQuery += fmt.Sprintf(" FROM public.%s as pub2 INNER JOIN processing.%s AS proc ON pub2.%s = proc.%s",
		table.TableName, table.TableName, table.PkColumn.Column, table.PkColumn.Column)

	sqlQuery += fmt.Sprintf(" INNER JOIN (SELECT * FROM anon.%s ORDER BY ppk_id LIMIT %d OFFSET %d) AS anon ON proc.ppk_id = anon.ppk_id",
		table.TableName, chunkSize, offset)

	if len(leftJoinSqlLinesField) > 0 {
		for _, leftJoinSqlLine := range leftJoinSqlLinesField {
			sqlQuery += leftJoinSqlLine
		}
	}

	sqlQuery += fmt.Sprintf(" WHERE pub1.%s = pub2.%s;", table.PkColumn.Column, table.PkColumn.Column)

	return sqlQuery
}

func UpdateOriginalTable(db *sql.DB, table common.Table, chunkSize int, leftJoins []common.LeftJoinsDetails) error {
	leftJoinSqlLinesField, thisTablesDetails := getLeftJoinsSql(table, leftJoins)
	totalChunks := (table.RowCount + chunkSize - 1) / chunkSize

	for chunk := 0; chunk < totalChunks; chunk++ {
		offset := chunk * chunkSize
		sqlQuery := getSqlUpdateStatement(table, thisTablesDetails, chunkSize, offset, leftJoinSqlLinesField)
		_, err := db.Exec(sqlQuery)
		if err != nil {
			return err
		}
		common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Table %s.%s, chunk %d of %d has been updated in public schema", "public", table.TableName, chunk+1, totalChunks))
	}
	return nil
}

func UpdateAsyncOriginalTables(db *sql.DB, tableDetails []common.Table, chunkSize int, leftJoins []common.LeftJoinsDetails) error {
	concurrency := 4
	semaphore := make(chan struct{}, concurrency) // Create a channel for concurrent goroutines
	done := make(chan struct{})                   // Create a channel to signal when all updates are done
	var wg sync.WaitGroup
	go func() { // Launch a goroutine to close the done channel when all updates are done
		defer close(done)
		wg.Add(len(tableDetails))
		for range tableDetails {
			<-done
			wg.Done()
		}
		wg.Wait() // Wait for all tables to finish processing
	}()

	// Iterate over each table, launch a process for each and update the public schema tables in chunks
	for _, table := range tableDetails {
		semaphore <- struct{}{} // Acquire a token from the semaphore

		go func(table common.Table) { // Launch a goroutine to process the table
			defer func() {
				// Release the token back to the semaphore
				<-semaphore
				done <- struct{}{} // Signal that this table's processing is done
			}()

			err := UpdateOriginalTable(db, table, chunkSize, leftJoins)
			if err != nil {
				fmt.Printf("ERROR: %s", err)
			}
		}(table)
	}

	// Wait for all updates to finish
	wg.Wait()
	return nil
}

//  DEBUGGING QUERY - Add the below to the UpdateAsyncOriginalTables function to see what the SQL is doing
// if table.TableName == "named_deputy" {
// 	fmt.Print(sqlQuery + "\n\n")
// }

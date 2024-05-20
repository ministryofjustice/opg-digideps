package processing

import (
	"anonymisation/common"
	"database/sql"
	"fmt"
	"sync"
)

func UpdateOriginalTables(db *sql.DB, tableDetails []common.Table, chunkSize int) error {
	for _, table := range tableDetails {

		totalChunks := (table.RowCount + chunkSize - 1) / chunkSize

		for chunk := 0; chunk < totalChunks; chunk++ {
			offset := chunk * chunkSize

			// Construct the update query
			sqlQuery := fmt.Sprintf("UPDATE public.%s pub SET", table.TableName)
			for _, field := range table.FieldNames {
				sqlQuery += fmt.Sprintf(" %s = CASE WHEN NULLIF(proc.%s, '') IS NULL THEN proc.%s ELSE anon.%s END,", field.Column, field.Column, field.Column, field.Column)
			}
			sqlQuery = sqlQuery[:len(sqlQuery)-1] // Remove the trailing comma
			sqlQuery += fmt.Sprintf(" FROM processing.%s AS proc, (SELECT * FROM anon.%s ORDER BY ppk_id LIMIT %d OFFSET %d) AS anon WHERE pub.%s = proc.%s AND proc.ppk_id = anon.ppk_id;",
				table.TableName, table.TableName, chunkSize, offset, table.PkColumn.Column, table.PkColumn.Column)

			// fmt.Print(sqlQuery + "\n\n")

			// Execute the update query
			_, err := db.Exec(sqlQuery)
			if err != nil {
				return err
			}
			common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Table %s.%s, chunk %d of %d has been updated in public schema", "public", table.TableName, chunk+1, totalChunks))
		}
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
			sqlQuery += fmt.Sprintf(" %s = CASE WHEN NULLIF(proc.%s, '') IS NULL THEN proc.%s ELSE COALESCE(%s.%s, anon.%s) END,", field.Column, field.Column, field.Column, consistentDetail.TableName, consistentDetail.FieldName, field.Column)
		} else {
			sqlQuery += fmt.Sprintf(" %s = CASE WHEN NULLIF(proc.%s, '') IS NULL THEN proc.%s ELSE anon.%s END,", field.Column, field.Column, field.Column, field.Column)
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

func UpdateAsyncOriginalTables(db *sql.DB, tableDetails []common.Table, chunkSize int, leftJoins []common.LeftJoinsDetails) error {
	// Create a channel to control the number of concurrent goroutines
	concurrency := 5
	semaphore := make(chan struct{}, concurrency)

	// Create a channel to signal when all updates are done
	done := make(chan struct{})

	var wg sync.WaitGroup
	// Launch a goroutine to close the done channel when all updates are done
	go func() {
		defer close(done)

		wg.Add(len(tableDetails))
		for range tableDetails {
			<-done
			wg.Done()
		}
		wg.Wait() // Wait for all tables to finish processing
	}()

	// Iterate over each table
	for _, table := range tableDetails {
		// Acquire a token from the semaphore
		semaphore <- struct{}{}

		// Launch a goroutine to process the table
		go func(table common.Table) {
			defer func() {
				// Release the token back to the semaphore
				<-semaphore
				done <- struct{}{} // Signal that this table's processing is done
			}()

			// var leftJoinSqlLinesFields []string

			leftJoinSqlLinesField, thisTablesDetails := getLeftJoinsSql(table, leftJoins)

			totalChunks := (table.RowCount + chunkSize - 1) / chunkSize

			for chunk := 0; chunk < totalChunks; chunk++ {
				offset := chunk * chunkSize

				sqlQuery := getSqlUpdateStatement(table, thisTablesDetails, chunkSize, offset, leftJoinSqlLinesField)

				// Execute the update query
				_, err := db.Exec(sqlQuery)
				if err != nil {
					fmt.Println(err) // Handle error
				}
				common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Table %s.%s, chunk %d of %d has been updated in public schema", "public", table.TableName, chunk+1, totalChunks))
			}
		}(table)
	}

	// Wait for all updates to finish
	wg.Wait()
	return nil
}

//  PRINT FOR QUERY
// if table.TableName == "named_deputy" {
// 	fmt.Print(sqlQuery + "\n\n")
// }

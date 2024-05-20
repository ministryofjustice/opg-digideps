package initialisation

import (
	"anonymisation/common"
	"database/sql"
	"fmt"
)

func CopySourceTablesToProcessing(db *sql.DB, tables []common.Table, truncate bool) ([]common.Table, error) {
	for tableIndex, table := range tables {
		// Truncate the destination table if truncate flag is set
		if truncate {
			common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Truncating table: %s", table.TableName))
			_, err := db.Exec(fmt.Sprintf("TRUNCATE TABLE processing.%s", table.TableName))
			if err != nil {
				return nil, fmt.Errorf("failed to truncate table %s: %v", table.TableName, err)
			}
		}

		// Generate the INSERT INTO SELECT statement
		fieldList := ""
		for _, field := range table.FieldNames {
			fieldList += field.Column + ","
		}
		fieldList = fieldList[:len(fieldList)-1] // Remove the trailing comma

		query := fmt.Sprintf("INSERT INTO processing.%s (%s,%s,%s) SELECT %s,%s,%s FROM public.%s", table.TableName, table.PkColumn.Column, fieldList, "anonymised", table.PkColumn.Column, fieldList, "false", table.TableName)

		// Execute the INSERT INTO SELECT query
		_, err := db.Exec(query)
		if err != nil {
			return nil, fmt.Errorf("failed to copy table %s: %v", table.TableName, err)
		}

		// Get the row count of the destination table
		var rowCount int
		err = db.QueryRow(fmt.Sprintf("SELECT COUNT(*) FROM processing.%s", table.TableName)).Scan(&rowCount)
		if err != nil {
			return nil, fmt.Errorf("failed to get row count of table %s: %v", table.TableName, err)
		}

		common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Table %s copied successfully. Rows inserted: %d\n", table.TableName, rowCount))

		table.RowCount = rowCount
		tables[tableIndex] = table
	}
	return tables, nil
}

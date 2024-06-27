package initialisation

import (
	"anonymisation/common"
	"database/sql"
	"fmt"
)

/**
* The processing schema tables are intermediate tables that contain:
* - Generated PK that is incremented by 1 each row
* - The PK of the table from public schema
 */
func CopySourceTablesToProcessing(db *sql.DB, tables []common.Table, truncate bool) ([]common.Table, error) {
	for tableIndex, table := range tables {
		// Usually we wish to truncate. TODO - Find out if it's valid to not truncate and simply append
		if truncate {
			common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Truncating table: %s", table.TableName))
			_, err := db.Exec(fmt.Sprintf("TRUNCATE TABLE processing.%s", table.TableName))
			if err != nil {
				return nil, fmt.Errorf("failed to truncate table %s: %v", table.TableName, err)
			}
		}

		query := fmt.Sprintf("INSERT INTO processing.%s (%s) SELECT %s FROM public.%s", table.TableName, table.PkColumn.Column, table.PkColumn.Column, table.TableName)
		common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("%s\n", query))

		_, err := db.Exec(query)
		if err != nil {
			return nil, fmt.Errorf("failed to copy table %s: %v", table.TableName, err)
		}

		var processingRowCount int
		err = db.QueryRow(fmt.Sprintf("SELECT COUNT(*) FROM processing.%s", table.TableName)).Scan(&processingRowCount)
		if err != nil {
			return nil, fmt.Errorf("failed to get row count of table %s: %v", table.TableName, err)
		}

		var anonRowCount int
		err = db.QueryRow(fmt.Sprintf("SELECT COUNT(*) FROM anon.%s", table.TableName)).Scan(&anonRowCount)
		if err != nil {
			return nil, fmt.Errorf("failed to get row count of table %s: %v", table.TableName, err)
		}

		common.LogInformation(common.GetCurrentFuncName(), fmt.Sprintf("Table %s copied successfully. Rows inserted: %d\n", table.TableName, processingRowCount))

		// We store the row counts so we can work out how many chunks to process
		table.RowCount = processingRowCount
		table.ExistingRowCount = anonRowCount
		tables[tableIndex] = table
	}
	return tables, nil
}
